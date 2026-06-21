<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderDetailController extends Controller
{
    private function generateDetailId(): string
    {
        $last = OrderDetail::where('order_detail_id', 'like', 'OD%')
            ->orderBy('order_detail_id', 'desc')
            ->first();
        $number = $last ? ((int) substr($last->order_detail_id, 2)) + 1 : 1;
        return 'OD' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:orders,order_id',
            'item_id' => 'required|string|exists:items,item_id',
            'quantity' => 'required|integer|min:1',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menambahkan item ke order yang sudah ' . $order->order_status,
            ], 422);
        }

        $item = Item::findOrFail($request->item_id);

        if ($item->stock !== null && $item->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Stok item {$item->item_name} tidak cukup (tersisa {$item->stock})",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $detail = OrderDetail::create([
                'order_detail_id' => $this->generateDetailId(),
                'order_id' => $request->order_id,
                'item_id' => $request->item_id,
                'quantity' => $request->quantity,
                'purchase_price_at_transaction' => $item->purchase_price,
                'selling_price_at_transaction' => $item->selling_price,
                'subtotal' => $item->selling_price * $request->quantity,
            ]);

            if ($item->stock !== null) {
                $item->stock -= $request->quantity;
                $item->save();
            }

            $total = OrderDetail::where('order_id', $request->order_id)->sum('subtotal');
            $order->update(['total_amount' => $total]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke order',
                'data' => $detail->load('item'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $detail = OrderDetail::findOrFail($id);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $order = Order::findOrFail($detail->order_id);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengubah item pada order yang sudah ' . $order->order_status,
            ], 422);
        }

        $item = Item::findOrFail($detail->item_id);
        $diff = $request->quantity - $detail->quantity;

        if ($item->stock !== null && $diff > 0 && $item->stock < $diff) {
            return response()->json([
                'success' => false,
                'message' => "Stok item {$item->item_name} tidak cukup",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $detail->update([
                'quantity' => $request->quantity,
                'subtotal' => $item->selling_price * $request->quantity,
            ]);

            if ($item->stock !== null) {
                $item->stock -= $diff;
                $item->save();
            }

            $total = OrderDetail::where('order_id', $detail->order_id)->sum('subtotal');
            $order->update(['total_amount' => $total]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diperbarui',
                'data' => $detail->load('item'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $detail = OrderDetail::findOrFail($id);
        $order = Order::findOrFail($detail->order_id);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus item dari order yang sudah ' . $order->order_status,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $item = Item::find($detail->item_id);
            if ($item && $item->stock !== null) {
                $item->stock += $detail->quantity;
                $item->save();
            }

            $detail->delete();

            $total = OrderDetail::where('order_id', $order->order_id)->sum('subtotal');
            $order->update(['total_amount' => $total]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari order',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
