# Blueprint Model Eloquent

Dokumen ini memetakan tabel database ke model Laravel beserta relasi utamanya.

## Daftar Model

- `Role`
- `User`
- `MedicineCategory`
- `Unit`
- `Medicine`
- `StockSource`
- `DistributionDestination`
- `StockReceipt`
- `StockReceiptItem`
- `MedicineBatch`
- `StockDistribution`
- `StockDistributionItem`
- `StockAdjustment`
- `StockAdjustmentItem`
- `ActivityLog`

## Struktur Folder yang Disarankan

```text
app/Models
app/Http/Controllers
app/Http/Requests
app/Services
app/Policies
```

## 1. Role

Relasi:

- `hasMany(User::class)`

Contoh:

```php
class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

## 2. User

Relasi:

- `belongsTo(Role::class)`
- `hasMany(StockReceipt::class, 'received_by')`
- `hasMany(StockDistribution::class, 'distributed_by')`
- `hasMany(StockAdjustment::class, 'created_by')`
- `hasMany(ActivityLog::class)`

Catatan:

- Model `User` bisa memakai helper seperti `isAdmin()` atau `hasRole($name)`.

## 3. MedicineCategory

Relasi:

- `hasMany(Medicine::class, 'category_id')`

## 4. Unit

Relasi:

- `hasMany(Medicine::class)`

## 5. Medicine

Relasi:

- `belongsTo(MedicineCategory::class, 'category_id')`
- `belongsTo(Unit::class)`
- `hasMany(StockReceiptItem::class)`
- `hasMany(MedicineBatch::class)`
- `hasMany(StockDistributionItem::class)`
- `hasMany(StockAdjustmentItem::class)`

Accessor/logic yang berguna:

- total stok saat ini
- status stok minimum
- batch aktif
- batch mendekati kedaluwarsa

Contoh:

```php
class Medicine extends Model
{
    protected $fillable = [
        'category_id',
        'unit_id',
        'code',
        'name',
        'brand',
        'dosage',
        'minimum_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }
}
```

## 6. StockSource

Relasi:

- `hasMany(StockReceipt::class, 'source_id')`

## 7. DistributionDestination

Relasi:

- `hasMany(StockDistribution::class, 'destination_id')`

## 8. StockReceipt

Relasi:

- `belongsTo(StockSource::class, 'source_id')`
- `belongsTo(User::class, 'received_by')`
- `hasMany(StockReceiptItem::class, 'receipt_id')`

Logic penting:

- saat transaksi `posted`, sistem membuat atau menambah data batch
- saat `cancelled`, stok dari receipt harus dibatalkan dengan aturan yang aman

## 9. StockReceiptItem

Relasi:

- `belongsTo(StockReceipt::class, 'receipt_id')`
- `belongsTo(Medicine::class)`
- `hasOne(MedicineBatch::class, 'receipt_item_id')`

## 10. MedicineBatch

Relasi:

- `belongsTo(Medicine::class)`
- `belongsTo(StockReceiptItem::class, 'receipt_item_id')`
- `hasMany(StockDistributionItem::class, 'batch_id')`
- `hasMany(StockAdjustmentItem::class, 'batch_id')`

Scope yang disarankan:

- `scopeAvailable()`
- `scopeExpired()`
- `scopeAlmostExpired($days = 30)`
- `scopeFefo()`

Contoh:

```php
public function scopeAvailable(Builder $query): Builder
{
    return $query->where('qty_remaining', '>', 0);
}

public function scopeFefo(Builder $query): Builder
{
    return $query->orderBy('expired_at')->orderBy('id');
}
```

## 11. StockDistribution

Relasi:

- `belongsTo(DistributionDestination::class, 'destination_id')`
- `belongsTo(User::class, 'distributed_by')`
- `hasMany(StockDistributionItem::class, 'distribution_id')`

Logic penting:

- item distribusi mengurangi `qty_remaining` pada batch terkait
- validasi harus menolak distribusi jika stok batch tidak cukup

## 12. StockDistributionItem

Relasi:

- `belongsTo(StockDistribution::class, 'distribution_id')`
- `belongsTo(MedicineBatch::class, 'batch_id')`
- `belongsTo(Medicine::class)`

## 13. StockAdjustment

Relasi:

- `belongsTo(User::class, 'created_by')`
- `hasMany(StockAdjustmentItem::class, 'adjustment_id')`

Logic penting:

- penyesuaian dapat menambah atau mengurangi `qty_remaining`
- seluruh perubahan stok sebaiknya dibungkus database transaction

## 14. StockAdjustmentItem

Relasi:

- `belongsTo(StockAdjustment::class, 'adjustment_id')`
- `belongsTo(MedicineBatch::class, 'batch_id')`
- `belongsTo(Medicine::class)`

## 15. ActivityLog

Relasi:

- `belongsTo(User::class)`

Kegunaan:

- log create, update, delete, login, logout, posting transaksi

## Service Class yang Sebaiknya Dibuat

Supaya controller tidak terlalu penuh, logika stok lebih aman dipindah ke service:

- `StockReceiptService`
- `StockDistributionService`
- `StockAdjustmentService`
- `StockMonitoringService`
- `ActivityLogService`

Contoh tanggung jawab:

- `StockReceiptService`: simpan receipt, simpan item, bentuk batch
- `StockDistributionService`: pilih batch FEFO, validasi stok, kurangi batch
- `StockAdjustmentService`: hitung selisih dan update batch
- `StockMonitoringService`: ringkasan stok dashboard dan alert

## Form Request yang Disarankan

- `StoreMedicineRequest`
- `UpdateMedicineRequest`
- `StoreStockReceiptRequest`
- `StoreStockDistributionRequest`
- `StoreStockAdjustmentRequest`
- `StoreUserRequest`

## Cast yang Disarankan

- tanggal: `received_date`, `distributed_date`, `adjustment_date`, `expired_at`
- boolean: `is_active`
- decimal: `unit_cost`

## Trait / Helper Tambahan

Kalau nanti project mulai besar, bisa ditambah:

- trait `HasCodeGenerator`
- trait `LogsActivity`
- trait `HasStatusLabel`

## Catatan Implementasi

- Pakai `DB::transaction()` untuk stok masuk, stok keluar, dan penyesuaian stok.
- Jangan hitung stok hanya dari tabel transaksi saat runtime bila performa mulai berat; gunakan `medicine_batches.qty_remaining` sebagai sumber stok berjalan.
- Untuk audit yang lebih rapi, setiap transaksi penting bisa menyimpan siapa pembuat dan siapa yang melakukan posting.
