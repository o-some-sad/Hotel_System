<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Ban extends Model
{
    use SoftDeletes;

    public function banned_by(): MorphTo
    {
        return $this->morphTo();
    }
    public function banned(): BelongsTo
    {
    return $this->belongsTo(Receptionist::class);
    }
    //ban->banned
    //ban->banned_by
}
