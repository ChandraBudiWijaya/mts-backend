<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponse; // Import trait
use App\Http\Resources\WorkPlanResource; // Import resource

class WorkPlanController extends Controller
{
    use ApiResponse; // Gunakan trait

    public function index()
    {
        $user = Auth::user();
        $query = WorkPlan::with(['employee', 'geofence', 'creator', 'approver'])->latest();

        if ($user->roles->contains('slug', 'mandor') && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        $workPlans = $query->get();

        return $this->successResponse(
            WorkPlanResource::collection($workPlans),
            'Data rencana kerja berhasil diambil.'
        );
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|string|exists:employees,id',
            'geofence_id' => 'required|integer|exists:geofences,id',
            'spk_number' => 'nullable|string|max:255',
            'activity_description' => 'required|string',
        ]);

        $workPlan = WorkPlan::create([
            'date' => $validatedData['date'],
            'employee_id' => $validatedData['employee_id'],
            'geofence_id' => $validatedData['geofence_id'],
            'spk_number' => $validatedData['spk_number'],
            'activity_description' => $validatedData['activity_description'],
            'status' => 'Submitted',
            'created_by' => Auth::id(),
        ]);

        return $this->successResponse(
            new WorkPlanResource($workPlan->load(['employee', 'geofence', 'creator'])),
            'Rencana kerja berhasil dibuat.',
            201
        );
    }

    public function show(WorkPlan $workPlan)
    {
        return $this->successResponse(
            new WorkPlanResource($workPlan->load(['employee', 'geofence', 'creator', 'approver'])),
            'Detail rencana kerja berhasil diambil.'
        );
    }

    public function update(Request $request, WorkPlan $workPlan)
    {
        if (!in_array($workPlan->status, ['Draft', 'Submitted'])) {
            return $this->errorResponse('Rencana kerja tidak dapat diubah.', 403);
        }

        $validatedData = $request->validate([
            'date' => 'sometimes|required|date',
            'employee_id' => 'sometimes|required|string|exists:employees,id',
            'geofence_id' => 'sometimes|required|integer|exists:geofences,id',
            'spk_number' => 'nullable|string|max:255',
            'activity_description' => 'sometimes|required|string',
        ]);

        $workPlan->update($validatedData);

        return $this->successResponse(
            new WorkPlanResource($workPlan->load(['employee', 'geofence', 'creator', 'approver'])),
            'Rencana kerja berhasil diperbarui.'
        );
    }

    public function destroy(WorkPlan $workPlan)
    {
        $workPlan->delete();
        return $this->successResponse(null, 'Rencana kerja berhasil dihapus.');
    }

    public function approve(WorkPlan $workPlan)
    {
        $workPlan->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
        ]);

        return $this->successResponse(
            new WorkPlanResource($workPlan->load(['employee', 'geofence', 'creator', 'approver'])),
            'Rencana kerja berhasil disetujui.'
        );
    }
}
