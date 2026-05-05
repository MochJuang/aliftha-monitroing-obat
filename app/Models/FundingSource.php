<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundingSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'source_type',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rkoHeaders(): HasMany
    {
        return $this->hasMany(RkoHeader::class);
    }

    public function procurementRealizations(): HasMany
    {
        return $this->hasMany(ProcurementRealization::class);
    }
}
