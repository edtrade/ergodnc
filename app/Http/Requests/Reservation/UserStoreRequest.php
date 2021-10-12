<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
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
            'office_id' => ['required', 'integer',Rule::exists('offices','id')],
            'start_date' => ['required', 'date', 'after:'.now()->addDay()->toString()],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}
