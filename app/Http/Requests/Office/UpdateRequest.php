<?php

namespace App\Http\Requests\Office;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    protected $office;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    //override to inject request('office')
    public function all($keys = null) 
    {
       $data = parent::all();
       $this->office = $this->route('office');
       return $data;
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
            'featured_image_id' => [
                'integer',
                Rule::exists('images','id')
                    ->where('resource_type','office')
                    ->where('resource_id',$this->office->id)
            ],
            'tags'=>['array'],
            'tags.*' => ['integer',Rule::exists('tags','id')]
        ];
    }
}
