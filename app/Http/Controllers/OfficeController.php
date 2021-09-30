<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Http\Resources\OfficeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\Office\CreateRequest;
use App\Http\Requests\Office\UpdateRequest;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfficePendingApprovalNotification;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    //
    public function index(Request $request):JsonResource
    {
        $offices = Office::query()
            ->where('approval_status',Office::APPROVAL_APPROVED)
            ->where('hidden',false)
            ->when(request('user_id'),function($builder){
                $builder->where('user_id',request('user_id'));
            })
            ->when(request('visitor_id'),function($builder){
                $builder->whereRelation('reservations','user_id','=',request('visitor_id'));
            })           
            ->when(request('lat') && request('lng'),
                function($builder){
                    $builder->nearestTo(request('lat'),request('lng'));
                },
                function($builder){
                    $builder->orderBy('id','ASC');
                }
            ) 
            ->with(['images','tags','user'])
            ->withCount([
                'reservations'=> function($builder){
                    $builder->where('status',Reservation::STATUS_ACTIVE);
                }
            ])
            ->paginate(20);

        return OfficeResource::collection($offices);    
    }

    //
    public function show(Office $office):JsonResource
    {
        $office->loadCount([
            'reservations' => function($builder){
                $builder->where('status',Office::APPROVAL_APPROVED);
            }
        ])
        ->load(['images', 'tags', 'user']);

        return OfficeResource::make($office);
    }

    //
    public function create(CreateRequest $request):JsonResource
    {
        abort_unless(auth()->user()->tokenCan('office.create'),
            Response::HTTP_FORBIDDEN
        );

        $office = DB::transaction(function() use ($request){
            $office = auth()->user()->offices()->create(Arr::except($request->validated(),['tags']));
            $office->tags()->attach($request->tags);
            return $office;
        });

        Notification::send(User::where('email','admin@admin.com')->first(), 
            new OfficePendingApprovalNotification($office));        

        return OfficeResource::make($office->load(['images', 'tags', 'user']));

    }

    //
    public function update(UpdateRequest $request, Office $office)
    {
        abort_unless(auth()->user()->tokenCan('office.update'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $office);

        $office->fill(Arr::except($request->validated(),['tags']));

        if($requiresReview = $office->isDirty(['lat','lng','price_per_day'])){
            $office->approval_status = Office::APPROVAL_PENDING;
        }

        $office = DB::transaction(function() use ($request,$office){
            $office->save();
            if(isset($request->tags)){
                $office->tags()->sync($request->tags);    
            }       
            return $office;
        });

        if($requiresReview){
            Notification::send(User::where('email','admin@admin.com')->first(), 
                new OfficePendingApprovalNotification($office));
        }

         return OfficeResource::make($office->load(['images', 'tags', 'user']));      
    }

    //
    public function delete(Office $office)
    {
        abort_unless(auth()->user()->tokenCan('office.delete'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('delete', $office);

        throw_if($office->reservations()->where('status',Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(["office" => "Cannot delete active office"]));

        $office->delete();
    }

}
