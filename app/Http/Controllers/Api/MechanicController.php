<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mechanic;
use Illuminate\Http\Request;

class MechanicController extends Controller
{
    public function index()
    {
        $mechanics = Mechanic::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mekanik berhasil diambil',
            'data' => $mechanics
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mechanic_id' => 'required|string|max:20|unique:mechanics,mechanic_id',
            'mechanic_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:100',
        ]);

        $mechanic = Mechanic::create([
            'mechanic_id' => $request->mechanic_id,
            'mechanic_name' => $request->mechanic_name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'specialization' => $request->specialization,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mekanik berhasil ditambahkan',
            'data' => $mechanic
        ], 201);
    }

    public function show($id)
    {
        $mechanic = Mechanic::with('orders')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail mekanik berhasil diambil',
            'data' => $mechanic
        ]);
    }

    public function update(Request $request, $id)
    {
        $mechanic = Mechanic::findOrFail($id);

        $request->validate([
            'mechanic_name' => 'sometimes|required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:100',
        ]);

        $mechanic->update([
            'mechanic_name' => $request->mechanic_name ?? $mechanic->mechanic_name,
            'phone_number' => $request->phone_number ?? $mechanic->phone_number,
            'address' => $request->address ?? $mechanic->address,
            'specialization' => $request->specialization ?? $mechanic->specialization,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mekanik berhasil diperbarui',
            'data' => $mechanic
        ]);
    }

    public function destroy($id)
    {
        $mechanic = Mechanic::findOrFail($id);
        $mechanic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mekanik berhasil dihapus'
        ]);
    }
}