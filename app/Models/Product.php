<?php

namespace App\Models;

use App\Enums\AuctionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'category',
        'reserve_price',
        'auction_start',
        'auction_end',
        'status',
        'winning_bid_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auction_start' => 'datetime',
            'auction_end' => 'datetime',
            'status' => AuctionStatus::class,
            'reserve_price' => 'decimal:2',
        ];
    }

    /**
     * Get the creator (user) that owns the product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the bids for this product.
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Get the images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Scope a query to only include active auctions.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', AuctionStatus::Active)
            ->where('auction_start', '<=', now())
            ->where('auction_end', '>', now());
    }

    /**
     * Scope a query to only include ended auctions.
     */
    public function scopeEnded(Builder $query): void
    {
        $query->where('auction_end', '<=', now())
            ->whereIn('status', [AuctionStatus::Active, AuctionStatus::Ended]);
    }

    /**
     * Scope a query to only include auctions that need closure.
     */
    public function scopeNeedsClosure(Builder $query): void
    {
        $query->where('auction_end', '<=', now())
            ->whereIn('status', [AuctionStatus::Active, AuctionStatus::Ended]);
    }

    /**
     * Check if the auction is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === AuctionStatus::Active
            && $this->auction_start <= now()
            && $this->auction_end > now();
    }

    /**
     * Check if the auction has ended.
     */
    public function hasEnded(): bool
    {
        return $this->auction_end <= now();
    }

    /**
     * Get the winning bid for this product.
     */
    public function getWinningBid(): ?Bid
    {
        return $this->bids()
            ->orderBy('amount', 'desc')
            ->first();
    }

    /**
     * Export auction data to JSON format.
     * Includes product details, bids, and related information.
     */
    public function toExportArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'reserve_price' => (float) $this->reserve_price,
            'auction_start' => $this->auction_start->toIso8601String(),
            'auction_end' => $this->auction_end->toIso8601String(),
            'status' => $this->status->value,
            'winning_bid_id' => $this->winning_bid_id,
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'bids' => $this->bids->map(fn($bid) => [
                'id' => $bid->id,
                'user_id' => $bid->user_id,
                'amount' => (float) $bid->amount,
                'created_at' => $bid->created_at->toIso8601String(),
            ])->toArray(),
            'images' => $this->images->map(fn($image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'is_primary' => $image->is_primary,
                'display_order' => $image->display_order,
            ])->toArray(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
