<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'receipt_number',
        'source_id',
        'rko_header_id',
        'received_date',
        'received_by',
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
            'received_date' => 'date',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(StockSource::class, 'source_id');
    }

    public function rkoHeader(): BelongsTo
    {
        return $this->belongsTo(RkoHeader::class, 'rko_header_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class, 'receipt_id');
    }
}
