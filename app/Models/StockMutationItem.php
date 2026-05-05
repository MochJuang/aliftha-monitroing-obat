<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "stock_mutation_id",
        "medicine_id",  
        "quantity",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "quantity" => "integer",
        ];
    }

    public function mutation(): BelongsTo
    {
        return $this->belongsTo(StockMutation::class, "stock_mutation_id");
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
