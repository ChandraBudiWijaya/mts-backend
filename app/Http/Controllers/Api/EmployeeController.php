<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class EmployeeController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan.
     */
    public function index()
    {
        return Employee::with('user:id,email')->get();
    }

    /**
     * Menyimpan karyawan baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|string|unique:employees,id',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // Buat akun login di tabel users
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Buat data profil di tabel employees
        $employee = Employee::create([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'position' => $validatedData['position'],
            'phone' => $validatedData['phone'],
            'user_id' => $user->id, // Hubungkan dengan user yang baru dibuat
        ]);

        // TODO: Berikan role "Mandor" atau role lain ke user yang baru dibuat

        return response()->json($employee, 201);
    }

    /**
     * Menampilkan satu karyawan spesifik.
     */
    public function show(Employee $employee)
    {
        // Route model binding akan otomatis menemukan employee berdasarkan ID
        return $employee->load('user:id,email');
    }

    /**
     * Mengupdate data karyawan.
     */
    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        $employee->update($validatedData);

        return response()->json($employee);
    }

    /**
     * Menghapus data karyawan.
     */
    public function destroy(Employee $employee)
    {
        // Juga hapus user login terkait untuk kebersihan data
        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        return response()->json(null, 204); // 204 No Content
    }
}
