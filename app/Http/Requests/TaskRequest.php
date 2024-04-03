<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'task_assigner_id' => 'required|numeric|exists:users,id',
            'task_id' => 'nullable|numeric|exists:tasks,id',
            'department_id' => 'required|numeric|exists:departments,id',
            'user_id' => 'required|numeric|exists:users,id',
            'task_name' => 'required|string',
            'task_description' => 'nullable'

        ];
    }
}
