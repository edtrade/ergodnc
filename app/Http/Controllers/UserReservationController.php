<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Reservation;
use App\Http\Resources\ReservationResource;

class UserReservationController extends Controller
{
    //
    public function index()
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
                        $builder->whereBetween('start_date',[request('from_date'), request('to_date')])
                            ->orWhereBetween('end_date',[request('from_date'), request('to_date')]);
                    })   
                    ->with(['office.featuredImage'])
                    ->paginate(20);

        return ReservationResource::collection($reservations);            
    }
}
