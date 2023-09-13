<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\Verify_email;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Config;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new Verify_email());
    }

    public function listAddresses(): HasMany
    {
        return $this->hasMany(ListAddress::class);
    }
    public function review(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function order(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }
    public function tracking(): HasMany
    {
        return $this->hasMany(Tracking::class, 'user_id');
    }
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function wishlists(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Wishlist::class);
    }
}
