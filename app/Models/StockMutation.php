<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'mutation_date',
        'mutation_type',
        'quantity',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'mutation_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
