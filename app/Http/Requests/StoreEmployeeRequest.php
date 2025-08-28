<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Di sini kita bisa menambahkan logika otorisasi, misalnya:
     * return $this->user()->can('create-employees');
     * Untuk saat ini, kita biarkan true karena otorisasi sudah ditangani di level route middleware.
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
        // Semua rules validasi dari EmployeeController@store dipindahkan ke sini.
        return [
            'id' => 'required|string|unique:employees,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|exists:roles,id',
            'plantation_group' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
        ];
    }
}
