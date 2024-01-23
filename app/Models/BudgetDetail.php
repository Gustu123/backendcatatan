<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BudgetDetail extends Model
{
    use HasFactory;
    /**
     * Get the post that owns the comment.
     */

    protected $fillable = [
        'name',
    ];
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'purposable');
    }
}
