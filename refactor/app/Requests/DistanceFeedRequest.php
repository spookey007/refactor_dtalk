<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class DistanceFeedRequest extends FormRequest
{
    public function rules()
    {
        return [
            'distance' => 'nullable',
            'time' => 'nullable',
            'jobid' => 'required',
            'session_time' => 'nullable',
            'flagged' => 'required|boolean',
            'admincomment' => 'nullable',
            'manually_handled' => 'required|boolean',
            'by_admin' => 'required|boolean',
        ];
    }
}