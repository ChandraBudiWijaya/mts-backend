<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Jika user adalah Mandor, hanya tampilkan rencana kerja miliknya
        if ($user->roles->contains('slug', 'mandor')) {
            // Pastikan user tersebut terhubung dengan data employee
            if ($user->employee) {
                 return WorkPlan::where('employee_id', $user->employee->id)
                    ->with(['employee:id,name', 'geofence:id,name', 'creator:id,name'])
                    ->latest()
                    ->get();
            }
            return response()->json([]); // Kembalikan array kosong jika tidak ada data employee
        }

        // Untuk peran lain (Admin, Kabag, Kasie), tampilkan semua
        return WorkPlan::with(['employee:id,name', 'geofence:id,name', 'creator:id,name'])
            ->latest()
            ->get();
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
            'status' => 'Submitted', // Langsung Submitted saat dibuat
            'created_by' => Auth::id(), // Diisi oleh user yang sedang login
        ]);

        return response()->json($workPlan, 201);
    }

    public function show(WorkPlan $workPlan)
    {
        return $workPlan->load(['employee', 'geofence', 'creator', 'approver']);
    }

    public function update(Request $request, WorkPlan $workPlan)
    {
        if (!in_array($workPlan->status, ['Draft', 'Submitted'])) {
            return response()->json(['message' => 'Rencana kerja tidak dapat diubah.'], 403);
        }

        $validatedData = $request->validate([
            'date' => 'sometimes|required|date',
            'employee_id' => 'sometimes|required|string|exists:employees,id',
            'geofence_id' => 'sometimes|required|integer|exists:geofences,id',
            'spk_number' => 'nullable|string|max:255',
            'activity_description' => 'sometimes|required|string',
        ]);

        $workPlan->update($validatedData);

        return response()->json($workPlan);
    }

    public function destroy(WorkPlan $workPlan)
    {
        $workPlan->delete();
        return response()->json(null, 204);
    }

    public function approve(WorkPlan $workPlan)
    {
        $workPlan->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
        ]);

        return response()->json($workPlan);
    }
}
