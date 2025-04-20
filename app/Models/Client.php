<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use app\Models\Receptionist;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nationalId',
        'country',
        'gender',
        'image'
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
}
