<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use app\Models\Receptionist;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    use SoftDeletes;
    use HasFactory;

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
