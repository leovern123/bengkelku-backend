<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::with('itemType')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kategori item berhasil diambil',
            'data' => $categories
        ]);
    }

    private function generateCategoryId(): int
    {
        $last = ItemCategory::orderBy('item_category_id', 'desc')->first();
        return $last ? $last->item_category_id + 1 : 1;
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_type_id' => 'required|integer|exists:item_types,item_type_id',
            'category_name' => 'required|string|max:100',
        ]);

        $category = ItemCategory::create([
            'item_category_id' => $this->generateCategoryId(),
            'item_type_id' => $request->item_type_id,
            'category_name' => $request->category_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori item berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    public function show($id)
    {
        $category = ItemCategory::with(['itemType', 'items'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori item berhasil diambil',
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        $request->validate([
            'item_type_id' => 'sometimes|required|integer|exists:item_types,item_type_id',
            'category_name' => 'sometimes|required|string|max:100',
        ]);

        $category->update([
            'item_type_id' => $request->item_type_id ?? $category->item_type_id,
            'category_name' => $request->category_name ?? $category->category_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori item berhasil diperbarui',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori item berhasil dihapus'
        ]);
    }
}