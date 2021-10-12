<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Reservation;

class ReservationIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'status'=> Rule::in([Reservation::STATUS_ACTIVE, Reservation::STATUS_CANCELLED]),
            'office_id'=> ['integer'],
            'from_date'=>['date','required_with:to_date'],
            'to_date'=>['date','required_with:from_date']
        ];
    }
}
