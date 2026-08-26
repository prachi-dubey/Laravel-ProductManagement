<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Events\Order\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Interfaces\Order\OrderRepositoryInterface;
use App\Interfaces\Product\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Order rules — data access via OrderRepository + ProductRepository.
 */
class OrderService
{
    /** @var OrderRepositoryInterface */
    private $orders;

    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(
        OrderRepositoryInterface $orders,
        ProductRepositoryInterface $products
    ) {
        $this->orders = $orders;
        $this->products = $products;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(User $user, array $filters): LengthAwarePaginator
    {
        return $this->orders->index($user, $filters);
    }

    public function show(Order $order): Order
    {
        return $this->orders->loadRelations($order, ['items']);
    }

    /**
     * Place an order for the given user.
     *
     * Expected $data:
     * - items: list of { product_id, quantity }
     *
     * Shipping address is taken from the user's profile and snapshotted onto the order.
     *
     * @param  array<string, mixed>  $data
     */
    public function place(User $user, array $data): Order
    {
        $profile = $this->orders->findShippingProfile($user);

        if (! $profile) {
            throw ApiException::invalidAddress();
        }

        $order = DB::transaction(function () use ($user, $profile, $data) {
            $lines = [];
            $subtotal = 0;

            foreach ($data['items'] as $row) {
                $product = $this->products->findForUpdate((int) $row['product_id']);

                if (! $product || ! $product->is_active) {
                    throw ApiException::productUnavailable();
                }

                $qty = (int) $row['quantity'];

                if ($product->stock < $qty) {
                    throw ApiException::insufficientStock($product->name, $product->stock);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $qty, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);

            $order = $this->orders->create(array_merge([
                'number' => $this->generateNumber(),
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'placed_at' => now(),
            ], $profile->shippingSnapshot()));

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                $this->orders->addItem($order, [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);

                $this->products->decrementStock($product, $line['quantity']);
            }

            return $this->orders->loadRelations($order, ['items.product', 'user']);
        });

        // After commit: event → listeners → queued jobs → notifications
        OrderPlaced::dispatch($order);

        return $order;
    }

    private function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
        } while ($this->orders->numberExists($number));

        return $number;
    }
}
