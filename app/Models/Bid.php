<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory, HasUuids;

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
        'product_id',
        'user_id',
        'amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the product that this bid belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who placed this bid.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include bids for a specific user.
     * CRITICAL: This scope is used to filter bids by ownership.
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to conditionally include bid amounts based on authorization.
     * CRITICAL: This scope enforces bid privacy by only including amounts
     * for the bid owner or admin users. For unauthorized users, amounts are excluded.
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    public function scopeWithAmountIfAuthorized(Builder $query, User $user): void
    {
        // Only include amount if user is admin or the bid owner
        // For other users, the amount column should not be selected
        if ($user->role !== UserRole::Admin) {
            // If not admin, we need to check ownership per-bid
            // This scope adds a conditional select that only includes amount
            // when the user_id matches the authenticated user
            $query->selectRaw('
                id,
                product_id,
                user_id,
                CASE 
                    WHEN user_id = ? THEN amount 
                    ELSE NULL 
                END as amount,
                created_at,
                updated_at
            ', [$user->id]);
        }
        // If admin, no modification needed - they see all amounts
    }

    /**
     * Get the rank of this bid relative to other bids on the same product.
     * Rank 1 is the highest bid, rank 2 is second highest, etc.
     *
     * @return int
     */
    public function getRank(): int
    {
        return Bid::where('product_id', $this->product_id)
            ->where('amount', '>', $this->amount)
            ->count() + 1;
    }

    /**
     * Check if this bid is currently the winning bid for its product.
     *
     * @return bool
     */
    public function isWinning(): bool
    {
        $highestBid = Bid::where('product_id', $this->product_id)
            ->orderBy('amount', 'desc')
            ->first();

        return $highestBid && $highestBid->id === $this->id;
    }
}

