<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voter extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'election_id'];

    public function votes(): HasMany
    {
        return $this->hasMany (Vote::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo (Election::class);
    }
}
