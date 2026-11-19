/**
 * Helper pemanggil API untuk halaman web.
 * Autentikasi memakai sesi login web (Sanctum mode stateful) + CSRF token.
 */

// Escape teks sebelum disisipkan ke HTML (mencegah XSS)
function esc(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML.replace(/"/g, '&quot;');
}

// Memanggil endpoint API. Contoh: api('/admin/polis', { method: 'POST', body: {...} })
async function api(path, options = {}) {
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    };
    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(window.API_BASE + path, {
        method: options.method || 'GET',
        headers: headers,
        credentials: 'same-origin',
        body: options.body ? JSON.stringify(options.body) : null,
    });

    let data = null;
    try {
        data = await response.json();
    } catch (e) {
        // respons bukan JSON
    }

    if (response.status === 401) {
        window.location.href = window.LOGIN_URL; // sesi habis
    }

    if (!response.ok) {
        const error = new Error((data && data.message) || 'Terjadi kesalahan pada server.');
        error.status = response.status;
        error.errors = (data && data.errors) || {};
        throw error;
    }

    return data;
}

// Menampilkan notifikasi di elemen <div id="alert-area">
function tampilAlert(type, message) {
    const area = document.getElementById('alert-area');
    if (!area) return;

    const kelas = type === 'success' ? 'alert-success' : 'alert-error';
    const ikon  = type === 'success' ? 'fa-check-circle' : 'fa-circle-xmark';

    area.innerHTML =
        '<div class="alert ' + kelas + ' mb-4 rounded-xl shadow-sm">' +
        '<i class="fas ' + ikon + '"></i><span>' + esc(message) + '</span></div>';
}

// Notifikasi yang bertahan saat pindah halaman (setelah simpan, lalu redirect)
function simpanFlash(type, message) {
    sessionStorage.setItem('flash', JSON.stringify({ type: type, message: message }));
}

function bacaFlash() {
    const raw = sessionStorage.getItem('flash');
    if (!raw) return;
    sessionStorage.removeItem('flash');
    const flash = JSON.parse(raw);
    tampilAlert(flash.type, flash.message);
}

// ===== PENGGANTI hapusErrorForm & tampilErrorForm =====
function hapusErrorForm() {
    document.querySelectorAll('[data-error], .js-error').forEach(function (el) {
        if (el.classList.contains('js-error')) {
            el.remove();
        } else {
            el.textContent = '';
        }
    });
}

function tampilErrorForm(errors) {
    Object.keys(errors).forEach(function (field) {
        const pesan = errors[field][0];

        // 1) Jika ada penampung data-error="field", pakai itu (halaman Poli)
        const slot = document.querySelector('[data-error="' + field + '"]');
        if (slot) {
            slot.textContent = pesan;
            return;
        }

        // 2) Jika tidak, buat pesan baru di bawah input
        const input = document.querySelector('form [name="' + field + '"]');
        if (!input) return;

        let host = input.parentElement;
        if (host.classList.contains('flex')) host = host.parentElement;

        const p = document.createElement('p');
        p.className = 'text-xs text-red-500 mt-1 js-error';
        p.textContent = pesan;
        host.appendChild(p);
    });
}

// ===== FUNGSI BARU (tambahkan di bagian bawah file) =====

// Format angka menjadi rupiah: 15000 -> Rp 15.000
function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

// Mengisi isi <tbody>. barisFn(item) mengembalikan string HTML <tr>
function isiTabel(tbody, items, jumlahKolom, pesanKosong, barisFn) {
    if (items.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="' + jumlahKolom + '" class="text-center py-12 text-slate-400">' +
            '<i class="fas fa-inbox text-3xl mb-3 block"></i>' + esc(pesanKosong) + '</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(barisFn).join('');
}

// Sel <td> berisi tombol Edit dan Hapus. hapusFn = NAMA fungsi hapus (string)
function tombolAksi(urlEdit, hapusFn, id) {
    return '<td class="px-6 py-4 text-right"><div class="inline-flex items-center gap-2">' +
        '<a href="' + urlEdit.replace('__ID__', id) + '" ' +
        'class="inline-flex items-center gap-1 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition">' +
        '<i class="fas fa-pen-to-square text-xs"></i> Edit</a>' +
        '<button type="button" onclick="' + hapusFn + '(' + id + ')" ' +
        'class="inline-flex items-center gap-1 px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-semibold transition">' +
        '<i class="fas fa-trash text-xs"></i> Hapus</button>' +
        '</div></td>';
}

// DELETE ke API setelah konfirmasi, lalu panggil onSukses (biasanya muat ulang tabel)
async function hapusData(url, konfirmasi, onSukses) {
    if (!confirm(konfirmasi)) return;

    try {
        const res = await api(url, { method: 'DELETE' });
        tampilAlert('success', res.message);
        onSukses();
    } catch (err) {
        tampilAlert('error', err.message);
    }
}

// Mengirim isi form ke API (POST/PUT), lalu pindah halaman
// opsi: { url, method, pesan, redirect }
async function kirimForm(form, opsi) {
    hapusErrorForm();

    const tombol = form.querySelector('button[type="submit"]');
    if (tombol) tombol.disabled = true;

    const body = {};
    new FormData(form).forEach(function (nilai, kunci) {
        if (kunci !== '_token' && kunci !== '_method') body[kunci] = nilai;
    });

    try {
        await api(opsi.url, { method: opsi.method, body: body });
        simpanFlash('success', opsi.pesan);
        window.location.href = opsi.redirect;
    } catch (err) {
        if (err.status === 422 && Object.keys(err.errors).length > 0) {
            tampilErrorForm(err.errors);
        } else {
            tampilAlert('error', err.message);
        }
        if (tombol) tombol.disabled = false;
    }
}

// Mengisi kolom form dari data API (cocokkan berdasarkan atribut name)
function isiForm(form, data) {
    Object.keys(data).forEach(function (kunci) {
        const el = form.elements[kunci];
        if (el && data[kunci] !== null && typeof data[kunci] !== 'object') {
            el.value = data[kunci];
        }
    });
}

// Mengisi <select> dari daftar item API
function isiDropdown(select, items, kunciNilai, kunciLabel) {
    items.forEach(function (item) {
        const opt = document.createElement('option');
        opt.value = item[kunciNilai];
        opt.textContent = item[kunciLabel];
        select.appendChild(opt);
    });
}

// Format tanggal dari server ("2026-11-17 10:30:00") menjadi "17/11/2026" atau "17/11/2026 10:30"
function formatTanggal(teks, denganJam) {
    const d = new Date(String(teks).replace(' ', 'T'));
    if (isNaN(d.getTime())) return '-';

    const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    if (!denganJam) return tgl;

    const jam = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    return tgl + ' ' + jam;
}

// ===== TABEL BERHALAMAN: pencarian + filter + pagination, semuanya lewat API =====
// opsi:
//   url         : endpoint API, mis. '/admin/obat'
//   tbody       : id elemen <tbody> (default 'data-body')
//   kolom       : jumlah kolom tabel (untuk baris kosong)
//   pesanKosong : teks saat data kosong
//   cari        : placeholder kotak pencarian (hilangkan jika tidak perlu pencarian)
//   filter      : [{ nama, label, jenis, pilihan, sumber }]
//                   jenis 'select' (default): pilihan [{nilai, teks}] atau sumber {url, nilai, teks}
//                   jenis 'angka' atau 'tanggal': kolom isian biasa
//   baris       : function (item, nomor) yang mengembalikan string <tr>
//   setelahMuat : (opsional) function (respons) dipanggil setelah data tampil
// Elemen yang dibutuhkan di halaman: <div id="filter-bar"></div> dan <div id="pagination"></div>
// Mengembalikan { muat }, untuk memuat ulang data (mis. setelah hapus)
function tabelBerhalaman(opsi) {
    const tbody = document.getElementById(opsi.tbody || 'data-body');
    const barFilter = document.getElementById('filter-bar');
    const boxHalaman = document.getElementById('pagination');

    const param = { page: 1, per_page: 10 };
    let timer = null;

    // ----- Memuat data dari API -----
    async function muat() {
        const query = new URLSearchParams();
        Object.keys(param).forEach(function (kunci) {
            if (param[kunci] !== '' && param[kunci] !== null && param[kunci] !== undefined) {
                query.set(kunci, param[kunci]);
            }
        });

        try {
            const res = await api(opsi.url + (opsi.url.indexOf('?') === -1 ? '?' : '&') + query.toString());
            const meta = res.meta;

            // Halaman kosong karena data terakhir dihapus: mundur ke halaman terakhir
            if (res.data.length === 0 && meta.current_page > 1) {
                param.page = meta.last_page > 0 ? meta.last_page : 1;
                return muat();
            }

            if (res.data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="' + opsi.kolom + '" class="text-center py-12 text-slate-400">' +
                    '<i class="fas fa-inbox text-3xl mb-3 block"></i>' + esc(opsi.pesanKosong) + '</td></tr>';
            } else {
                tbody.innerHTML = res.data.map(function (item, i) {
                    return opsi.baris(item, meta.from + i);
                }).join('');
            }

            gambarHalaman(meta);
            if (opsi.setelahMuat) opsi.setelahMuat(res);
        } catch (err) {
            tampilAlert('error', err.message);
        }
    }

    // ----- Kontrol halaman -----
    function gambarHalaman(meta) {
        if (!boxHalaman) return;

        if (meta.total === 0) {
            boxHalaman.innerHTML = '';
            return;
        }

        const pilihanPer = [5, 10, 25, 50, 100].map(function (n) {
            return '<option value="' + n + '"' + (n === meta.per_page ? ' selected' : '') + '>' + n + ' / halaman</option>';
        }).join('');

        boxHalaman.innerHTML =
            '<div class="flex flex-wrap items-center justify-between gap-3 px-2 py-4 text-sm text-slate-500">' +
            '<span>Menampilkan ' + meta.from + '–' + meta.to + ' dari ' + meta.total + ' data</span>' +
            '<div class="flex items-center gap-3">' +
            '<select id="per-halaman" class="select select-bordered select-sm">' + pilihanPer + '</select>' +
            '<div class="join">' +
            '<button type="button" data-halaman="' + (meta.current_page - 1) + '" class="join-item btn btn-sm"' +
                (meta.current_page <= 1 ? ' disabled' : '') + '>«</button>' +
            '<button type="button" class="join-item btn btn-sm pointer-events-none">Halaman ' +
                meta.current_page + ' / ' + meta.last_page + '</button>' +
            '<button type="button" data-halaman="' + (meta.current_page + 1) + '" class="join-item btn btn-sm"' +
                (meta.current_page >= meta.last_page ? ' disabled' : '') + '>»</button>' +
            '</div></div></div>';

        boxHalaman.querySelectorAll('[data-halaman]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                param.page = parseInt(tombol.getAttribute('data-halaman'));
                muat();
            });
        });

        document.getElementById('per-halaman').addEventListener('change', function () {
            param.per_page = parseInt(this.value);
            param.page = 1;
            muat();
        });
    }

    // ----- Kotak pencarian dan filter -----
    function gambarFilter() {
        if (!barFilter) return;
        if (!opsi.cari && !(opsi.filter && opsi.filter.length)) return;

        let html = '<div class="flex flex-wrap items-end gap-3 mb-4">';

        if (opsi.cari) {
            html += '<div class="flex-1 min-w-[200px]">' +
                '<input type="search" id="kotak-cari" placeholder="' + esc(opsi.cari) + '" class="input input-bordered w-full"></div>';
        }

        (opsi.filter || []).forEach(function (f) {
            if (f.jenis === 'angka' || f.jenis === 'tanggal') {
                html += '<div><label class="block text-xs text-slate-500 mb-1">' + esc(f.label) + '</label>' +
                    '<input type="' + (f.jenis === 'angka' ? 'number' : 'date') + '" data-filter="' + f.nama + '" ' +
                    'class="input input-bordered"' + (f.jenis === 'angka' ? ' min="0"' : '') + '></div>';
            } else {
                html += '<div><select data-filter="' + f.nama + '" class="select select-bordered">' +
                    '<option value="">' + esc(f.label) + '</option>' +
                    (f.pilihan || []).map(function (p) {
                        return '<option value="' + esc(p.nilai) + '">' + esc(p.teks) + '</option>';
                    }).join('') + '</select></div>';
            }
        });

        html += '<button type="button" id="reset-filter" class="btn btn-ghost bg-slate-100">Reset</button></div>';
        barFilter.innerHTML = html;

        // Pencarian dengan jeda 400 ms agar tidak memanggil API di setiap ketikan
        const kotak = document.getElementById('kotak-cari');
        if (kotak) {
            kotak.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    param.search = kotak.value.trim();
                    param.page = 1;
                    muat();
                }, 400);
            });
        }

        // Filter: setiap perubahan memuat ulang dari halaman 1
        barFilter.querySelectorAll('[data-filter]').forEach(function (el) {
            el.addEventListener('change', function () {
                param[el.getAttribute('data-filter')] = el.value;
                param.page = 1;
                muat();
            });
        });

        // Reset: kosongkan semua pencarian dan filter
        document.getElementById('reset-filter').addEventListener('click', function () {
            Object.keys(param).forEach(function (kunci) {
                if (kunci !== 'page' && kunci !== 'per_page') delete param[kunci];
            });
            param.page = 1;
            if (kotak) kotak.value = '';
            barFilter.querySelectorAll('[data-filter]').forEach(function (el) { el.value = ''; });
            muat();
        });

        // Pilihan filter yang diambil dari API (mis. daftar poli)
        (opsi.filter || []).forEach(function (f) {
            if (!f.sumber) return;
            api(f.sumber.url).then(function (res) {
                const select = barFilter.querySelector('[data-filter="' + f.nama + '"]');
                isiDropdown(select, res.data, f.sumber.nilai, f.sumber.teks);
            }).catch(function (err) {
                tampilAlert('error', err.message);
            });
        });
    }

    gambarFilter();
    muat();

    return { muat: muat };
}