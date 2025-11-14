<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        if ($status) {
            $orders = $this->orderService->getByStatus($status);
        } else {
            $orders = $this->orderService->getAll();
        }

        return view('admin.orders.index', compact('orders', 'status'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = $this->orderService->getOne($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:191',
        ]);

        $this->orderService->updateStatus($id, $validated['status']);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Trạng thái đơn hàng đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->orderService->delete($id);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Đơn hàng đã được xóa thành công!');
    }
}
