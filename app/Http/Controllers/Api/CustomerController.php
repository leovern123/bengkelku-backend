<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('vehicles')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil diambil',
            'data' => $customers
        ]);
    }

    private function generateCustomerId(): string
    {
        $last = Customer::where('customer_id', 'like', 'CU%')
            ->orderBy('customer_id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->customer_id, 2)) + 1 : 1;

        return 'CU' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
        ]);

        $customer = Customer::create([
            'customer_id' => $this->generateCustomerId(),
            'customer_name' => $request->customer_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil ditambahkan',
            'data' => $customer
        ], 201);
    }

    public function show($id)
    {
        $customer = Customer::with('vehicles')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail customer berhasil diambil',
            'data' => $customer
        ]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_name' => 'sometimes|required|string|max:100',
        ]);

        $customer->update([
            'customer_name' => $request->customer_name ?? $customer->customer_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil diperbarui',
            'data' => $customer
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus'
        ]);
    }
}