<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'employee_id',
        'total_work_minutes',
        'total_outside_area_minutes',
        'total_distance_km',
        'last_update',
    ];

    /**
     * Mendefinisikan relasi ke model Employee.
     * Setiap ringkasan harian dimiliki oleh satu Employee.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
