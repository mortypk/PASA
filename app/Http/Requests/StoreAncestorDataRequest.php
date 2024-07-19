<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAncestorDataRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'gender' => 'required|exists:genders,id',
            'ancestor_surname' => 'required|string',
            'given_name' => 'required|string',
            'source_of_arrival' => 'nullable',
            'date_of_birth' => 'nullable',
            'month_of_birth' => 'nullable',
            'year_of_birth' => 'nullable',
            'date_of_death' => 'nullable',
            'month_of_death' => 'nullable',
            'year_of_death' => 'nullable',
            'arrival_date_in_sa' => 'nullable|string',
            'evidence_of_arrival' => 'nullable|string',
            'mode_of_arrival_id' => 'nullable|int',
            'marriage_date' =>'nullable',
            'marriage_place'=>'nullable',
            'spouse_family_name' =>'nullable',
            'spouse_given_name' => 'nullable',
            'spouse_birth_date' => 'nullable',
            'spouse_place_of_birth' =>'nullable',
            'spouse_place_of_death' =>'nullable',
            'spouse_death_date' => 'nullable',
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'gender.required' => 'Gender field is required',
            'ancestor_surname.required' => 'Pioneer\'s Family Name field is required',
            'ancestor_surname.string' => 'Pioneer\'s Family Name must be a string',
            'given_name.required' => 'Pioneer\'s Given Name field is required',
            'given_name.string' => 'Pioneer\'s Given Name must be a string',
            'source_of_arrival.required' => 'Mode of Travel field is required',
        ];

        return $messages;
    }
}
