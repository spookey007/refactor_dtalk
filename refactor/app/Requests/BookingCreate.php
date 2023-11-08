<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingCreate extends FormRequest
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
        //ALL VALIDATIONS
        return [
            'from_language_id' => 'required',
            'immediate' => 'required|in:yes,no',
            'due_date' => 'required_if:immediate,no',
            'due_time' => 'required_if:immediate,no',
            'customer_phone_type' => 'required_without_all:customer_physical_type',
            'customer_physical_type' => 'required_without_all:customer_phone_type',
            'duration' => 'required',
        ];
    }
}
