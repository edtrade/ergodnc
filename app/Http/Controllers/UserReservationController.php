<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Reservation;
use App\Models\Office;
use App\Http\Resources\ReservationResource;
use App\Http\Requests\Reservation\ReservationIndexRequest;
use App\Http\Requests\Reservation\UserStoreRequest;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class UserReservationController extends Controller
{
    //
    public function index(ReservationIndexRequest $request): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'), Response::HTTP_FORBIDDEN);

        $reservations = Reservation::query()
                    ->where('user_id',auth()->id())
                    ->when(request('office_id'),function($builder){
                        $builder->where('office_id',request('office_id'));  
                    })
                    ->when(request('status'),function($builder){
                        $builder->where('status',request('status'));  
                    })               
                    ->when(request('from_date') && request('to_date'),function($builder){
                        $builder->betweenDates(request('from_date'),request('to_date'));
                    })   
                    ->with(['office.featuredImage'])
                    ->paginate(20);

        return ReservationResource::collection($reservations);            
    }

    //
    public function create(UserStoreRequest $request): JsonResource
    {
         abort_unless(auth()->user()->tokenCan('reservations.make'), Response::HTTP_FORBIDDEN);

         $office = Office::findOrFail($request->office_id);

         throw_if(auth()->id() == $office->user_id, 
            ValidationException::withMessages(["office_id" => "Cannot make reservation on your own office"]));

         $reservation = Cache::lock('reservations_office_'.$office->id, 10)->block(3,function() use ($office,$request){

            $numberDays =  Carbon::parse($request->end_date)
                ->endOfDay()
                ->diffInDays(Carbon::parse($request->start_date)->startOfDay()) + 1;

         throw_if($numberDays < 2, 
            ValidationException::withMessages(["end_date" => "Cannot make a one day reservation on this office"]));                

            throw_if($office->reservations()->activeBetween($request->start_date,$request->end_date)->exists(),
                ValidationException::withMessages(["office_id" => "Cannot make reservation during this time"]));

            $price = $numberDays * $office->price_per_day;

            if($numberDays > 28 && $office->monthly_discount){
                $price = $price - ($price * $office->monthly_discount /100);
            }

            return Reservation::create([
                'user_id' => auth()->id(),
                'office_id'=> $office->id,
                'start_date'=>$request->start_date,
                'end_date'=>$request->end_date,
                'status'=> Reservation::STATUS_ACTIVE,
                'price'=> $price
            ]);

         });

         return ReservationResource::make($reservation->load('office'));
    }
}
