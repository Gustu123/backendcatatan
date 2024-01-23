<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    private $dirName = 'trancations';
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'amount',
        'deskripsi',
        'receipt',
        'cash_out',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
    public function debts(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }


    public function purposable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function getDirname()
    {
        return $this->dirName;
    }
}
