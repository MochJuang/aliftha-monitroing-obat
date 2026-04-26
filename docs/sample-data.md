# Sample Data Aplikasi

Dokumen ini berisi contoh data awal yang konsisten dengan ERD untuk kebutuhan dummy data, presentasi, atau referensi pembuatan seeder Laravel.

## Catatan

- Semua data di bawah ini adalah data contoh.
- Format tanggal menggunakan `YYYY-MM-DD`.
- Data transaksi dibuat saling terhubung agar stok akhir bisa dicek dengan mudah.

## 1. Roles

| id | name | description |
|---|---|---|
| 1 | admin | Mengelola seluruh data dan pengguna |
| 2 | petugas_gudang | Mengelola stok masuk, stok keluar, dan monitoring |
| 3 | pimpinan | Melihat dashboard dan laporan |

## 2. Users

| id | role_id | name | username | email | phone | is_active |
|---|---|---|---|---|---|---|
| 1 | 1 | Super Admin | admin | admin@dppkb.go.id | 081200000001 | 1 |
| 2 | 2 | Nurhayati | petugas.gudang | gudang@dppkb.go.id | 081200000002 | 1 |
| 3 | 3 | Drs. Hendra Saputra | pimpinan | pimpinan@dppkb.go.id | 081200000003 | 1 |

## 3. Medicine Categories

| id | name | description |
|---|---|---|
| 1 | Pil KB | Obat kontrasepsi oral |
| 2 | Suntik KB | Obat kontrasepsi injeksi |
| 3 | Implant | Alat/obat kontrasepsi tanam |
| 4 | IUD | Alat kontrasepsi dalam rahim |

## 4. Units

| id | name | symbol |
|---|---|---|
| 1 | Strip | strip |
| 2 | Vial | vial |
| 3 | Set | set |

## 5. Medicines

| id | category_id | unit_id | code | name | brand | dosage | minimum_stock | is_active |
|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | PIL001 | Pil KB Andalan | Andalan | 28 tablet | 100 | 1 |
| 2 | 2 | 2 | SNT001 | Suntik KB 3 Bulan | Depo Medroxyprogesterone | 150 mg/ml | 80 | 1 |
| 3 | 3 | 3 | IMP001 | Implant 2 Batang | Indoplant | 2 batang | 100 | 1 |
| 4 | 4 | 3 | IUD001 | IUD Copper T 380A | Copper T | 380A | 25 | 1 |

## 6. Stock Sources

| id | name | source_type | address | phone | contact_person |
|---|---|---|---|---|---|
| 1 | BKKBN Provinsi Jawa Barat | bkkbn | Bandung | 022-7000001 | Rina |
| 2 | Dinas Kesehatan Kota Sukabumi | dinkes | Sukabumi | 0266-200001 | Dedi |
| 3 | PT Supplier Sehat Sentosa | supplier | Jakarta | 021-8000001 | Taufik |

## 7. Distribution Destinations

| id | code | name | destination_type | address | phone | contact_person | is_active |
|---|---|---|---|---|---|---|---|
| 1 | PKM001 | Puskesmas Cikole | puskesmas | Cikole, Sukabumi | 0266-210001 | Siti | 1 |
| 2 | PKM002 | Puskesmas Warudoyong | puskesmas | Warudoyong, Sukabumi | 0266-210002 | Lina | 1 |
| 3 | KLN001 | Klinik Keluarga Sehat | klinik | Sukaraja, Sukabumi | 0266-210003 | Rudi | 1 |
| 4 | BDN001 | Bidan Praktik Mandiri Siti | bidan | Citamiang, Sukabumi | 081300000001 | Siti Aminah | 1 |

## 8. Stock Receipts

| id | receipt_number | source_id | received_date | received_by | status | notes |
|---|---|---|---|---|---|---|
| 1 | RCV-2026-0001 | 1 | 2026-04-01 | 2 | posted | Penerimaan distribusi rutin dari BKKBN Provinsi |
| 2 | RCV-2026-0002 | 3 | 2026-04-10 | 2 | posted | Tambahan pengadaan lokal |

## 9. Stock Receipt Items

| id | receipt_id | medicine_id | batch_number | expired_at | quantity | unit_cost | notes |
|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | PIL240301 | 2027-03-31 | 500 | 0 | Distribusi pusat |
| 2 | 1 | 2 | SNT240215 | 2027-02-15 | 300 | 0 | Distribusi pusat |
| 3 | 1 | 3 | IMP240120 | 2028-01-20 | 100 | 0 | Distribusi pusat |
| 4 | 2 | 4 | IUD260401 | 2031-04-01 | 80 | 45000 | Pengadaan supplier |
| 5 | 2 | 1 | PIL260401 | 2028-04-01 | 200 | 5000 | Penambahan stok pil |

## 10. Medicine Batches

| id | medicine_id | receipt_item_id | batch_number | expired_at | qty_received | qty_remaining |
|---|---|---|---|---|---|---|
| 1 | 1 | 1 | PIL240301 | 2027-03-31 | 500 | 395 |
| 2 | 2 | 2 | SNT240215 | 2027-02-15 | 300 | 250 |
| 3 | 3 | 3 | IMP240120 | 2028-01-20 | 100 | 90 |
| 4 | 4 | 4 | IUD260401 | 2031-04-01 | 80 | 70 |
| 5 | 1 | 5 | PIL260401 | 2028-04-01 | 200 | 200 |

## 11. Stock Distributions

| id | distribution_number | destination_id | distributed_date | distributed_by | status | notes |
|---|---|---|---|---|---|---|
| 1 | DST-2026-0001 | 1 | 2026-04-12 | 2 | posted | Distribusi bulanan ke Puskesmas Cikole |
| 2 | DST-2026-0002 | 3 | 2026-04-18 | 2 | posted | Permintaan stok dari klinik |
| 3 | DST-2026-0003 | 4 | 2026-04-22 | 2 | posted | Distribusi ke bidan praktik mandiri |

## 12. Stock Distribution Items

| id | distribution_id | batch_id | medicine_id | quantity | notes |
|---|---|---|---|---|---|
| 1 | 1 | 1 | 1 | 60 | Pil KB untuk layanan rutin |
| 2 | 1 | 2 | 2 | 30 | Suntik KB 3 bulan |
| 3 | 1 | 4 | 4 | 10 | IUD untuk program pelayanan |
| 4 | 2 | 1 | 1 | 40 | Distribusi tambahan pil KB |
| 5 | 2 | 3 | 3 | 10 | Implant 2 batang |
| 6 | 3 | 2 | 2 | 20 | Suntik KB untuk BPM |

## 13. Stock Adjustments

| id | adjustment_number | adjustment_date | adjustment_type | created_by | notes |
|---|---|---|---|---|---|
| 1 | ADJ-2026-0001 | 2026-04-25 | rusak | 2 | Koreksi stok pil KB akibat kemasan rusak |

## 14. Stock Adjustment Items

| id | adjustment_id | batch_id | medicine_id | system_qty | actual_qty | difference_qty | reason |
|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | 1 | 400 | 395 | -5 | 5 strip rusak karena kemasan lembap |

## 15. Activity Logs

| id | user_id | module | action | description | ip_address | created_at |
|---|---|---|---|---|---|---|
| 1 | 1 | auth | login | Admin login ke sistem | 127.0.0.1 | 2026-04-26 08:00:00 |
| 2 | 2 | stock_receipts | create | Membuat transaksi RCV-2026-0001 | 127.0.0.1 | 2026-04-01 09:15:00 |
| 3 | 2 | stock_receipts | create | Membuat transaksi RCV-2026-0002 | 127.0.0.1 | 2026-04-10 10:20:00 |
| 4 | 2 | stock_distributions | create | Membuat transaksi DST-2026-0001 | 127.0.0.1 | 2026-04-12 11:00:00 |
| 5 | 2 | stock_distributions | create | Membuat transaksi DST-2026-0002 | 127.0.0.1 | 2026-04-18 13:40:00 |
| 6 | 2 | stock_distributions | create | Membuat transaksi DST-2026-0003 | 127.0.0.1 | 2026-04-22 09:05:00 |
| 7 | 2 | stock_adjustments | create | Membuat penyesuaian ADJ-2026-0001 | 127.0.0.1 | 2026-04-25 15:10:00 |

## Ringkasan Pergerakan Stok

### Obat 1: Pil KB Andalan

- Stok masuk batch `PIL240301`: `500`
- Stok masuk batch `PIL260401`: `200`
- Stok keluar dari batch `PIL240301`: `60 + 40 = 100`
- Penyesuaian rusak batch `PIL240301`: `-5`
- Stok akhir:
  - Batch `PIL240301`: `500 - 100 - 5 = 395`
  - Batch `PIL260401`: `200`
  - Total obat: `595`

### Obat 2: Suntik KB 3 Bulan

- Stok masuk: `300`
- Stok keluar: `30 + 20 = 50`
- Stok akhir: `250`

### Obat 3: Implant 2 Batang

- Stok masuk: `100`
- Stok keluar: `10`
- Stok akhir: `90`

### Obat 4: IUD Copper T 380A

- Stok masuk: `80`
- Stok keluar: `10`
- Stok akhir: `70`

## Ringkasan Stok Akhir

Per tanggal `2026-04-26`

| medicine_id | code | name | total_stock | minimum_stock | status |
|---|---|---|---|---|---|
| 1 | PIL001 | Pil KB Andalan | 595 | 100 | aman |
| 2 | SNT001 | Suntik KB 3 Bulan | 250 | 80 | aman |
| 3 | IMP001 | Implant 2 Batang | 90 | 100 | di bawah minimum |
| 4 | IUD001 | IUD Copper T 380A | 70 | 25 | aman |

## Contoh Data untuk Dashboard

- Total jenis obat: `4`
- Total stok keseluruhan: `1005`
- Total transaksi stok masuk: `2`
- Total transaksi stok keluar: `3`
- Total penyesuaian stok: `1`
- Obat di bawah minimum: `1`
- Batch mendekati expired dalam 12 bulan:
  - `SNT240215` expired `2027-02-15`
  - `PIL240301` expired `2027-03-31`

## Catatan Implementasi Seeder

Jika nanti ingin diubah menjadi seeder Laravel, urutan insert yang aman:

1. `roles`
2. `users`
3. `medicine_categories`
4. `units`
5. `medicines`
6. `stock_sources`
7. `distribution_destinations`
8. `stock_receipts`
9. `stock_receipt_items`
10. `medicine_batches`
11. `stock_distributions`
12. `stock_distribution_items`
13. `stock_adjustments`
14. `stock_adjustment_items`
15. `activity_logs`
