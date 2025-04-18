<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use SoftDeletes;

    public function created_by(): MorphTo
    {
        return $this->morphTo();
    }
    public function client():  BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function room():  BelongsTo
    {
        return $this->belongsTo(Room::class);
    }



}
