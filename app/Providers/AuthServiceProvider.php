<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Kode ini akan mendaftarkan semua izin dari database ke Laravel
        try {
            // Loop melalui semua izin yang ada di database
            foreach (Permission::all() as $permission) {
                // Daftarkan Gate untuk setiap izin
                Gate::define($permission->slug, function (User $user) use ($permission) {
                    // Gunakan fungsi hasPermission yang sudah kita buat di model User
                    return $user->hasPermission($permission->slug);
                });
            }
        } catch (\Exception $e) {
            // Menangani error jika migrasi belum dijalankan.
            // Biarkan kosong agar tidak mengganggu proses `php artisan migrate`.
        }
    }
}
