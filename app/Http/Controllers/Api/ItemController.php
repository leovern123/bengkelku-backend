<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    private function generateItemId(): string
    {
        $last = Item::where('item_id', 'like', 'ITM%')
            ->orderBy('item_id', 'desc')
            ->first();
        $number = $last ? ((int) substr($last->item_id, 3)) + 1 : 1;
        return 'ITM' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $items = Item::with(['category.itemType', 'supplier'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data item berhasil diambil',
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_category_id' => 'required|integer|exists:item_categories,item_category_id',
            'supplier_id' => 'nullable|string|exists:suppliers,supplier_id',
            'item_name' => 'required|string|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
        ]);

        $item = Item::create([
            'item_id' => $this->generateItemId(),
            'item_category_id' => $request->item_category_id,
            'supplier_id' => $request->supplier_id,
            'item_name' => $request->item_name,
            'purchase_price' => $request->purchase_price ?? 0,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'image' => $request->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
       $item = Item::with(['category.itemType', 'supplier'])
             ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail item berhasil diambil',
            'data' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'item_category_id' => 'sometimes|required|integer|exists:item_categories,item_category_id',
            'item_name' => 'sometimes|required|string|max:100',
            'supplier_id' => 'nullable|string|exists:suppliers,supplier_id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
        ]);

        $item->update([
            'item_category_id' => $request->item_category_id ?? $item->item_category_id,
            'item_name' => $request->item_name ?? $item->item_name,
            'supplier_id' => $request->supplier_id ?? $item->supplier_id,
            'purchase_price' => $request->purchase_price ?? $item->purchase_price,
            'selling_price' => $request->selling_price ?? $item->selling_price,
            'stock' => $request->stock ?? $item->stock,
            'image' => $request->image ?? $item->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil diperbarui',
            'data' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus'
        ]);
    }
}