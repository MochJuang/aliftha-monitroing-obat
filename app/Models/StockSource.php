<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockSource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'source_type',
        'address',
        'phone',
        'contact_person',
    ];

    public function stockReceipts(): HasMany
    {
        return $this->hasMany(StockReceipt::class, 'source_id');
    }
}
