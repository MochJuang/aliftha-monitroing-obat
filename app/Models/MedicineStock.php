<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'period',
        'quantity',
        'input_date',
        'status_note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'input_date' => 'date',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
