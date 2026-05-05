<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RkoHeader extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rko_number',
        'period_month',
        'period_year',
        'funding_source_id',
        'total_budget',
        'status',
        'submitted_at',
        'approved_at',
        'submitted_by',
        'approved_by',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'total_budget' => 'decimal:2',
            'submitted_at' => 'date',
            'approved_at' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RkoDetail::class);
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class, 'rko_header_id');
    }

    public function procurementRealizations(): HasMany
    {
        return $this->hasMany(ProcurementRealization::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
