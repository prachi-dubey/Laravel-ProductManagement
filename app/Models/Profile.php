<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone',
        'avatar_path',
        'bio',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasShippingAddress(): bool
    {
        return filled($this->line1)
            && filled($this->city)
            && filled($this->postal_code)
            && filled($this->country);
    }

    /**
     * Snapshot copied onto an order at checkout.
     *
     * @return array<string, mixed>
     */
    public function shippingSnapshot(): array
    {
        return [
            'shipping_line1' => $this->line1,
            'shipping_line2' => $this->line2,
            'shipping_city' => $this->city,
            'shipping_state' => $this->state,
            'shipping_postal_code' => $this->postal_code,
            'shipping_country' => $this->country,
        ];
    }
}
