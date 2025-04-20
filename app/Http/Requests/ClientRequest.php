<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Client can make registration , we will need to know email , name ,
        // avatar_image , country , gender all is required and email for sure
        // is unique , and countries must be drop down list from the
        // package , also gender must be Male Or Female
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clients,email'],
            'password' => ['required', 'string', 'min:8'],
            'nationalId' => ['required', 'string', 'unique:clients,nationalId'],
            'country' => ['required', 'string'],
            'gender' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format please enter valid one',
            'email.unique' => 'This email is already taken',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 character',
            'nationalId.required' => 'National ID is required',
            'nationalId.unique' => 'This national ID already taken',
            'country.required' => 'Country is required',
        ];
    }
}
