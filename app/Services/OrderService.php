<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;

class OrderService
{
    /**
     * Get all orders with user.
     */
    public function getAll()
    {
        return Order::with('user')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get orders for user.
     */
    public function getForUser($userId)
    {
        return Order::with('user')
            ->where('id_user', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get orders by status.
     */
    public function getByStatus($status)
    {
        return Order::with('user')
            ->where('status', 'like', "%{$status}%")
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get orders for user by status.
     */
    public function getForUserByStatus($userId, $status)
    {
        return Order::with('user')
            ->where('id_user', $userId)
            ->where('status', 'like', "%{$status}%")
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get latest order for user.
     */
    public function getLatestForUser($userId)
    {
        return Order::where('id_user', $userId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Get one order by id.
     */
    public function getOne($id)
    {
        return Order::with(['user', 'orderDetails.product'])
            ->findOrFail($id);
    }

    /**
     * Create a new order with order details.
     */
    public function create(array $data, array $orderDetails = [])
    {
        $order = Order::create([
            'id_user' => $data['id_user'],
            'status' => $data['status'] ?? 'Chờ lấy hàng',
            'address' => $data['address'],
            'phone' => $data['phone'],
        ]);

        // Add order details
        foreach ($orderDetails as $detail) {
            OrderDetail::create([
                'id_order' => $order->id,
                'id_product' => $detail['id_product'],
                'quantity' => $detail['quantity'],
            ]);
        }

        return $order->load(['user', 'orderDetails.product']);
    }

    /**
     * Update order status.
     */
    public function updateStatus($id, $status)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        return $order;
    }

    /**
     * Delete an order.
     */
    public function delete($id)
    {
        $order = Order::findOrFail($id);
        return $order->delete();
    }
}

