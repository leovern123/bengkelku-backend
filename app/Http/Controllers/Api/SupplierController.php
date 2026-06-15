<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function generateSupplierId(): string
    {
        $last = Supplier::where('supplier_id', 'like', 'SUP%')
            ->orderBy('supplier_id', 'desc')
            ->first();
        $number = $last ? ((int) substr($last->supplier_id, 3)) + 1 : 1;
        return 'SUP' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $suppliers = Supplier::with('items')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data supplier berhasil diambil',
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create([
            'supplier_id' => $this->generateSupplierId(),
            'supplier_name' => $request->supplier_name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier
        ], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('items.category.itemType')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail supplier berhasil diambil',
            'data' => $supplier
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplier_name' => 'sometimes|required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update([
            'supplier_name' => $request->supplier_name ?? $supplier->supplier_name,
            'phone_number' => $request->phone_number ?? $supplier->phone_number,
            'address' => $request->address ?? $supplier->address,
            'notes' => $request->notes ?? $supplier->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diperbarui',
            'data' => $supplier
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}