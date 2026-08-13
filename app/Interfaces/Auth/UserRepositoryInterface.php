<?php

namespace App\Interfaces\Auth;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(User $user, array $relations = ['profile']): User;
}
