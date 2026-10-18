/**
 * Event & logika sisi klien untuk Form Biodata.
 * Aturan validasi dicerminkan dari includes/validator.php.
 * Server tetap menjadi penentu akhir (validasi ganda).
 */
(function () {
    'use strict';

    const form = document.getElementById('formBiodata');
    if (!form) return; // halaman hasil, tidak ada form

    const $ = (id) => document.getElementById(id);
    const summaryBox = $('ringkasanError');

    /* ---------- Aturan validasi: setiap fungsi mengembalikan pesan error atau '' ---------- */
    const rules = {
        nama(v) {
            if (!v) return 'Nama lengkap wajib diisi.';
            if (!/^[\p{L}][\p{L} .'\-]{2,59}$/u.test(v)) return 'Gunakan 3–60 karakter berupa huruf, spasi, titik, atau tanda hubung.';
            return '';
        },
        nim(v) {
            if (!v) return 'NIM wajib diisi.';
            if (!/^\d{8,12}$/.test(v)) return 'NIM harus berupa angka 8–12 digit.';
            return '';
        },
        email(v) {
            if (!v) return 'Email wajib diisi.';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Format email tidak valid, contoh: nama@kampus.ac.id.';
            return '';
        },
        telepon(v) {
            v = v.replace(/[\s-]/g, '');
            if (!v) return 'Nomor HP wajib diisi.';
            if (!/^(\+62|62|0)8[1-9][0-9]{7,10}$/.test(v)) return 'Nomor HP tidak valid, contoh: 081234567890.';
            return '';
        },
        tempat_lahir(v) {
            if (!v) return 'Tempat lahir wajib diisi.';
            if (!/^[\p{L}][\p{L} .'\-]{1,39}$/u.test(v)) return 'Tempat lahir hanya boleh berisi huruf (2–40 karakter).';
            return '';
        },
        tanggal_lahir(v) {
            if (!v) return 'Tanggal lahir wajib diisi.';
            const d = new Date(v + 'T00:00:00');
            if (isNaN(d)) return 'Tanggal lahir tidak valid.';
            if (d > new Date()) return 'Tanggal lahir tidak boleh di masa depan.';
            const umur = hitungUmur(v);
            if (umur < 15 || umur > 60) return 'Umur harus antara 15 dan 60 tahun.';
            return '';
        },
        semester(v) {
            return v ? '' : 'Semester harus antara 1 dan 14.';
        },
        prodi(v) {
            return v ? '' : 'Pilih program studi.';
        },
        alamat(v) {
            if (!v) return 'Alamat wajib diisi.';
            if (v.length < 10 || v.length > 200) return 'Alamat harus 10–200 karakter.';
            return '';
        },
    };

    /* ---------- Helper ---------- */
    const rapikan = (s) => s.replace(/\s+/g, ' ').trim();

    function hitungUmur(isoDate) {
        const lahir = new Date(isoDate + 'T00:00:00');
        const now = new Date();
        let umur = now.getFullYear() - lahir.getFullYear();
        const belumUlangTahun =
            now.getMonth() < lahir.getMonth() ||
            (now.getMonth() === lahir.getMonth() && now.getDate() < lahir.getDate());
        if (belumUlangTahun) umur--;
        return umur;
    }

    function tampilkanError(name, pesan) {
        const el = form.elements[name];
        const box = $('err-' + name);
        if (box) box.textContent = pesan;
        if (el && el.type !== 'radio' && el.type !== 'checkbox') {
            if (pesan) {
                el.setAttribute('aria-invalid', 'true');
                el.classList.remove('is-valid');
            } else {
                el.removeAttribute('aria-invalid');
                el.classList.add('is-valid');
            }
        }
    }

    /** Validasi satu field berdasarkan nama. Mengembalikan true bila valid. */
    function cekField(name) {
        let pesan = '';
        if (name === 'gender') {
            pesan = form.querySelector('input[name="gender"]:checked') ? '' : 'Pilih jenis kelamin.';
        } else if (name === 'setuju') {
            pesan = $('setuju').checked ? '' : 'Anda harus menyatakan bahwa data yang diisi benar.';
        } else if (rules[name]) {
            pesan = rules[name](rapikan(form.elements[name].value));
        }
        tampilkanError(name, pesan);
        return pesan === '';
    }

    const semuaField = [...Object.keys(rules), 'gender', 'setuju'];

    /* ---------- Event: validasi langsung saat blur, ulangi saat mengetik jika sudah error ---------- */
    Object.keys(rules).forEach((name) => {
        const el = form.elements[name];
        el.addEventListener('blur', () => cekField(name));
        const ev = el.tagName === 'SELECT' || el.type === 'date' ? 'change' : 'input';
        el.addEventListener(ev, () => {
            if (el.hasAttribute('aria-invalid') || el.classList.contains('is-valid')) cekField(name);
        });
    });

    form.querySelectorAll('input[name="gender"]').forEach((r) =>
        r.addEventListener('change', () => cekField('gender')));
    $('setuju').addEventListener('change', () => cekField('setuju'));

    /* ---------- Event: filter karakter saat mengetik ---------- */
    $('nim').addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');          // hanya angka
    });
    $('telepon').addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^\d+\s-]/g, '');   // angka, +, spasi, -
    });

    /* ---------- Event: hitung umur otomatis ---------- */
    const infoUmur = $('infoUmur');
    function perbaruiUmur() {
        const v = $('tanggal_lahir').value;
        infoUmur.textContent = v && !isNaN(new Date(v)) && new Date(v) <= new Date()
            ? 'Umur: ' + hitungUmur(v) + ' tahun'
            : '';
    }
    $('tanggal_lahir').addEventListener('change', perbaruiUmur);

    /* ---------- Event: penghitung karakter alamat ---------- */
    const alamat = $('alamat');
    const hitung = $('hitungAlamat');
    function perbaruiHitung() {
        hitung.textContent = alamat.value.length;
        hitung.parentElement.classList.toggle('warn', alamat.value.length >= 190);
    }
    alamat.addEventListener('input', perbaruiHitung);

    /* ---------- Event: submit ---------- */
    form.addEventListener('submit', (e) => {
        const gagal = semuaField.filter((name) => !cekField(name));

        if (gagal.length) {
            e.preventDefault();
            summaryBox.textContent = 'Ada ' + gagal.length + ' kolom yang perlu diperbaiki. Periksa pesan di bawah kolom yang bertanda merah.';
            summaryBox.hidden = false;

            const pertama = form.elements[gagal[0]];
            const target = pertama instanceof RadioNodeList ? pertama[0] : pertama;
            target.focus({ preventScroll: true });
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        summaryBox.hidden = true;
        $('btnKirim').disabled = true;           // cegah kirim ganda
        $('btnKirim').textContent = 'Mengirim…';
    });

    /* ---------- Event: reset ---------- */
    form.addEventListener('reset', () => {
        semuaField.forEach((name) => {
            tampilkanError(name, '');
            const el = form.elements[name];
            if (el && el.classList) el.classList.remove('is-valid');
        });
        summaryBox.hidden = true;
        // tunggu browser mengosongkan nilai, baru perbarui tampilan
        setTimeout(() => { perbaruiUmur(); perbaruiHitung(); }, 0);
    });

    // Kondisi awal (termasuk saat halaman dimuat ulang dengan data lama dari server)
    perbaruiUmur();
    perbaruiHitung();
})();
