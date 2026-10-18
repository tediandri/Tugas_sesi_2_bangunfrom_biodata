<?php
declare(strict_types=1);

/**
 * Konfigurasi opsi + fungsi validasi sisi server.
 * Aturan di sini dicerminkan oleh assets/app.js (validasi sisi klien).
 */

const GENDER = ['Laki-laki', 'Perempuan'];
const PRODI  = [
    'Teknik Informatika',
    'Sistem Informasi',
    'Manajemen Informatika',
    'Teknik Komputer',
    'Desain Komunikasi Visual',
];
const HOBI = ['Membaca', 'Olahraga', 'Musik', 'Coding', 'Traveling', 'Gaming'];

/** Escape output agar aman dari XSS. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Rapikan spasi berlebih. */
function clean($value): string
{
    return trim(preg_replace('/\s+/u', ' ', (string) $value));
}

/** Hitung umur dari tanggal lahir (format Y-m-d). */
function hitung_umur(string $tanggal): int
{
    return (new DateTime($tanggal))->diff(new DateTime('today'))->y;
}

/**
 * Validasi seluruh input.
 *
 * @return array{0: array<string,mixed>, 1: array<string,string>} [data bersih, daftar error]
 */
function validasi_biodata(array $in): array
{
    $errors = [];
    $data = [
        'nama'          => clean($in['nama'] ?? ''),
        'nim'           => clean($in['nim'] ?? ''),
        'email'         => strtolower(clean($in['email'] ?? '')),
        'telepon'       => preg_replace('/[\s\-]/', '', clean($in['telepon'] ?? '')),
        'tempat_lahir'  => clean($in['tempat_lahir'] ?? ''),
        'tanggal_lahir' => clean($in['tanggal_lahir'] ?? ''),
        'gender'        => clean($in['gender'] ?? ''),
        'prodi'         => clean($in['prodi'] ?? ''),
        'semester'      => clean($in['semester'] ?? ''),
        'alamat'        => clean($in['alamat'] ?? ''),
        'hobi'          => array_values(array_intersect(HOBI, (array) ($in['hobi'] ?? []))),
        'setuju'        => isset($in['setuju']),
    ];

    // Nama: huruf, spasi, titik, apostrof, tanda hubung (3-60 karakter)
    if ($data['nama'] === '') {
        $errors['nama'] = 'Nama lengkap wajib diisi.';
    } elseif (!preg_match("/^[\p{L}][\p{L} .'\-]{2,59}$/u", $data['nama'])) {
        $errors['nama'] = 'Gunakan 3–60 karakter berupa huruf, spasi, titik, atau tanda hubung.';
    }

    // NIM: angka 8-12 digit
    if ($data['nim'] === '') {
        $errors['nim'] = 'NIM wajib diisi.';
    } elseif (!preg_match('/^\d{8,12}$/', $data['nim'])) {
        $errors['nim'] = 'NIM harus berupa angka 8–12 digit.';
    }

    // Email
    if ($data['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid, contoh: nama@kampus.ac.id.';
    }

    // Telepon Indonesia: 08xx / +628xx / 628xx
    if ($data['telepon'] === '') {
        $errors['telepon'] = 'Nomor HP wajib diisi.';
    } elseif (!preg_match('/^(\+62|62|0)8[1-9][0-9]{7,10}$/', $data['telepon'])) {
        $errors['telepon'] = 'Nomor HP tidak valid, contoh: 081234567890.';
    }

    // Tempat lahir
    if ($data['tempat_lahir'] === '') {
        $errors['tempat_lahir'] = 'Tempat lahir wajib diisi.';
    } elseif (!preg_match("/^[\p{L}][\p{L} .'\-]{1,39}$/u", $data['tempat_lahir'])) {
        $errors['tempat_lahir'] = 'Tempat lahir hanya boleh berisi huruf (2–40 karakter).';
    }

    // Tanggal lahir: format valid, tidak di masa depan, umur 15-60
    if ($data['tanggal_lahir'] === '') {
        $errors['tanggal_lahir'] = 'Tanggal lahir wajib diisi.';
    } else {
        $dt = DateTime::createFromFormat('!Y-m-d', $data['tanggal_lahir']);
        $ok = $dt && $dt->format('Y-m-d') === $data['tanggal_lahir'];
        if (!$ok) {
            $errors['tanggal_lahir'] = 'Tanggal lahir tidak valid.';
        } elseif ($dt > new DateTime('today')) {
            $errors['tanggal_lahir'] = 'Tanggal lahir tidak boleh di masa depan.';
        } else {
            $umur = hitung_umur($data['tanggal_lahir']);
            if ($umur < 15 || $umur > 60) {
                $errors['tanggal_lahir'] = 'Umur harus antara 15 dan 60 tahun.';
            }
        }
    }

    // Jenis kelamin & prodi harus salah satu dari daftar (cegah manipulasi request)
    if (!in_array($data['gender'], GENDER, true)) {
        $errors['gender'] = 'Pilih jenis kelamin.';
    }
    if (!in_array($data['prodi'], PRODI, true)) {
        $errors['prodi'] = 'Pilih program studi.';
    }

    // Semester 1-14
    if (!ctype_digit($data['semester']) || (int) $data['semester'] < 1 || (int) $data['semester'] > 14) {
        $errors['semester'] = 'Semester harus antara 1 dan 14.';
    }

    // Alamat 10-200 karakter
    $len = mb_strlen($data['alamat']);
    if ($data['alamat'] === '') {
        $errors['alamat'] = 'Alamat wajib diisi.';
    } elseif ($len < 10 || $len > 200) {
        $errors['alamat'] = 'Alamat harus 10–200 karakter.';
    }

    if (!$data['setuju']) {
        $errors['setuju'] = 'Anda harus menyatakan bahwa data yang diisi benar.';
    }

    return [$data, $errors];
}
