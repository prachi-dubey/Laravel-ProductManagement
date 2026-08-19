<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helper\ApiListHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = ApiListHelper::perPage($request)['per_page'];
        $paginator = $request->user()
            ->notifications()
            ->paginate($perPage)
            ->appends($request->input());

        return response()->json([
            'success' => true,
            'message' => __('messages.notifications.listed'),
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

}
