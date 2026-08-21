<?php

namespace App\Repositories;

use App\Helper\ApiListHelper;
use App\Interfaces\Order\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function addItem(Order $order, array $item): void
    {
        $order->items()->create($item);
    }

    public function numberExists(string $number): bool
    {
        return Order::where('number', $number)->exists();
    }

    public function findShippingProfile(User $user): ?Profile
    {
        $profile = $user->profile;

        if (! $profile || ! $profile->hasShippingAddress()) {
            return null;
        }

        return $profile;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForViewer(User $user, array $filters): LengthAwarePaginator
    {
        $builder = Order::with(['items']);

        if (! $user->isAdmin()) {
            $builder->where('user_id', $user->id);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $builder->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('shipping_city', 'like', "%{$search}%")
                    ->orWhere('shipping_postal_code', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        ApiListHelper::applySort(
            $builder,
            $filters['sort'] ?? '',
            $filters['sort_direction'] ?? '',
            ['id', 'number', 'status', 'total', 'placed_at', 'created_at', 'updated_at'],
            'placed_at',
            'desc',
        );

        $perPage = ApiListHelper::perPage($filters['per_page'] ?? null);

        return $builder->paginate($perPage);
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Order $order, array $relations = ['items']): Order
    {
        return $order->load($relations);
    }
}
