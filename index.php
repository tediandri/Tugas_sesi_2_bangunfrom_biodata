<?php

declare(strict_types=1);
session_start();
require __DIR__ . '/includes/validator.php';

$_SESSION['csrf'] ??= bin2hex(random_bytes(16));

$errors  = [];
$old     = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf'] ?? '');

    if (!hash_equals($_SESSION['csrf'], $token)) {
        $errors['_form'] = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        $old = $_POST;
    } else {
        [$data, $errors] = validasi_biodata($_POST);
        if ($errors === []) {
            $data['umur'] = hitung_umur($data['tanggal_lahir']);
            $success = $data;
            $_SESSION['csrf'] = bin2hex(random_bytes(16)); // token sekali pakai
        } else {
            $old = $data;
        }
    }
}

/** Ambil nilai lama untuk mengisi ulang form. */
function old(string $key, array $old): string
{
    return e(is_array($old[$key] ?? null) ? '' : (string) ($old[$key] ?? ''));
}
function err(string $key, array $errors): string
{
    return e($errors[$key] ?? '');
}
function invalid(string $key, array $errors): string
{
    return isset($errors[$key]) ? ' aria-invalid="true"' : '';
}

$tanggal_maks = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Biodata Mahasiswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="topbar">
        <div class="wrap">
            <h1>Form Biodata Mahasiswa</h1>
            <p>Isi data dengan benar. Kolom bertanda <span class="req">*</span> wajib diisi.</p>
        </div>
    </header>

    <main class="wrap">
        <?php if ($success): ?>
        <!-- ======= HASIL: data tersimpan ======= -->
        <section class="card result" aria-live="polite">
            <div class="result-head">
                <span class="badge" aria-hidden="true">✓</span>
                <div>
                    <h2>Biodata berhasil dikirim</h2>
                    <p>Berikut data yang Anda masukkan.</p>
                </div>
            </div>
            <dl class="data-list">
                <div>
                    <dt>Nama lengkap</dt>
                    <dd><?= e($success['nama']) ?></dd>
                </div>
                <div>
                    <dt>NIM</dt>
                    <dd><?= e($success['nim']) ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd><?= e($success['email']) ?></dd>
                </div>
                <div>
                    <dt>Nomor HP</dt>
                    <dd><?= e($success['telepon']) ?></dd>
                </div>
                <div>
                    <dt>Tempat, tanggal lahir</dt>
                    <dd><?= e($success['tempat_lahir']) ?>,
                        <?= e(date('d-m-Y', strtotime($success['tanggal_lahir']))) ?> (<?= (int) $success['umur'] ?>
                        tahun)</dd>
                </div>
                <div>
                    <dt>Jenis kelamin</dt>
                    <dd><?= e($success['gender']) ?></dd>
                </div>
                <div>
                    <dt>Program studi</dt>
                    <dd><?= e($success['prodi']) ?>, semester <?= (int) $success['semester'] ?></dd>
                </div>
                <div>
                    <dt>Alamat</dt>
                    <dd><?= e($success['alamat']) ?></dd>
                </div>
                <div>
                    <dt>Hobi</dt>
                    <dd><?= $success['hobi'] ? e(implode(', ', $success['hobi'])) : '—' ?></dd>
                </div>
            </dl>
            <div class="actions">
                <button type="button" class="btn primary" onclick="window.print()">Cetak</button>
                <a class="btn" href="index.php">Isi formulir baru</a>
            </div>
        </section>

        <?php else: ?>
        <!-- ======= FORM ======= -->
        <form id="formBiodata" class="card" method="post" action="index.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

            <?php if (isset($errors['_form'])): ?>
            <div class="alert" role="alert"><?= e($errors['_form']) ?></div>
            <?php endif; ?>
            <div id="ringkasanError" class="alert" role="alert" hidden></div>
            <?php if ($errors && !isset($errors['_form'])): ?>
            <div class="alert" role="alert">Ada <?= count($errors) ?> kolom yang perlu diperbaiki. Periksa pesan di
                bawah kolom yang bertanda merah.</div>
            <?php endif; ?>

            <fieldset>
                <legend>Data pribadi</legend>
                <div class="grid">
                    <div class="field span-2">
                        <label for="nama">Nama lengkap <span class="req">*</span></label>
                        <input type="text" id="nama" name="nama" maxlength="60" autocomplete="name"
                            placeholder="Contoh: Budi Santoso" value="<?= old('nama', $old) ?>"
                            <?= invalid('nama', $errors) ?>>
                        <p class="error" id="err-nama"><?= err('nama', $errors) ?></p>
                    </div>

                    <div class="field">
                        <label for="tempat_lahir">Tempat lahir <span class="req">*</span></label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="40"
                            placeholder="Contoh: Jakarta" value="<?= old('tempat_lahir', $old) ?>"
                            <?= invalid('tempat_lahir', $errors) ?>>
                        <p class="error" id="err-tempat_lahir"><?= err('tempat_lahir', $errors) ?></p>
                    </div>

                    <div class="field">
                        <label for="tanggal_lahir">Tanggal lahir <span class="req">*</span></label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" max="<?= $tanggal_maks ?>"
                            value="<?= old('tanggal_lahir', $old) ?>" <?= invalid('tanggal_lahir', $errors) ?>>
                        <p class="hint" id="infoUmur"></p>
                        <p class="error" id="err-tanggal_lahir"><?= err('tanggal_lahir', $errors) ?></p>
                    </div>

                    <div class="field span-2">
                        <span class="label" id="lbl-gender">Jenis kelamin <span class="req">*</span></span>
                        <div class="choices" role="radiogroup" aria-labelledby="lbl-gender">
                            <?php foreach (GENDER as $g): ?>
                            <label class="choice">
                                <input type="radio" name="gender" value="<?= e($g) ?>"
                                    <?= ($old['gender'] ?? '') === $g ? 'checked' : '' ?>>
                                <span><?= e($g) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="error" id="err-gender"><?= err('gender', $errors) ?></p>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Kontak</legend>
                <div class="grid">
                    <div class="field">
                        <label for="email">Email <span class="req">*</span></label>
                        <input type="email" id="email" name="email" autocomplete="email" placeholder="nama@kampus.ac.id"
                            value="<?= old('email', $old) ?>" <?= invalid('email', $errors) ?>>
                        <p class="error" id="err-email"><?= err('email', $errors) ?></p>
                    </div>

                    <div class="field">
                        <label for="telepon">Nomor HP <span class="req">*</span></label>
                        <input type="tel" id="telepon" name="telepon" inputmode="tel" maxlength="16" autocomplete="tel"
                            placeholder="081234567890" value="<?= old('telepon', $old) ?>"
                            <?= invalid('telepon', $errors) ?>>
                        <p class="error" id="err-telepon"><?= err('telepon', $errors) ?></p>
                    </div>

                    <div class="field span-2">
                        <label for="alamat">Alamat <span class="req">*</span></label>
                        <textarea id="alamat" name="alamat" rows="3" maxlength="200"
                            placeholder="Jalan, RT/RW, kelurahan, kota"
                            <?= invalid('alamat', $errors) ?>><?= old('alamat', $old) ?></textarea>
                        <div class="meta">
                            <p class="error" id="err-alamat"><?= err('alamat', $errors) ?></p>
                            <p class="counter"><span id="hitungAlamat">0</span>/200</p>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Data akademik</legend>
                <div class="grid">
                    <div class="field">
                        <label for="nim">NIM <span class="req">*</span></label>
                        <input type="text" id="nim" name="nim" inputmode="numeric" maxlength="12"
                            placeholder="Contoh: 2412345678" value="<?= old('nim', $old) ?>"
                            <?= invalid('nim', $errors) ?>>
                        <p class="error" id="err-nim"><?= err('nim', $errors) ?></p>
                    </div>

                    <div class="field">
                        <label for="semester">Semester <span class="req">*</span></label>
                        <select id="semester" name="semester" <?= invalid('semester', $errors) ?>>
                            <option value="">Pilih semester</option>
                            <?php for ($i = 1; $i <= 14; $i++): ?>
                            <option value="<?= $i ?>" <?= ($old['semester'] ?? '') === (string) $i ? 'selected' : '' ?>>
                                Semester <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="error" id="err-semester"><?= err('semester', $errors) ?></p>
                    </div>

                    <div class="field span-2">
                        <label for="prodi">Program studi <span class="req">*</span></label>
                        <select id="prodi" name="prodi" <?= invalid('prodi', $errors) ?>>
                            <option value="">Pilih program studi</option>
                            <?php foreach (PRODI as $p): ?>
                            <option value="<?= e($p) ?>" <?= ($old['prodi'] ?? '') === $p ? 'selected' : '' ?>>
                                <?= e($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="error" id="err-prodi"><?= err('prodi', $errors) ?></p>
                    </div>

                    <div class="field span-2">
                        <span class="label" id="lbl-hobi">Hobi <span class="optional">(opsional)</span></span>
                        <div class="choices" role="group" aria-labelledby="lbl-hobi">
                            <?php foreach (HOBI as $h): ?>
                            <label class="choice">
                                <input type="checkbox" name="hobi[]" value="<?= e($h) ?>"
                                    <?= in_array($h, (array) ($old['hobi'] ?? []), true) ? 'checked' : '' ?>>
                                <span><?= e($h) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="field consent">
                <label class="choice">
                    <input type="checkbox" id="setuju" name="setuju" value="1"
                        <?= !empty($old['setuju']) ? 'checked' : '' ?>>
                    <span>Saya menyatakan bahwa data di atas benar. <span class="req">*</span></span>
                </label>
                <p class="error" id="err-setuju"><?= err('setuju', $errors) ?></p>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary" id="btnKirim">Kirim biodata</button>
                <button type="reset" class="btn" id="btnReset">Kosongkan</button>
            </div>
        </form>
        <?php endif; ?>
    </main>

    <footer class="wrap foot">BY TEDI ANDI PERMANA</footer>
    <script src="assets/app.js"></script>
</body>

</html>