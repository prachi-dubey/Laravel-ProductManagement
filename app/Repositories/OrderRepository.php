<?php

namespace App\Repositories;

use App\Interfaces\Order\OrderRepositoryInterface;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return Order::query()->create($data);
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
        return Order::query()->where('number', $number)->exists();
    }

    public function findAddressForUser(int $addressId, int $userId): ?Address
    {
        return Address::query()
            ->where('id', $addressId)
            ->where('user_id', $userId)
            ->first();
    }

    public function paginateForViewer(User $user, int $perPage): LengthAwarePaginator
    {
        $builder = Order::query()->with(['items'])->latest('placed_at');

        if (! $user->isAdmin()) {
            $builder->where('user_id', $user->id);
        }

        return $builder->paginate($perPage);
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Order $order, array $relations = ['items', 'address']): Order
    {
        return $order->load($relations);
    }
}
