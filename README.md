<img width="981" height="472" alt="Image" src="https://github.com/user-attachments/assets/3480b5bb-8bda-41db-a5fb-33610b7f6214" />
# Form Biodata Mahasiswa (PHP)

Tugas Individu – Pertemuan 02: Bangun form biodata yang fungsional, terbaca, dan mudah diuji.

## Cara menjalankan

Syarat: PHP 7.4 atau lebih baru (kompatibel dengan XAMPP versi lama maupun baru).

```bash
cd biodata-php
php -S localhost:8000
```

Buka `http://localhost:8000` di browser. (Dengan XAMPP/Laragon: salin folder `biodata-php` ke `htdocs`/`www`, lalu buka `http://localhost/biodata-php`.)

## Struktur proyek

```
biodata-php/
├── index.php              # halaman form + pemrosesan + tampilan hasil
├── includes/validator.php # opsi form, fungsi validasi server, helper escape
├── assets/style.css       # tampilan (responsif, siap cetak)
├── assets/app.js          # event & logika sisi klien
└── README.md
```

## Fitur sesuai rubrik

| Aspek | Implementasi |
|---|---|
| UI & struktur (30%) | Form terbagi 3 bagian (data pribadi, kontak, data akademik), label terhubung ke input, responsif di HP, fokus keyboard terlihat, halaman hasil siap cetak. |
| Event & logika (30%) | Validasi saat kolom ditinggalkan (`blur`), umur terhitung otomatis dari tanggal lahir, penghitung karakter alamat, NIM hanya menerima angka, tombol kirim dikunci setelah submit, tombol Kosongkan membersihkan pesan error. |
| Validasi input (25%) | Dua lapis: JavaScript (cepat) dan PHP (penentu akhir). Aturan ada di tabel di bawah. |
| Kerapian kode (15%) | Kode dipisah per tanggung jawab, ada komentar, `declare(strict_types=1)`, output di-escape dengan `htmlspecialchars`, token CSRF pada form. |

## Aturan validasi

| Kolom | Aturan |
|---|---|
| Nama lengkap | Wajib, 3–60 karakter, huruf/spasi/titik/tanda hubung |
| NIM | Wajib, angka 8–12 digit |
| Email | Wajib, format email valid |
| Nomor HP | Wajib, format Indonesia (`08…`, `62…`, `+62…`), 10–13 digit |
| Tempat lahir | Wajib, huruf, 2–40 karakter |
| Tanggal lahir | Wajib, tanggal valid, bukan masa depan, umur 15–60 tahun |
| Jenis kelamin | Wajib, salah satu dari daftar |
| Program studi | Wajib, salah satu dari daftar |
| Semester | Wajib, 1–14 |
| Alamat | Wajib, 10–200 karakter |
| Hobi | Opsional, hanya nilai dari daftar |
| Pernyataan benar | Wajib dicentang |

## Skenario uji singkat

1. Kirim form kosong: semua kolom wajib menampilkan pesan error, fokus pindah ke kolom pertama yang salah.
2. Isi NIM `abc123`: huruf langsung terhapus; isi `123` lalu pindah kolom: error "8–12 digit".
3. Isi email `budi@` : error format email.
4. Isi tanggal lahir tahun ini: error umur; isi tanggal valid: umur muncul otomatis.
5. Nonaktifkan JavaScript lalu kirim data salah: server PHP tetap menolak dan mengisi ulang data lama.
6. Isi semua data dengan benar: muncul halaman "Biodata berhasil dikirim" dengan tombol Cetak.

## Tangkapan layar

Tempel tangkapan layar di sini: form kosong, form dengan error, dan halaman hasil.
