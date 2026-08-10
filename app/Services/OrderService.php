<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidAddressException;
use App\Exceptions\ProductUnavailableException;
use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
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

    public function paginateForViewer(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->orders->paginateForViewer($user, $perPage);
    }

    /**
     * Place an order for the given user.
     *
     * Expected $data:
     * - address_id (int, belongs to user)
     * - items: list of { product_id, quantity }
     *
     * @param  array<string, mixed>  $data
     */
    public function place(User $user, array $data): Order
    {
        $address = $this->orders->findAddressForUser(
            (int) $data['address_id'],
            $user->id
        );

        if (! $address) {
            throw new InvalidAddressException();
        }

        $order = DB::transaction(function () use ($user, $address, $data) {
            $lines = [];
            $subtotal = 0;

            foreach ($data['items'] as $row) {
                $product = $this->products->findForUpdate((int) $row['product_id']);

                if (! $product || ! $product->is_active) {
                    throw new ProductUnavailableException();
                }

                $qty = (int) $row['quantity'];

                if ($product->stock < $qty) {
                    throw new InsufficientStockException($product->name, $product->stock);
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

            $order = $this->orders->create([
                'number' => $this->generateNumber(),
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'placed_at' => now(),
            ]);

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

            return $this->orders->loadRelations($order, ['items.product', 'address', 'user']);
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
