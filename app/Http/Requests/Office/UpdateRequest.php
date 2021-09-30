<?php

namespace App\Http\Requests\Office;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'title' => ['string','sometimes'],
            'description' => ['string','sometimes'],
            'lat' => ['numeric','sometimes'],
            'lng' => ['numeric','sometimes'],
            'address_line1' => ['string','sometimes'],
            'hidden' => ['boolean'],
            'price_per_day' => ['integer','sometimes', 'min:100'],
            'monthly_discount' => ['integer', 'min:100'],

            'tags'=>['array'],
            'tags.*' => ['integer',Rule::exists('tags','id')]
        ];
    }
}
