<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mechanic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    private function generateMechanicId(): string
    {
        $last = Mechanic::orderBy('mechanic_id', 'desc')->first();
        $num = $last ? (int) substr($last->mechanic_id, 3) + 1 : 1;
        return 'MCN' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mechanic_name' => 'required|string|max:100',
            'nik' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('mechanics', 'public');
        }

        $mechanic = Mechanic::create([
            'mechanic_id' => $this->generateMechanicId(),
            'mechanic_name' => $request->mechanic_name,
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
            'notes' => $request->notes,
            'photo' => $photoPath,
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
            'nik' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoPath = $mechanic->photo;
        if ($request->hasFile('photo')) {
            if ($mechanic->photo) {
                Storage::disk('public')->delete($mechanic->photo);
            }
            $photoPath = $request->file('photo')->store('mechanics', 'public');
        }

        $mechanic->update([
            'mechanic_name' => $request->mechanic_name ?? $mechanic->mechanic_name,
            'nik' => $request->nik ?? $mechanic->nik,
            'phone_number' => $request->phone_number ?? $mechanic->phone_number,
            'notes' => $request->notes ?? $mechanic->notes,
            'photo' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mekanik berhasil diperbarui',
            'data' => $mechanic->fresh()
        ]);
    }

    public function destroy($id)
    {
        $mechanic = Mechanic::findOrFail($id);
        if ($mechanic->photo) {
            Storage::disk('public')->delete($mechanic->photo);
        }
        $mechanic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mekanik berhasil dihapus'
        ]);
    }
}
