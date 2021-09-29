<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Office;
use App\Models\Reservation;
use App\Http\Resources\OfficeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\Office\CreateRequest;
use Illuminate\Support\Arr;

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

        $office = auth()->user()->offices()->create(Arr::except($request->validated(),['tags']));

        $office->tags()->sync($request->tags);

        return OfficeResource::make($office);

    }

}
