<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Reservation extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'room_id',
        'check_in',
        'check_out',
        'accompanying_number',
        'price',
        'notes',
        'is_approved',
        'created_by_id',
        'created_by_type',
    ];


    public function created_by(): MorphTo
    {
        return $this->morphTo();
    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
