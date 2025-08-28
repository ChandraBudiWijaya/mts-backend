<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\EmployeeResource;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary; // 1. Import Cloudinary

class EmployeeController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan daftar semua karyawan dengan fungsionalitas filter.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('email', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($request->has('pg')) {
            $query->where('plantation_group', $request->pg);
        }

        if ($request->has('wilayah')) {
            $query->where('wilayah', $request->wilayah);
        }

        $employees = $query->with('user.roles')->get();
        return $this->successResponse(EmployeeResource::collection($employees), 'Data karyawan berhasil diambil.');
    }

    /**
     * Menyimpan karyawan baru sesuai dengan data dari form.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->roles()->attach($validatedData['role_id']);

        $employee = Employee::create([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'user_id' => $user->id,
            'position' => $validatedData['position'],
            'plantation_group' => $validatedData['plantation_group'],
            'wilayah' => $validatedData['wilayah'],
            'phone' => $validatedData['phone'],
        ]);

        return $this->successResponse(new EmployeeResource($employee->load('user.roles')), 'Karyawan baru berhasil dibuat.', 201);
    }

    /**
     * Menampilkan satu karyawan spesifik.
     */
    public function show(Employee $employee)
    {
        $employeeData = $employee->load('user.roles');
        return $this->successResponse(new EmployeeResource($employeeData), 'Detail karyawan berhasil diambil.');
    }

    /**
     * Mengupdate data karyawan dan rolenya.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validatedData = $request->validated();

        $employee->update($validatedData);

        if (isset($validatedData['role_id']) && $employee->user) {
            $employee->user->roles()->sync([$validatedData['role_id']]);
        }

        return $this->successResponse(new EmployeeResource($employee->load('user.roles')), 'Data karyawan berhasil diperbarui.');
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

        return $this->successResponse(null, 'Karyawan berhasil dihapus.');
    }

    /**
     * 2. Menambahkan method baru untuk mengupdate foto profil.
     */
    public function updatePhoto(Request $request, Employee $employee)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $uploadedFile = $request->file('photo');

        // Hapus foto lama di Cloudinary jika ada
        if ($employee->photo_url) {
            $pathInfo = pathinfo($employee->photo_url);
            $publicId = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
            Cloudinary::destroy($publicId);
        }

        // Upload file baru ke folder 'profile-photos' di Cloudinary
        $uploadedFileUrl = Cloudinary::upload($uploadedFile->getRealPath(), [
            'folder' => 'profile-photos'
        ])->getSecurePath();

        // Simpan URL baru ke database
        $employee->update(['photo_url' => $uploadedFileUrl]);

        return $this->successResponse(new EmployeeResource($employee), 'Foto profil berhasil diperbarui.');
    }
}
