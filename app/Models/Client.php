<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use app\Models\Receptionist;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Notifications\ResetPassword;


class Client extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'client';

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nationalId',
        'country',
        'gender',
        'image',
        'created_by_id',
        'created_by_type',
        'email_verified_at',
        'verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function created_by(): MorphTo
    {
        return $this->morphTo();
    }
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    public function createdReservations(): MorphMany
    {
        return $this->morphMany(Reservation::class, 'created_by');
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
