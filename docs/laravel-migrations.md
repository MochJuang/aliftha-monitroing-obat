# Blueprint Migration Laravel

Dokumen ini memetakan ERD ke urutan migration Laravel yang aman untuk foreign key dan mudah diimplementasikan.

## Saran Versi

- PHP 8.2+
- Laravel 11
- MySQL 8

## Urutan Pembuatan Migration

Urutan ini penting agar relasi foreign key tidak gagal saat `php artisan migrate`.

1. `create_roles_table`
2. `add_role_id_to_users_table` atau modifikasi migration `users`
3. `create_medicine_categories_table`
4. `create_units_table`
5. `create_medicines_table`
6. `create_stock_sources_table`
7. `create_distribution_destinations_table`
8. `create_stock_receipts_table`
9. `create_stock_receipt_items_table`
10. `create_medicine_batches_table`
11. `create_stock_distributions_table`
12. `create_stock_distribution_items_table`
13. `create_stock_adjustments_table`
14. `create_stock_adjustment_items_table`
15. `create_activity_logs_table`

## Perintah Artisan yang Disarankan

```bash
php artisan make:migration create_roles_table
php artisan make:migration add_role_id_to_users_table --table=users
php artisan make:migration create_medicine_categories_table
php artisan make:migration create_units_table
php artisan make:migration create_medicines_table
php artisan make:migration create_stock_sources_table
php artisan make:migration create_distribution_destinations_table
php artisan make:migration create_stock_receipts_table
php artisan make:migration create_stock_receipt_items_table
php artisan make:migration create_medicine_batches_table
php artisan make:migration create_stock_distributions_table
php artisan make:migration create_stock_distribution_items_table
php artisan make:migration create_stock_adjustments_table
php artisan make:migration create_stock_adjustment_items_table
php artisan make:migration create_activity_logs_table
```

## Struktur Tabel per Migration

### 1. `roles`

Kolom:

- `id`
- `name` unique
- `description` nullable
- `timestamps`

Contoh skema:

```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50)->unique();
    $table->string('description')->nullable();
    $table->timestamps();
});
```

### 2. `users`

Laravel sudah menyediakan tabel `users`. Tinggal ditambah:

- `role_id` foreign key ke `roles`
- `username` unique
- `phone` nullable
- `is_active` default `true`
- `last_login_at` nullable

Contoh:

```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
    $table->string('username', 50)->unique()->after('name');
    $table->string('phone', 20)->nullable()->after('email');
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
});
```

Catatan:

- Kalau ingin lebih sederhana, `role_id` bisa dibuat `nullable(false)` setelah seeder role awal sudah ada.

### 3. `medicine_categories`

Kolom:

- `id`
- `name` unique
- `description` nullable
- `timestamps`

### 4. `units`

Kolom:

- `id`
- `name` unique
- `symbol`
- `timestamps`

### 5. `medicines`

Kolom:

- `id`
- `category_id`
- `unit_id`
- `code` unique
- `name`
- `brand` nullable
- `dosage` nullable
- `minimum_stock` default `0`
- `description` nullable
- `is_active` default `true`
- `timestamps`

Contoh:

```php
Schema::create('medicines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('medicine_categories')->restrictOnDelete();
    $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
    $table->string('code', 50)->unique();
    $table->string('name', 150);
    $table->string('brand', 100)->nullable();
    $table->string('dosage', 100)->nullable();
    $table->unsignedInteger('minimum_stock')->default(0);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 6. `stock_sources`

Kolom:

- `id`
- `name`
- `source_type`
- `address` nullable
- `phone` nullable
- `contact_person` nullable
- `timestamps`

### 7. `distribution_destinations`

Kolom:

- `id`
- `code` unique
- `name`
- `destination_type`
- `address` nullable
- `phone` nullable
- `contact_person` nullable
- `is_active` default `true`
- `timestamps`

### 8. `stock_receipts`

Kolom:

- `id`
- `receipt_number` unique
- `source_id`
- `received_date`
- `received_by`
- `notes` nullable
- `status` default `posted`
- `timestamps`

Contoh:

```php
Schema::create('stock_receipts', function (Blueprint $table) {
    $table->id();
    $table->string('receipt_number', 50)->unique();
    $table->foreignId('source_id')->constrained('stock_sources')->restrictOnDelete();
    $table->date('received_date');
    $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
    $table->text('notes')->nullable();
    $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
    $table->timestamps();
});
```

### 9. `stock_receipt_items`

Kolom:

- `id`
- `receipt_id`
- `medicine_id`
- `batch_number`
- `expired_at`
- `quantity`
- `unit_cost` default `0`
- `notes` nullable
- `timestamps`

Catatan:

- Kombinasi `receipt_id`, `medicine_id`, `batch_number` boleh dibuat index.

### 10. `medicine_batches`

Kolom:

- `id`
- `medicine_id`
- `receipt_item_id`
- `batch_number`
- `expired_at`
- `qty_received`
- `qty_remaining`
- `timestamps`

Catatan:

- Tabel ini penting untuk monitoring batch dan FEFO.
- Kombinasi `medicine_id`, `batch_number`, `expired_at` sebaiknya di-index.

### 11. `stock_distributions`

Kolom:

- `id`
- `distribution_number` unique
- `destination_id`
- `distributed_date`
- `distributed_by`
- `notes` nullable
- `status` default `posted`
- `timestamps`

### 12. `stock_distribution_items`

Kolom:

- `id`
- `distribution_id`
- `batch_id`
- `medicine_id`
- `quantity`
- `notes` nullable
- `timestamps`

Catatan:

- Validasi aplikasi harus memastikan `quantity <= qty_remaining` pada batch.

### 13. `stock_adjustments`

Kolom:

- `id`
- `adjustment_number` unique
- `adjustment_date`
- `adjustment_type`
- `created_by`
- `notes` nullable
- `timestamps`

Nilai `adjustment_type` yang disarankan:

- `opname`
- `koreksi`
- `expired`
- `rusak`

### 14. `stock_adjustment_items`

Kolom:

- `id`
- `adjustment_id`
- `batch_id`
- `medicine_id`
- `system_qty`
- `actual_qty`
- `difference_qty`
- `reason` nullable
- `timestamps`

Catatan:

- `difference_qty` bisa dihitung di backend dari `actual_qty - system_qty`.

### 15. `activity_logs`

Kolom:

- `id`
- `user_id` nullable
- `module`
- `action`
- `description` nullable
- `ip_address` nullable
- `created_at`

Catatan:

- Tidak perlu `updated_at`.

## Index yang Disarankan

- `medicines.code`
- `stock_receipts.receipt_number`
- `stock_distributions.distribution_number`
- `stock_adjustments.adjustment_number`
- `medicine_batches (medicine_id, expired_at)`
- `stock_receipt_items (receipt_id, medicine_id)`
- `stock_distribution_items (distribution_id, medicine_id)`
- `activity_logs (user_id, created_at)`

## Seeder Awal yang Perlu Dibuat

- `RoleSeeder`
- `UnitSeeder`
- `MedicineCategorySeeder`
- `AdminUserSeeder`

Contoh data role:

- `admin`
- `petugas_gudang`
- `pimpinan`

## Urutan Fitur Saat Implementasi

Supaya pengerjaan Laravel lebih stabil, urutan implementasi yang disarankan:

1. Auth dan role user
2. Master data
3. Stok masuk
4. Batch stok
5. Stok keluar
6. Monitoring stok
7. Laporan
8. Penyesuaian stok
9. Activity log
