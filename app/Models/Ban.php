<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Ban extends Model
{
    protected $table = 'ban';
    use SoftDeletes;
    use HasFactory;


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
