<?php

namespace App\Repositories\Contracts;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order;

    /**
     * @param  array<string, mixed>  $item
     */
    public function addItem(Order $order, array $item): void;

    public function numberExists(string $number): bool;

    public function findAddressForUser(int $addressId, int $userId): ?Address;

    public function paginateForViewer(User $user, int $perPage): LengthAwarePaginator;

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Order $order, array $relations = ['items', 'address']): Order;
}
