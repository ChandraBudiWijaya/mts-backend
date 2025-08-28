<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Rules dari EmployeeController@update dipindahkan ke sini.
        // Kita tidak perlu validasi email atau password karena tidak di-update di sini.
        return [
            'name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'role_id' => 'sometimes|integer|exists:roles,id',
            'plantation_group' => 'sometimes|required|string|max:255',
            'wilayah' => 'sometimes|required|string|max:255',
        ];
    }
}
