<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRealization extends Model
{
    use HasFactory;

    protected $fillable = [
        'rko_header_id',
        'funding_source_id',
        'medicine_id',
        'period_month',
        'period_year',
        'realization_date',
        'realized_quantity',
        'unit_price',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'realization_date' => 'date',
            'realized_quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function rkoHeader(): BelongsTo
    {
        return $this->belongsTo(RkoHeader::class);
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
