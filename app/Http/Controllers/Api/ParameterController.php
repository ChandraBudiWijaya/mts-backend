<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MstParam;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\ParameterResource;

class ParameterController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan semua parameter.
     */
    public function index()
    {
        $parameters = MstParam::all();
        return $this->successResponse(ParameterResource::collection($parameters), 'Data parameter berhasil diambil.');
    }

    /**
     * Mengupdate parameter yang ada.
     * Kita hanya akan mengizinkan update, bukan pembuatan atau penghapusan,
     * karena key parameter biasanya sudah didefinisikan oleh sistem.
     */
    public function update(Request $request, MstParam $parameter)
    {
        $validated = $request->validate([
            'param_value' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $parameter->update([
            'param_value' => $validated['param_value'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        return $this->successResponse(new ParameterResource($parameter), 'Parameter berhasil diperbarui.');
    }

    /**
     * Menampilkan satu parameter.
     */
    public function show(MstParam $parameter)
    {
        return $this->successResponse(new ParameterResource($parameter), 'Detail parameter berhasil diambil.');
    }
}
