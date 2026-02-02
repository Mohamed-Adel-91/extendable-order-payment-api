<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Http\Requests\AdminUpdateOrderStatusRequest;

class AdminOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $orders = Order::query()
            ->with('items')
            ->latest('id')
            ->paginate($perPage);

        return $this->successResponse([
            'orders' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'count' => $orders->count(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Order $order)
    {
        $order->load('items');

        return $this->successResponse($order);
    }

    public function updateStatus(AdminUpdateOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();

        $newStatus = (int) $data['status'];
        $cancelReason = $data['cancel_reason'] ?? null;

        $updated = $this->orderService->updateStatusByAdmin(
            $request->user('admin_api'),
            $order,
            $newStatus,
            $cancelReason
        );

        return $this->successResponse($updated, message: 'Order status updated successfully');
    }
}
