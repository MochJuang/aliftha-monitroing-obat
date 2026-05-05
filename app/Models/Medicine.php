<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'unit_id',
        'code',
        'name',
        'medicine_type',
        'brand',
        'dosage',
        'minimum_stock',
        'standard_price',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_stock' => 'integer',
            'standard_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function rkoDetails(): HasMany
    {
        return $this->hasMany(RkoDetail::class);
    }

    public function procurementRealizations(): HasMany
    {
        return $this->hasMany(ProcurementRealization::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MedicineStock::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}
