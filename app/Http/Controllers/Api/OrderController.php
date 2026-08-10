<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiListQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin HTTP layer — OrderService → repositories → Eloquent.
 */
class OrderController extends Controller
{
    /** @var OrderService */
    private $orders;

    public function __construct(OrderService $orders)
    {
        $this->orders = $orders;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $perPage = ApiListQuery::perPage($request)['per_page'];
        $paginator = $this->orders->paginateForViewer($request->user(), $perPage);
        $paginator->appends($request->query());

        return response()->json(
            ApiListQuery::paginatedResponse(
                OrderResource::collection($paginator),
                'Orders retrieved successfully.'
            )
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orders->place($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items', 'address']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}
