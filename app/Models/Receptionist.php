<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receptionist extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'receptionists';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nationalId',
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

    public function client(): MorphMany
    {
        return $this->morphMany(Client::class, 'created_by');
    }

    public function reservations(): MorphMany
    {
        return $this->morphMany(Reservation::class, 'created_by');
    }
}
