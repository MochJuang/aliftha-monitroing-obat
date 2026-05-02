# BAB V
# PENUTUP

## 5.1 Kesimpulan

Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian sistem yang telah dilakukan, maka dapat diambil beberapa kesimpulan sebagai berikut:

- penelitian ini berhasil merancang dan membangun aplikasi monitoring obat kontrasepsi berbasis web pada Dinas Pengendalian Penduduk dan Keluarga Berencana Kota Sukabumi menggunakan framework Laravel dan basis data MySQL/MariaDB,
- aplikasi yang dibangun mampu mengintegrasikan proses pengelolaan master data, pencatatan stok masuk, pencatatan stok keluar, penyesuaian stok, monitoring stok, monitoring batch dan kedaluwarsa, pembuatan laporan, manajemen pengguna, dan log aktivitas ke dalam satu sistem yang terpusat,
- sistem yang dibangun mampu membantu mengurangi ketergantungan pada proses pencatatan manual yang sebelumnya berpotensi menimbulkan kesalahan pencatatan, keterlambatan pelaporan, serta kesulitan dalam pemantauan stok secara berkala,
- fitur monitoring yang tersedia pada aplikasi mampu menampilkan informasi stok obat secara lebih cepat dan terstruktur, termasuk informasi stok minimum, batch obat, serta obat yang mendekati masa kedaluwarsa,
- penerapan pengelolaan stok berbasis batch dan metode FEFO (*First Expired First Out*) mampu mendukung proses distribusi obat secara lebih tepat, sehingga stok dengan masa kedaluwarsa terdekat dapat diprioritaskan terlebih dahulu,
- berdasarkan hasil pengujian *black box testing*, fungsi-fungsi utama pada aplikasi telah berjalan sesuai dengan kebutuhan, baik pada proses login, pengelolaan master data, transaksi stok masuk, transaksi stok keluar, penyesuaian stok, monitoring, laporan, manajemen pengguna, maupun log aktivitas,
- secara keseluruhan, aplikasi monitoring obat kontrasepsi berbasis web yang dibangun dapat membantu meningkatkan efektivitas dan efisiensi pengelolaan stok obat kontrasepsi serta mendukung proses monitoring dan pengambilan keputusan pada instansi terkait.

## 5.2 Saran

Berdasarkan hasil penelitian dan implementasi sistem yang telah dilakukan, maka saran yang dapat diberikan untuk pengembangan lebih lanjut adalah sebagai berikut:

- menambahkan fitur notifikasi otomatis untuk stok minimum, stok habis, dan batch yang mendekati masa kedaluwarsa agar proses monitoring dapat dilakukan secara lebih proaktif,
- menambahkan fitur ekspor laporan ke dalam format PDF atau Excel untuk mempermudah proses dokumentasi dan pelaporan resmi,
- mengembangkan dashboard yang lebih interaktif dengan penambahan grafik statistik stok masuk, stok keluar, distribusi obat, dan tren penggunaan obat kontrasepsi,
- menambahkan fitur cetak dokumen transaksi, seperti bukti stok masuk, bukti stok keluar, dan hasil penyesuaian stok,
- mengembangkan integrasi sistem dengan unit atau fasilitas pelayanan kesehatan lain agar proses pelaporan distribusi obat dapat dilakukan secara lebih luas dan terhubung,
- meningkatkan keamanan sistem melalui penguatan pengelolaan hak akses, pencatatan log aktivitas yang lebih detail, serta mekanisme pencadangan data secara berkala,
- melakukan evaluasi dan pemeliharaan sistem secara berkelanjutan agar aplikasi tetap sesuai dengan kebutuhan operasional instansi dan perkembangan teknologi.
