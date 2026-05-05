<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkoDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rko_header_id',
        'medicine_id',
        'planned_quantity',
        'approved_quantity',
        'estimated_unit_price',
        'approved_unit_price',
        'total_estimate',
        'priority',
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
            'planned_quantity' => 'integer',
            'approved_quantity' => 'integer',
            'estimated_unit_price' => 'decimal:2',
            'approved_unit_price' => 'decimal:2',
            'total_estimate' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(RkoHeader::class, 'rko_header_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
