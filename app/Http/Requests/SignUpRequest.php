<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date',
            'address' => 'required|string|max:255',
            'role' => 'required|in:Assigner,Assignee,assigner,assignee',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'company_id' => 'required|string',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => ['required','confirmed', Password::min(8)->numbers()->symbols()->mixedCase()]
        ];
    }

    // ['required','confirmed,', Password::min(8)->mixedCase()->symbols()->numbers()]
}
