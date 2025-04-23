<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ban extends Model
{
    protected $table = 'bans';
    use SoftDeletes;
    use HasFactory;
    
    protected $fillable = [
        'banned_id', 
        'banned_type',
        'banned_by_id',
        'banned_by_type',
        'reason',
        'expires_at',
        'is_permanent'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        'is_permanent' => 'boolean'
    ];

    public function banned_by(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function banned(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function isActive()
    {
        if ($this->trashed()) {
            return false;
        }
        
        if ($this->is_permanent) {
            return true;
        }
        
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}