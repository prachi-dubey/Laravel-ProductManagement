<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\IndexOrderRequest;
use App\Http\Requests\Api\Order\StoreOrderRequest;
use App\Http\Resources\Api\Order\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /** @var OrderService */
    private $orders;

    public function __construct(OrderService $orders)
    {
        $this->orders = $orders;
    }

    public function index(IndexOrderRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $paginator = $this->orders->paginateForViewer(
            $request->user(),
            $request->validated()
        );

        return $this->paginated(
            OrderResource::collection($paginator),
            __('messages.orders.listed')
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orders->place($request->user(), $request->validated());

        return $this->success(
            __('messages.orders.placed'),
            new OrderResource($order),
            Response::HTTP_CREATED
        );
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items']);

        return $this->success(
            __('messages.orders.shown'),
            new OrderResource($order)
        );
    }
}
