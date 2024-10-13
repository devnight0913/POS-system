<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory, HasUuid;
    
    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array<int, string>
    //  */
    protected $fillable = [
        'name',
        'price',
        'date'
    ];

    /**
     * Scope a query to search Suppliers
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search)  return $query;
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    // use HasApiTokens, HasFactory, Notifiable, HasUuid, HasProfilePhoto;

    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'username',
    //     'phone',
    //     'password',
    //     'role',
    // ];

    // /**
    //  * The attributes that should be hidden for serialization.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    // /**
    //  * The attributes that should be cast.
    //  *
    //  * @var array<string, string>
    //  */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    // /**
    //  * Scope a query to search posts
    //  */
    // public function scopeSearch(Builder $query, ?string $search): Builder
    // {
    //     return $query->where('name', 'LIKE', "%{$search}%");
    // }

    // public function getIsSuperAdminAttribute(): bool
    // {
    //     return $this->role == Role::SUPER_ADMIN;
    // }
    // public function getIsAdminAttribute(): bool
    // {
    //     return $this->role == Role::ADMIN;
    // }
    // public function getIsCashierAttribute(): bool
    // {
    //     return $this->role == Role::CASHIER;
    // }

    // public function getCanCreateAttribute(): bool
    // {
    //     return $this->role == Role::ADMIN || $this->role == Role::SUPER_ADMIN;
    // }
    // public function getCanEditAttribute(): bool
    // {
    //     return $this->role == Role::ADMIN || $this->role == Role::SUPER_ADMIN;
    // }

    // public function getCanDeleteAttribute(): bool
    // {
    //     return $this->role == Role::ADMIN || $this->role == Role::SUPER_ADMIN;
    // }
}
