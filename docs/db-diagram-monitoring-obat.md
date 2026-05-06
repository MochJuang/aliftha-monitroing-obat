# DB Diagram Monitoring Obat Kontrasepsi

Dokumen ini berisi diagram basis data inti untuk aplikasi monitoring obat kontrasepsi. Diagram difokuskan pada tabel operasional utama aplikasi dan tidak memasukkan tabel bawaan Laravel seperti `cache`, `jobs`, `sessions`, dan `password_reset_tokens`.

## Entity Relationship Diagram

```mermaid
erDiagram
    ROLES {
        bigint id PK
        string name
        string description
        datetime created_at
        datetime updated_at
    }

    USERS {
        bigint id PK
        bigint role_id FK
        string name
        string username
        string email
        string phone
        string password
        boolean is_active
        datetime last_login_at
        datetime created_at
        datetime updated_at
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint user_id FK
        string module
        string action
        text description
        string ip_address
        datetime created_at
    }

    MEDICINE_CATEGORIES {
        bigint id PK
        string name
        string description
        datetime created_at
        datetime updated_at
    }

    UNITS {
        bigint id PK
        string name
        string symbol
        datetime created_at
        datetime updated_at
    }

    MEDICINES {
        bigint id PK
        bigint category_id FK
        bigint unit_id FK
        string code
        string name
        string medicine_type
        string brand
        string dosage
        int minimum_stock
        decimal standard_price
        text description
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    FUNDING_SOURCES {
        bigint id PK
        string code
        string name
        string source_type
        text notes
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    DISTRIBUTION_DESTINATIONS {
        bigint id PK
        string code
        string name
        string destination_type
        text address
        string phone
        string contact_person
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    RKO_HEADERS {
        bigint id PK
        string rko_number
        int period_month
        int period_year
        bigint funding_source_id FK
        decimal total_budget
        enum status
        date submitted_at
        date approved_at
        bigint submitted_by FK
        bigint approved_by FK
        text notes
        datetime created_at
        datetime updated_at
    }

    RKO_DETAILS {
        bigint id PK
        bigint rko_header_id FK
        bigint medicine_id FK
        int planned_quantity
        int approved_quantity
        decimal estimated_unit_price
        decimal approved_unit_price
        decimal total_estimate
        string priority
        text notes
        datetime created_at
        datetime updated_at
    }

    PROCUREMENT_REALIZATIONS {
        bigint id PK
        bigint rko_header_id FK
        bigint funding_source_id FK
        bigint medicine_id FK
        int period_month
        int period_year
        date realization_date
        int realized_quantity
        decimal unit_price
        decimal total_amount
        text notes
        datetime created_at
        datetime updated_at
    }

    STOCK_MUTATIONS {
        bigint id PK
        string mutation_number
        bigint medicine_id FK
        bigint rko_header_id FK
        bigint distribution_destination_id FK
        bigint created_by FK
        boolean is_auto_generated
        date mutation_date
        enum mutation_type
        int quantity
        string reference
        text notes
        datetime created_at
        datetime updated_at
    }

    STOCK_MUTATION_ITEMS {
        bigint id PK
        bigint stock_mutation_id FK
        bigint medicine_id FK
        int quantity
        text notes
        datetime created_at
        datetime updated_at
    }

    MEDICINE_STOCKS {
        bigint id PK
        bigint medicine_id FK
        string period
        int quantity
        date input_date
        string status_note
        datetime created_at
        datetime updated_at
    }

    ROLES ||--o{ USERS : memiliki
    USERS ||--o{ ACTIVITY_LOGS : mencatat
    USERS ||--o{ RKO_HEADERS : submitted_by
    USERS ||--o{ RKO_HEADERS : approved_by
    USERS ||--o{ STOCK_MUTATIONS : created_by

    MEDICINE_CATEGORIES ||--o{ MEDICINES : mengelompokkan
    UNITS ||--o{ MEDICINES : satuan

    FUNDING_SOURCES ||--o{ RKO_HEADERS : sumber_dana
    FUNDING_SOURCES ||--o{ PROCUREMENT_REALIZATIONS : sumber_dana

    MEDICINES ||--o{ RKO_DETAILS : direncanakan
    MEDICINES ||--o{ PROCUREMENT_REALIZATIONS : direalisasikan
    MEDICINES ||--o{ STOCK_MUTATIONS : mutasi_header
    MEDICINES ||--o{ STOCK_MUTATION_ITEMS : mutasi_detail
    MEDICINES ||--o{ MEDICINE_STOCKS : stok_periodik

    RKO_HEADERS ||--o{ RKO_DETAILS : memiliki
    RKO_HEADERS ||--o{ PROCUREMENT_REALIZATIONS : menghasilkan
    RKO_HEADERS ||--o{ STOCK_MUTATIONS : referensi

    DISTRIBUTION_DESTINATIONS ||--o{ STOCK_MUTATIONS : tujuan_opsional
    STOCK_MUTATIONS ||--o{ STOCK_MUTATION_ITEMS : memiliki
```

## Keterangan Singkat

- `rko_headers` berfungsi sebagai dokumen utama pengajuan kebutuhan obat per periode.
- `rko_details` menyimpan rincian item obat yang diajukan dan hasil persetujuannya.
- `procurement_realizations` menyimpan data realisasi pengadaan yang terbentuk dari RKO yang disetujui.
- `stock_mutations` menjadi header transaksi mutasi obat, baik mutasi masuk otomatis dari persetujuan RKO maupun mutasi keluar manual.
- `stock_mutation_items` menyimpan rincian item per transaksi mutasi.
- `medicine_stocks` menyimpan snapshot stok per periode untuk kebutuhan monitoring.

## Catatan Implementasi

- Pada schema saat ini, tabel `stock_mutations` masih memiliki `medicine_id` dan `quantity`, sementara rincian transaksi juga disimpan pada `stock_mutation_items`. Jadi, struktur mutasi yang berjalan masih bersifat hybrid antara header ringkas dan detail item.
- Jika diagram ini akan dimasukkan ke BAB IV, bagian ini paling cocok ditempatkan pada subbab analisa basis data atau perancangan basis data.
