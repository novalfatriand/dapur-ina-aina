# Dapur Ina Aina - PHP Murni + MySQL

Project web sederhana untuk sistem self-order / pengolahan transaksi restoran.

## Stack
- PHP 8+
- MySQL 8+
- MySQLi
- HTML + Tailwind CSS CDN
- Tanpa framework PHP
- Tanpa JavaScript framework

## Database
Import `dapur_ina_aina.sql` ke MySQL/phpMyAdmin.

Konfigurasi database ada di:
`config/database.php`

Default:
- host: localhost
- user: root
- password: kosong
- database: dapur_ina_aina

## Akun bawaan
Email: fatriandn@gmail.com
Password: 1234
Role: administrator

## Halaman
- Customer: `customer/menu.php`
- Customer cek status: `customer/status.php`
- Login: `login.php`
- Administrator: `admin/index.php`
- Kasir: `kasir/index.php`

## Alur
1. Customer membuka menu.
2. Customer menambahkan produk ke keranjang.
3. Customer mengisi nama dan nomor telepon.
4. Sistem membuat kode pesanan.
5. Pembayaran dilakukan langsung di kasir.
6. Kasir/admin mengubah status pembayaran menjadi PAID.
7. Kasir/admin mengubah status pesanan:
   SEDANG DIPROSES -> SIAP -> SELESAI.
8. Customer mengecek status dengan kode pesanan + nomor telepon.

## Catatan struktur SQL
SQL yang diberikan memiliki `detail_pesanan.uuid_kategori`, bukan `uuid_produk`.
Karena diminta mengikuti database tersebut, implementasi detail transaksi menyimpan kategori produk.
Untuk sistem produksi, relasi idealnya ditambah `uuid_produk` pada detail_pesanan agar setiap item pesanan dapat dilacak ke produk secara langsung.
