<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use app\Models\Receptionist;
use app\Models\Client;
use app\Models\Floor;
use app\Models\Ban;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    protected $table = 'admins';

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

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    public function receptionists(): MorphMany
    {
        return $this->morphMany(Receptionist::class, 'created_by');
    }

    public function clients(): MorphMany
    {
        return $this->morphMany(Client::class, 'created_by');
    }

    public function floors(): MorphMany
    {
        return $this->morphMany(Floor::class, 'created_by');
    }

    public function bans(): MorphMany
    {
        return $this->morphMany(Ban::class, 'banned_by');
    }

    public function rooms(): MorphMany
    {
        return $this->morphMany(Room::class, 'created_by');
    }

    public function reservations(): MorphMany
    {
        return $this->morphMany(Reservation::class, 'created_by');
    }
}
