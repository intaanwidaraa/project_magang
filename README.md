# Sistem Inventaris Gudang Bengkel

Aplikasi web manajemen inventaris sederhana yang dibangun menggunakan Laravel 12 dan Filament v3.3. Didesain khusus untuk kebutuhan internal bengkel atau dealer dalam mengelola alur masuk dan keluar suku cadang.


*(Saran: Ganti bagian ini dengan screenshot dashboard aplikasi Anda yang sudah jadi)*

---

## Fitur Utama

-   **Manajemen Data Master:** CRUD untuk Produk (Barang) dan Pemasok (Supplier).
-   **Alur Stok Lengkap:** Proses Barang Masuk (Purchase Order) untuk menambah stok dan Barang Keluar (Stock Requisition) untuk penggunaan internal.
-   **Pelacakan Stok:** Log riwayat pergerakan stok untuk setiap item, memberikan jejak audit yang jelas.
-   **SKU Otomatis:** Pembuatan SKU (Stock Keeping Unit) unik secara otomatis saat produk baru ditambahkan.
-   **Notifikasi Stok Minimum:** Peringatan otomatis di dalam aplikasi saat stok produk mencapai batas minimum.
-   **Sistem User & Hak Akses:** Dua peran user (Administrator & Staf Gudang) dengan hak akses yang berbeda.
-   **Dashboard Informatif:** Ringkasan visual kondisi inventaris, termasuk item stok kritis dan barang yang paling sering keluar.
-   **Laporan Terpusat:** Halaman laporan dinamis dengan kemampuan filter (per supplier, per tanggal) dan ekspor data ke Excel.

---

## Teknologi yang Digunakan

-   **Backend:** Laravel 12
-   **Admin Panel:** Filament v3.3
-   **Database:** MySQL
-   **Paket Utama:**
    -   `spatie/laravel-permission` untuk manajemen peran & hak akses.
    -   `maatwebsite/excel` untuk fungsionalitas ekspor ke Excel.

---

## Instalasi

Berikut adalah langkah-langkah untuk menjalankan proyek ini di lingkungan lokal Anda.

1.  **Clone Repositori**
    ```bash
    git clone [https://github.com/](https://github.com/)[NAMA_USER_ANDA]/[NAMA_REPO_ANDA].git
    cd [NAMA_REPO_ANDA]
    ```

2.  **Install Dependensi PHP**
    Jalankan Composer untuk menginstal semua paket yang dibutuhkan.
    ```bash
    composer install
    ```

3.  **Siapkan File Environment**
    Salin file `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```

4.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5.  **Konfigurasi Database**
    Buka file `.env` dan sesuaikan pengaturan database Anda:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=bengkel_inventaris
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    Pastikan Anda sudah membuat database `bengkel_inventaris` di MySQL Anda.

6.  **Jalankan Migrasi & Seeder**
    Perintah ini akan membuat semua tabel database dan mengisi data awal (termasuk akun user).
    ```bash
    php artisan migrate --seed
    ```

7.  **Jalankan Development Server**
    ```bash
    php artisan serve
    ```
    Aplikasi sekarang berjalan di `http://127.0.0.1:8000`.

---

## Akun Default

Setelah proses instalasi selesai, Anda bisa login ke panel admin di `http://127.0.0.1:8000/admin` menggunakan akun berikut:

**1. Administrator**
-   **Email:** `admin@bengkel.com`
-   **Password:** `password`

**2. Staf Gudang**
-   **Email:** `staff@bengkel.com`
-   **Password:** `password`
