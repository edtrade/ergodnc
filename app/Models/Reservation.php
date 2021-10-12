<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;
    
    protected $casts = [
        'price' => 'integer',
        'status' => 'integer',
        'start_date' => 'immutable_date',
        'end_date' => 'immutable_date',
        'wifi_password'=>'encrypted'
    ];
    
    //Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }    

    public function scopeActiveBetween($query, $from_date, $to_date)
    {
        return $query->whereStatus(Reservation::STATUS_ACTIVE)
            ->betweenDates($from_date, $to_date);
    }

    public function scopeBetweenDates($query, $from_date, $to_date)
    {
        //group conditions
        return $query->where(function($builder) use ($from_date, $to_date){
            //
            return $builder->whereBetween('start_date',[$from_date, $to_date])
                ->orWhereBetween('end_date',[$from_date, $to_date])
                //
                ->orWhere(function($builder) use ($from_date, $to_date){
                    $builder->where('start_date','<',$from_date)
                        ->where('end_date','>',$to_date);
                });
         });
    }
}
