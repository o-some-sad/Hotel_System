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

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clients,email'],
            'password' => ['required', 'string', 'min:8'],
            'nationalId' => ['required', 'string', 'size:10', 'unique:clients,nationalId'],
            'country' => ['required', 'string', 'regex:/^[A-Za-z\s]+$/', 'exists:lc_countries_translations,name'],
            'gender' => ['required'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,gif,svg,webp', 'max:1024']
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
            'nationalId.size' => 'National ID should be 10 numbers',
            'nationalId.unique' => 'This national ID already taken',
            'country.required' => 'Country is required',
            'country.exists' => 'Invalid country please enter valid one',
            'country.regex' => 'Country must contain only letters and spaces.',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Allowed image types: jpeg, png, jpg, gif, svg, webp',
            'image.max' => 'Image size must not exceed 1MB'
        ];
    }
}
