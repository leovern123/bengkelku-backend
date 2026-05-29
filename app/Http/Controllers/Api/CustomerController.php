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

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string|max:20|unique:customers,customer_id',
            'customer_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'note' => $request->note,
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
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $customer->update([
            'customer_name' => $request->customer_name ?? $customer->customer_name,
            'phone_number' => $request->phone_number ?? $customer->phone_number,
            'address' => $request->address ?? $customer->address,
            'note' => $request->note ?? $customer->note,
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