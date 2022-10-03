<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserValidation extends FormRequest
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
        return [
            "name"=>'required|regex:/[A-Z]{1}[a-z]+/',
            "email"=>'required|email|unique:users',
            "Username"=>'required|unique:users|regex:/[A-Z]{1}[a-z]*[0-9]{1,}[a-z]*/',
            "mobile"=>'required|max:10|min:10|regex:/[6789][0-9]{9}/',
            "address"=>'required'
        ];
    }
}
