<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
class UpdateJob extends FormRequest
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
            'from_language_id' => 'required',
            'due' => 'required',
            'admin_comments' => 'required',
            'reference' => 'required',
            // Add other validation rules here
        ];
    }
}
