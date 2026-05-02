<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockDistribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'distribution_number',
        'destination_id',
        'distributed_date',
        'distributed_by',
        'notes',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'distributed_date' => 'date',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(DistributionDestination::class, 'destination_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockDistributionItem::class, 'distribution_id');
    }
}
