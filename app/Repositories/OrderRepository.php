<?php

namespace App\Repositories;

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

    public function paginateForViewer(User $user, int $perPage): LengthAwarePaginator
    {
        $builder = Order::with(['items'])->latest('placed_at');

        if (! $user->isAdmin()) {
            $builder->where('user_id', $user->id);
        }

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
