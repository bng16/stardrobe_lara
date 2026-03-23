<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreatorShop extends Model
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
        'user_id',
        'shop_name',
        'bio',
        'profile_image',
        'banner_image',
        'is_onboarded',
    ];

    /**
     * Get the creator (user) that owns the shop.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the private information for this creator shop.
     */
    public function privateInfo(): HasOne
    {
        return $this->hasOne(CreatorPrivateInfo::class, 'creator_shop_id');
    }

    /**
     * Get the products listed by this creator shop.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'creator_id', 'user_id');
    }

    /**
     * Get the followers of this creator shop.
     * Since follows table uses user_id (not creator_shop_id), we specify user_id as the parent key.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'creator_id',
            'follower_id',
            'user_id',  // Parent key on creator_shops table
            'id'        // Related key on users table
        )->withTimestamps();
    }

    /**
     * Get the total number of followers for this creator shop.
     */
    public function getFollowerCount(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get the count of active products for this creator shop.
     */
    public function getActiveProductCount(): int
    {
        return $this->products()
            ->where('status', 'active')
            ->count();
    }
}
