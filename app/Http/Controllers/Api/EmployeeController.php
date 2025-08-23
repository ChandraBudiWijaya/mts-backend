<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan dengan fungsionalitas filter.
     */
    public function index(Request $request)
    {
        // Memulai query builder
        $query = Employee::query();

        // Filter berdasarkan pencarian Nama Karyawan atau Username (email)
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('email', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Filter berdasarkan Plantation Group
        if ($request->has('pg')) {
            $query->where('plantation_group', $request->pg);
        }

        // Filter berdasarkan Wilayah
        if ($request->has('wilayah')) {
            $query->where('wilayah', $request->wilayah);
        }

        // Eager load relasi dan eksekusi query
        return $query->with('user.roles')->get();
    }

    /**
     * Menyimpan karyawan baru sesuai dengan data dari form.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|string|unique:employees,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|exists:roles,id',
            'plantation_group' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
        ]);

        // Buat akun login di tabel users
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Berikan peran (role) yang dipilih dari form
        $user->roles()->attach($validatedData['role_id']);

        // Buat data profil di tabel employees
        $employee = Employee::create([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'user_id' => $user->id,
            'position' => $validatedData['position'],
            'plantation_group' => $validatedData['plantation_group'],
            'wilayah' => $validatedData['wilayah'],
            'phone' => $validatedData['phone'],
        ]);

        return response()->json($employee, 201);
    }

    /**
     * Menampilkan satu karyawan spesifik.
     */
    public function show(Employee $employee)
    {
        return $employee->load('user.roles');
    }

    /**
     * Mengupdate data karyawan dan rolenya.
     */
    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'role_id' => 'sometimes|integer|exists:roles,id',
            'plantation_group' => 'sometimes|required|string|max:255',
            'wilayah' => 'sometimes|required|string|max:255',
        ]);

        // Update data di tabel employees
        $employee->update($validatedData);

        // Jika ada role_id yang dikirim, update peran user terkait
        if ($request->has('role_id') && $employee->user) {
            $employee->user->roles()->sync([$validatedData['role_id']]);
        }

        return response()->json($employee->load('user.roles'));
    }

    /**
     * Menghapus data karyawan.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        return response()->json(null, 204);
    }
}
