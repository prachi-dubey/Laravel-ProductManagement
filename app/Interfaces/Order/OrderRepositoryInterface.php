<?php

namespace App\Interfaces\Order;

use App\Models\Order;
use App\Models\Profile;
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

    public function findShippingProfile(User $user): ?Profile;

    public function paginateForViewer(User $user, int $perPage): LengthAwarePaginator;

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Order $order, array $relations = ['items']): Order;
}
