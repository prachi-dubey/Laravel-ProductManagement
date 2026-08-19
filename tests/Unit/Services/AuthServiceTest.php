<?php

namespace Tests\Unit\Services;

use App\Interfaces\Auth\UserRepositoryInterface;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function test_register_forces_customer_role_before_persisting(): void
    {
        $user = User::factory()->create([
            'name' => 'Prachi',
            'email' => 'prachi@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['name'] === 'Prachi'
                    && $payload['email'] === 'prachi@example.com'
                    && $payload['password'] === 'Shop@1234'
                    && $payload['role'] === User::ROLE_CUSTOMER;
            }))
            ->andReturn($user);

        $service = new AuthService($repository);

        $result = $service->register([
            'name' => 'Prachi',
            'email' => 'prachi@example.com',
            'password' => 'Shop@1234',
        ]);

        $this->assertSame($user, $result['user']);
        $this->assertIsString($result['token']);
        $this->assertNotSame('', $result['token']);
    }

    public function test_login_throws_validation_exception_for_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('customer@example.com')
            ->andReturn($user);

        $service = new AuthService($repository);

        $this->expectException(ValidationException::class);

        try {
            $service->login([
                'email' => 'customer@example.com',
                'password' => 'wrong-password',
            ]);
        } catch (ValidationException $exception) {
            $this->assertSame(
                __('messages.auth.credentials_invalid'),
                $exception->errors()['email'][0]
            );

            throw $exception;
        }
    }
}
