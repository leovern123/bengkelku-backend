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

    private function saveBase64Photo(string $base64, string $photoName, ?string $oldPath = null): string
    {
        if ($oldPath) {
            $oldFull = storage_path('app/public/' . $oldPath);
            if (file_exists($oldFull)) @unlink($oldFull);
        }
        $ext = strtolower(pathinfo($photoName, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = 'mechanics/' . uniqid() . '.' . $ext;
        $fullPath = storage_path('app/public/' . $filename);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, base64_decode($base64));
        return $filename;
    }

    public function store(Request $request)
    {
        $request->validate([
            'mechanic_name' => 'required|string|max:100',
            'nik' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'photo_base64' => 'nullable|string',
            'photo_name' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($request->photo_base64) {
            $photoPath = $this->saveBase64Photo($request->photo_base64, $request->photo_name ?? 'photo.jpg');
        }

        $mechanic = Mechanic::create([
            'mechanic_id' => $this->generateMechanicId(),
            'mechanic_name' => $request->mechanic_name,
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
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
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'photo_base64' => 'nullable|string',
            'photo_name' => 'nullable|string',
        ]);

        $photoPath = $mechanic->photo;
        if ($request->photo_base64) {
            $photoPath = $this->saveBase64Photo($request->photo_base64, $request->photo_name ?? 'photo.jpg', $mechanic->photo);
        }

        $mechanic->update([
            'mechanic_name' => $request->mechanic_name ?? $mechanic->mechanic_name,
            'nik' => $request->nik ?? $mechanic->nik,
            'phone_number' => $request->phone_number ?? $mechanic->phone_number,
            'notes' => $request->notes ?? $mechanic->notes,
            'address' => $request->address ?? $mechanic->address,
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
