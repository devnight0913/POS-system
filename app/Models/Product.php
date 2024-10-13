<?php

namespace App\Models;

use App\Traits\HasImage;
use App\Traits\HasStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuid, HasImage, HasStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image_path',
        // 'sale_price',
        'wholesale_price',
        'retailsale_price',
        'cost',
        // 'barcode',
        'wholesale_barcode',
        'retail_barcode',
        // 'sku',
        'wholesale_sku',
        'retail_sku',
        'quantity',
        'description',
        'sort_order',
        'is_active',
        'category_id',
        'in_stock',
        'track_stock',
        'continue_selling_when_out_of_stock',
    ];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'image_url',
        'full_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'track_stock' => 'boolean',
        'continue_selling_when_out_of_stock' => 'boolean',
    ];

    /**
     * Scope a query to search posts
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;
        return $query->where('name', 'LIKE', "%{$search}%")
            ->orWhere('retail_barcode', 'LIKE', "%{$search}%")
            ->orWhere('retail_sku', 'LIKE', "%{$search}%")
            ->orWhereHas('category', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
    }


    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Get the cost value.
     *
     * @return float
     */
    public function getCostValueAttribute(): float
    {

        $hasExchangeRate = config('settings')->enableExchangeRateForItems;
        if ($hasExchangeRate) {
            $exchangeRate = config('settings')->exchangeRate;
            return $this->cost * $exchangeRate;
        }
        return $this->cost;
    }

    
    public function getTableSalesViewPriceAttribute(): string
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;

        if ($hasExchangeRate) {
            $price = currency_format($this->price);
            // return currency_format($this->sale_price * $this->in_stock, $hasExchangeRate) . " ({$price})";
            return currency_format($this->retailsale_price * $this->in_stock, $hasExchangeRate) . " ({$price})";
        }
        // return currency_format($this->sale_price * $this->in_stock, $hasExchangeRate);
        return currency_format($this->retailsale_price * $this->in_stock, $hasExchangeRate);
    }
    
    public function getTableWholesaleViewPriceAttribute(): string
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;

        if ($hasExchangeRate) {
            $price = currency_format($this->wholesale_price);
            return currency_format($this->wholesale_price * $this->in_stock, $hasExchangeRate) . " ({$price})";
        }
        return currency_format($this->wholesale_price * $this->in_stock, $hasExchangeRate);
    }
    
    public function getWholeCostAttribute(): string
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;

        if ($hasExchangeRate) {
            $price = currency_format($this->cost);
            return currency_format($this->cost * $this->in_stock, $hasExchangeRate) . " ({$price})";
        }
        return currency_format($this->cost * $this->in_stock, $hasExchangeRate);
    }
    
    /**
     * Get the price.
     *
     * @return float
     */
    public function getPriceAttribute(): float
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;
        if ($hasExchangeRate) {
            $exchangeRate = config('settings')->exchangeRate;
            return $this->retailsale_price * $exchangeRate;
            // return $this->sale_price * $exchangeRate;
        }
        // return $this->sale_price;
        return $this->retailsale_price;
    }

    /**
     * Get the view price.
     *
     * @return string
     */
    public function getViewPriceAttribute(): string
    {
        return currency_format($this->price);
    }
    /**
     * Get the view price.
     *
     * @return string
     */
    public function getTableViewPriceAttribute(): string
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;

        if ($hasExchangeRate) {
            $price = currency_format($this->price);
            return currency_format($this->retailsale_price, $hasExchangeRate) . " ({$price})";
            // return currency_format($this->sale_price, $hasExchangeRate) . " ({$price})";
        }
        return currency_format($this->retailsale_price, $hasExchangeRate);
        // return currency_format($this->sale_price, $hasExchangeRate);
    }
    
    public function getTableViewCostAttribute(): string
    {
        $hasExchangeRate = config('settings')->enableExchangeRateForItems;
        return currency_format($this->cost, $hasExchangeRate);
    }

    /**
     * Get the view cost.
     *
     * @return string
     */
    public function getViewCostAttribute(): string
    {
        return currency_format($this->cost);
    }

    /**
     * Get the full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }
}
