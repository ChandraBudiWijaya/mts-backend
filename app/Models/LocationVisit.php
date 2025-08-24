<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationVisit extends Model
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
        'geofence_id',
        'entry_time',
        'exit_time',
        'visit_duration_minutes',
    ];

    /**
     * Mendefinisikan relasi ke model Employee.
     * Setiap kunjungan dimiliki oleh satu Employee.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
