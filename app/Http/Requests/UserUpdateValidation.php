<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateValidation extends FormRequest
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
            "name"=>'regex:/([A-Za-z])+( [A-Za-z]+)/',
            "email"=>'email|unique:users',
            "username"=>'unique:users|regex:/[A-Z]{1}[a-z]*[0-9]{1,}[a-z]*/',
            "mobile"=>'max:10|min:10|regex:/[6789][0-9]{9}/',
        ];
    }
}
