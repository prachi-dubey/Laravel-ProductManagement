<?php

namespace App\Repositories;

use App\Interfaces\Auth\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(User $user, array $relations = ['profile']): User
    {
        return $user->load($relations);
    }
}
