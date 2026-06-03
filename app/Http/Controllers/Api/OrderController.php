<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'customer',
            'vehicle',
            'user',
            'mechanic',
            'details.item.category.itemType',
            'payment'
        ])
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data order berhasil diambil',
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|max:20|unique:orders,order_id',
            'customer_id' => 'required|string|exists:customers,customer_id',
            'vehicle_id' => 'required|string|exists:vehicles,vehicle_id',
            'user_id' => 'required|string|exists:users,user_id',
            'mechanic_id' => 'nullable|string|exists:mechanics,mechanic_id',
            'order_code' => 'required|string|max:50|unique:orders,order_code',
            'order_status' => 'nullable|in:pending,process,completed,cancelled',
            'details' => 'required|array|min:1',
            'details.*.order_detail_id' => 'required|string|max:20|distinct|unique:order_details,order_detail_id',
            'details.*.item_id' => 'required|string|exists:items,item_id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = 0;

            $order = Order::create([
                'order_id' => $request->order_id,
                'customer_id' => $request->customer_id,
                'vehicle_id' => $request->vehicle_id,
                'user_id' => $request->user_id,
                'mechanic_id' => $request->mechanic_id,
                'order_code' => $request->order_code,
                'order_status' => $request->order_status ?? 'pending',
                'total_amount' => 0,
            ]);

            foreach ($request->details as $detail) {
                $item = Item::with('category.itemType')->findOrFail($detail['item_id']);

                $quantity = $detail['quantity'];
                $purchasePrice = $item->purchase_price;
                $sellingPrice = $item->selling_price;
                $subtotal = $sellingPrice * $quantity;

                OrderDetail::create([
                    'order_detail_id' => $detail['order_detail_id'],
                    'order_id' => $order->order_id,
                    'item_id' => $item->item_id,
                    'quantity' => $quantity,
                    'purchase_price_at_transaction' => $purchasePrice,
                    'selling_price_at_transaction' => $sellingPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;

                // Kurangi stok hanya jika item punya stok.
                // Item jenis service biasanya stock = null.
                if (!is_null($item->stock)) {
                    if ($item->stock < $quantity) {
                        throw new \Exception("Stok item {$item->item_name} tidak cukup");
                    }

                    $item->stock -= $quantity;
                    $item->save();
                }
            }

            $order->update([
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            $order->load([
                'customer',
                'vehicle',
                'user',
                'mechanic',
                'details.item.category.itemType',
                'payment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order gagal dibuat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with([
            'customer',
            'vehicle',
            'user',
            'mechanic',
            'details.item.category.itemType',
            'payment'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail order berhasil diambil',
            'data' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'mechanic_id' => 'nullable|string|exists:mechanics,mechanic_id',
            'order_status' => 'sometimes|required|in:pending,process,completed,cancelled',
        ]);

        $order->update([
            'mechanic_id' => $request->mechanic_id ?? $order->mechanic_id,
            'order_status' => $request->order_status ?? $order->order_status,
        ]);

        $order->load([
            'customer',
            'vehicle',
            'user',
            'mechanic',
            'details.item.category.itemType',
            'payment'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diperbarui',
            'data' => $order
        ]);
    }

    public function destroy($id)
    {
        $order = Order::with('details.item')->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($order->details as $detail) {
                $item = $detail->item;

                if ($item && !is_null($item->stock)) {
                    $item->stock += $detail->quantity;
                    $item->save();
                }
            }

            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order gagal dihapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complete($id)
    {
        $order = Order::findOrFail($id);

        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Order yang sudah dibatalkan tidak bisa diselesaikan'
            ], 422);
        }

        if ($order->order_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah selesai'
            ], 422);
        }

        $order->update([
            'order_status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diselesaikan',
            'data' => $order
        ]);
    }

    public function cancel($id)
    {
        $order = Order::with('details.item')->findOrFail($id);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order dengan status ' . $order->order_status . ' tidak bisa dibatalkan'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($order->details as $detail) {
                $item = $detail->item;

                if ($item && !is_null($item->stock)) {
                    $item->stock += $detail->quantity;
                    $item->save();
                }
            }

            $order->update([
                'order_status' => 'cancelled',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order gagal dibatalkan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}