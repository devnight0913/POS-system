<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrderDetail extends Model
{
    use HasFactory, HasUuid;


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'view_total',
        'view_price',
        'view_wholesale_price',
        'view_retailsale_price',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }


    public function first_product()
    {
        return $this->belongsTo(Product::class, 'product_a')->withTrashed();
    }


    /**
     * Scope a query to search posts
     */
    public function scopeOfAuthor(Builder $query, ?string $authorId): Builder
    {
        if (!$authorId)  return $query;
        return $query->whereHas('order', function ($q) use ($authorId) {
            return $q->where('user_id', $authorId);
        });
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
     * Get the view wholesale price.
     *
     * @return string
     */
    public function getViewWholesalePriceAttribute(): string
    {
        if($this->price_type === 0) return "-";
        return currency_format($this->wholesale_price);
    }


    /**
     * Get the view retailsale price.
     *
     * @return string
     */
    public function getViewRetailsalePriceAttribute(): string
    {
        if($this->price_type === 1) return "-";
        return currency_format($this->retailsale_price);
    }

    /**
     * Get the view total.
     *
     * @return string
     */
    public function getViewTotalAttribute(): string
    {
        return currency_format($this->total);
    }
}
