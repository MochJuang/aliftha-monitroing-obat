<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'mutation_number',
        'medicine_id',
        'rko_header_id',
        'distribution_destination_id',
        'created_by',
        'is_auto_generated',
        'mutation_date',
        'mutation_type',
        'quantity',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_auto_generated' => 'boolean',
            'mutation_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function rkoHeader(): BelongsTo
    {
        return $this->belongsTo(RkoHeader::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(DistributionDestination::class, 'distribution_destination_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMutationItem::class);
    }
}
