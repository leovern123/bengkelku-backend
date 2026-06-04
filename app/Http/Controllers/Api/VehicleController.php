<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('customer')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kendaraan berhasil diambil',
            'data' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|string|max:20|unique:vehicles,vehicle_id',
            'customer_id' => 'required|string|exists:customers,customer_id',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_id' => $request->vehicle_id,
            'customer_id' => $request->customer_id,
            'license_plate' => $request->license_plate,
            'brand' => $request->brand,
            'model' => $request->model,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil ditambahkan',
            'data' => $vehicle
        ], 201);
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('customer')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail kendaraan berhasil diambil',
            'data' => $vehicle
        ]);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'customer_id' => 'sometimes|required|string|exists:customers,customer_id',
            'license_plate' => 'sometimes|required|string|max:20|unique:vehicles,license_plate,' . $id . ',vehicle_id',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
        ]);

        $vehicle->update([
            'customer_id' => $request->customer_id ?? $vehicle->customer_id,
            'license_plate' => $request->license_plate ?? $vehicle->license_plate,
            'brand' => $request->brand ?? $vehicle->brand,
            'model' => $request->model ?? $vehicle->model,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil diperbarui',
            'data' => $vehicle
        ]);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil dihapus'
        ]);
    }
}