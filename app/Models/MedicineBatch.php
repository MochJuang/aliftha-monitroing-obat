<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'medicine_id',
        'receipt_item_id',
        'batch_number',
        'expired_at',
        'qty_received',
        'qty_remaining',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
            'qty_received' => 'integer',
            'qty_remaining' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function receiptItem(): BelongsTo
    {
        return $this->belongsTo(StockReceiptItem::class, 'receipt_item_id');
    }

    public function stockDistributionItems(): HasMany
    {
        return $this->hasMany(StockDistributionItem::class, 'batch_id');
    }

    public function stockAdjustmentItems(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class, 'batch_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('qty_remaining', '>', 0);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('expired_at', '<', now()->toDateString());
    }

    public function scopeAlmostExpired(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('expired_at', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query->orderBy('expired_at')->orderBy('id');
    }
}
