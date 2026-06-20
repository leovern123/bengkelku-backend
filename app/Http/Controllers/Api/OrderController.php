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
    private function generateOrderId(): string
    {
        $last = Order::where('order_id', 'like', 'ORD%')
            ->orderBy('order_id', 'desc')
            ->first();
        $number = $last ? ((int) substr($last->order_id, 3)) + 1 : 1;
        return 'ORD' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

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
            'transaction_type' => 'nullable|in:service,product_sale',
            'customer_id'      => 'nullable|string|exists:customers,customer_id',
            'vehicle_id'       => 'nullable|string|exists:vehicles,vehicle_id',
            'user_id'          => 'required|string|exists:users,user_id',
            'mechanic_id'      => 'nullable|string|exists:mechanics,mechanic_id',
            'order_status'     => 'nullable|in:pending,process,completed,cancelled',
        ]);

        $transactionType = $request->transaction_type ?? 'service';

        if ($transactionType === 'service') {
            if (empty($request->customer_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => ['customer_id' => ['Customer wajib diisi untuk servis kendaraan']],
                ], 422);
            }
            if (empty($request->vehicle_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => ['vehicle_id' => ['Kendaraan wajib diisi untuk servis kendaraan']],
                ], 422);
            }
        }

        $orderId = $this->generateOrderId();

        $order = Order::create([
            'order_id'         => $orderId,
            'transaction_type' => $transactionType,
            'customer_id'      => $request->customer_id ?: null,
            'vehicle_id'       => $request->vehicle_id ?: null,
            'user_id'          => $request->user_id,
            'mechanic_id'      => $request->mechanic_id,
            'order_code'       => 'WO-' . $orderId,
            'order_status'     => $request->order_status ?? 'pending',
            'total_amount'     => 0,
        ]);

        try {
            $order->load([
                'customer',
                'vehicle',
                'user',
                'mechanic',
                'details.item',
                'payment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
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
            if ($order->order_status === 'completed') {
                foreach ($order->details as $detail) {
                    $item = $detail->item;
                    if ($item && !is_null($item->stock)) {
                        $item->stock += $detail->quantity;
                        $item->save();
                    }
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

    public function process($id)
    {
        $order = Order::findOrFail($id);

        if ($order->order_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya order dengan status pending yang bisa diproses'
            ], 422);
        }

        $order->update(['order_status' => 'process']);

        return response()->json([
            'success' => true,
            'message' => 'Order sedang diproses',
            'data' => $order
        ]);
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

    public function cancel($id, Request $request)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        $order = Order::with('details.item')->findOrFail($id);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order dengan status ' . $order->order_status . ' tidak bisa dibatalkan'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order->update([
                'order_status' => 'cancelled',
                'cancel_reason' => $request->cancel_reason,
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