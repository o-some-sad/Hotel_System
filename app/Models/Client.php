<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use app\Models\Receptionist;
use app\Models\Client;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    public function created_by(): MorphTo
    {
        return $this->morphTo();
    }
    public function reservation(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
