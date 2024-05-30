<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Election extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date', 'ussd_code'];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('start_date', '<=', Carbon::now()->format ('Y-m-d'))
            ->where('end_date', '>=', Carbon::now()->format ('Y-m-d'));
    }
}
