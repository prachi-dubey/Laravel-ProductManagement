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
        $user = User::create($data);
        $user->profile()->create([]);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $profile = $user->profile ?? $user->profile()->create([]);
        $profile->update($data);

        return $user->load('profile');
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(User $user, array $relations = ['profile']): User
    {
        return $user->load($relations);
    }
}
