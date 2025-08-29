<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan daftar semua role.
     */
    public function index()
    {
        // Eager load permissions untuk efisiensi
        $roles = Role::with('permissions')->get();
        return $this->successResponse(RoleResource::collection($roles), 'Data roles berhasil diambil.');
    }

    /**
     * Menyimpan role baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id', // Validasi setiap item di array
        ]);

        // Gunakan transaction untuk memastikan data konsisten
        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validated['name'], 'slug' => \Str::slug($validated['name'])]);
            $role->permissions()->sync($validated['permissions']);
            DB::commit();

            return $this->successResponse(new RoleResource($role->load('permissions')), 'Role berhasil dibuat.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal membuat role.', 500, $e->getMessage());
        }
    }

    /**
     * Menampilkan detail satu role.
     */
    public function show(Role $role)
    {
        return $this->successResponse(new RoleResource($role->load('permissions')), 'Detail role berhasil diambil.');
    }

    /**
     * Mengupdate role yang ada.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $validated['name'], 'slug' => \Str::slug($validated['name'])]);
            $role->permissions()->sync($validated['permissions']);
            DB::commit();

            return $this->successResponse(new RoleResource($role->load('permissions')), 'Role berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal memperbarui role.', 500, $e->getMessage());
        }
    }

    /**
     * Menghapus role.
     */
    public function destroy(Role $role)
    {
        // Tambahkan validasi agar role Super Admin tidak bisa dihapus
        if ($role->slug === 'super-admin') {
            return $this->errorResponse('Role Super Admin tidak dapat dihapus.', 403);
        }

        $role->delete();
        return $this->successResponse(null, 'Role berhasil dihapus.');
    }

    /**
     * Mengambil semua permission yang tersedia.
     */
    /**
     * Mengambil semua permission yang tersedia, dengan caching.
     */
    public function allPermissions()
    {
        // 2. Gunakan Cache::remember()
        $permissions = Cache::remember('all_permissions', now()->addHours(24), function () {
            // Blok kode ini hanya akan dijalankan jika 'all_permissions' tidak ada di cache.
            return Permission::all();
        });

        return $this->successResponse($permissions, 'Data permissions berhasil diambil.');
    }

}
