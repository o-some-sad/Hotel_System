<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        $clientId = $this->route('client');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('clients', 'email')->ignore($clientId), function ($attribute, $value, $fail) {
                $restrictedDomains = ['@manager', '@receptionist', '@admin'];
                foreach ($restrictedDomains as $domain) {
                    if (str_contains($value, $domain)) {
                        $fail('This email domain is reserved for a staff used');
                    }
                }
            }],
            'password' => [$this->route('client') ? 'nullable' : 'required', 'string', 'min:6'],
            'nationalId' => ['required', 'string', 'size:14', Rule::unique('clients', 'nationalId')->ignore($clientId),],
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
            'password.min' => 'Password must be at least 6 character',
            'nationalId.required' => 'National ID is required',
            'nationalId.size' => 'National ID should be 14 numbers',
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
