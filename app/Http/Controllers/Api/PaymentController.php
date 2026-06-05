<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function generatePaymentId(): string
    {
        $last = Payment::where('payment_id', 'like', 'PAY%')
            ->orderBy('payment_id', 'desc')
            ->first();
        $number = $last ? ((int) substr($last->payment_id, 3)) + 1 : 1;
        return 'PAY' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $payments = Payment::with([
            'order.customer',
            'order.vehicle',
            'order.user',
            'order.mechanic',
            'order.details.item'
        ])
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diambil',
            'data' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:orders,order_id|unique:payments,order_id',
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Order yang sudah dibatalkan tidak bisa dibayar'
            ], 422);
        }

        if ($request->paid_amount < $order->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah bayar kurang dari total order',
                'total_amount' => $order->total_amount,
                'paid_amount' => $request->paid_amount
            ], 422);
        }

        $changeAmount = $request->paid_amount - $order->total_amount;

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'payment_id' => $this->generatePaymentId(),
                'order_id' => $request->order_id,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $changeAmount,
                'payment_status' => 'paid',
                'payment_date' => $request->payment_date ?? now(),
            ]);

            $order->update([
                'order_status' => 'completed',
            ]);

            DB::commit();

            $payment->load([
                'order.customer',
                'order.vehicle',
                'order.user',
                'order.mechanic',
                'order.details.item'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => $payment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal diproses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $payment = Payment::with([
            'order.customer',
            'order.vehicle',
            'order.user',
            'order.mechanic',
            'order.details.item'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pembayaran berhasil diambil',
            'data' => $payment
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'payment_method' => 'sometimes|required|in:cash,qris,transfer,debit',
            'paid_amount' => 'sometimes|required|numeric|min:0',
            'payment_status' => 'sometimes|required|in:unpaid,paid,cancelled',
            'payment_date' => 'nullable|date',
        ]);

        $order = Order::with('details.item')->findOrFail($payment->order_id);

        $paidAmount = $request->paid_amount ?? $payment->paid_amount;
        $newStatus = $request->payment_status ?? $payment->payment_status;

        if ($paidAmount < $order->total_amount && $newStatus === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah bayar kurang dari total order'
            ], 422);
        }

        $changeAmount = $paidAmount - $order->total_amount;

        DB::beginTransaction();

        try {
            $payment->update([
                'payment_method' => $request->payment_method ?? $payment->payment_method,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_status' => $newStatus,
                'payment_date' => $request->payment_date ?? $payment->payment_date,
            ]);

            if ($newStatus === 'paid') {
                $order->update([
                    'order_status' => 'completed',
                ]);
            }

            if ($newStatus === 'cancelled') {
                foreach ($order->details as $detail) {
                    $item = $detail->item;
                    if ($item && $item->stock !== null) {
                        $item->stock += $detail->quantity;
                        $item->save();
                    }
                }

                $order->update([
                    'order_status' => 'process',
                ]);
            }

            DB::commit();

            $payment->load([
                'order.customer',
                'order.vehicle',
                'order.user',
                'order.mechanic',
                'order.details.item'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diperbarui',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal diperbarui',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($payment->order_id);

            $payment->delete();

            $order->update([
                'order_status' => 'process',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal dihapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}