<x-layouts.app title="Detail Transaksi">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('pasien.transaksi') }}" class="btn btn-sm btn-ghost bg-slate-100">← Kembali</a>
        <h2 class="text-2xl font-bold">Detail Transaksi</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom kiri --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-lg">Informasi Pemeriksaan</h3>
                        <span id="badge-status"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm mt-2">
                        <div><p class="text-slate-500">Tanggal Periksa</p><p id="tgl-periksa" class="font-medium">-</p></div>
                        <div><p class="text-slate-500">Dokter</p><p id="nama-dokter" class="font-medium">-</p></div>
                        <div><p class="text-slate-500">Poli</p><p id="nama-poli" class="font-medium">-</p></div>
                        <div><p class="text-slate-500">Catatan Dokter</p><p id="catatan" class="font-medium">-</p></div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-2">Obat yang Diresepkan</h3>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th><th>Nama Obat</th><th>Kemasan</th><th class="text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody id="obat-body">
                                <tr><td colspan="4" class="text-center text-slate-400">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom kanan --}}
        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-2">Rincian Biaya</h3>
                    <div class="flex justify-between text-sm"><span>Total Obat</span><span id="total-obat">-</span></div>
                    <div class="flex justify-between text-sm"><span>Jasa Dokter</span><span id="jasa-dokter">-</span></div>
                    <div class="divider my-1"></div>
                    <div class="flex justify-between font-bold text-lg"><span>Total Bayar</span><span id="total-biaya">-</span></div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-2">Pembayaran</h3>
                    <div id="kartu-bayar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const TRANSAKSI_ID = {{ (int) $id }};

        function isiTeks(id, teks) {
            document.getElementById(id).textContent = teks;
        }

        // Tabel obat yang diresepkan
        function tampilObat(daftar) {
            const body = document.getElementById('obat-body');

            if (daftar.length === 0) {
                body.innerHTML = '<tr><td colspan="4" class="text-center text-slate-500">Tidak ada obat.</td></tr>';
                return;
            }

            body.innerHTML = daftar.map(function (o, i) {
                return '<tr><td>' + (i + 1) + '</td><td>' + esc(o.nama_obat) + '</td><td>' + esc(o.kemasan || '-') +
                    '</td><td class="text-right">' + formatRupiah(o.harga) + '</td></tr>';
            }).join('');
        }

        // Kartu pembayaran: info lunas, atau form pilih metode
        function tampilPembayaran(t) {
            const box = document.getElementById('kartu-bayar');

            if (t.status_bayar === 'Lunas') {
                const p = t.pembayaran || {};
                box.innerHTML =
                    '<div class="alert alert-success text-white text-sm"><span>Transaksi sudah dibayar.</span></div>' +
                    '<div class="text-sm space-y-1 mt-2">' +
                    '<p><span class="text-slate-500">Metode:</span> ' + esc(p.metode) + '</p>' +
                    '<p><span class="text-slate-500">Jumlah:</span> ' + formatRupiah(p.jumlah) + '</p>' +
                    '<p><span class="text-slate-500">Waktu:</span> ' + formatTanggal(p.tgl_bayar, true) + '</p></div>';
                return;
            }

            const pilihan = ['Tunai', 'Transfer Bank', 'E-Wallet'].map(function (m) {
                return '<label class="flex items-center gap-3 border rounded-lg px-4 py-3 cursor-pointer">' +
                    '<input type="radio" name="metode" value="' + m + '" class="radio radio-primary">' +
                    '<span>' + m + '</span></label>';
            }).join('');

            box.innerHTML =
                '<form id="form-bayar">' +
                '<p class="text-sm text-slate-500 mb-3">Pilih metode pembayaran:</p>' +
                '<div class="space-y-2 mb-2">' + pilihan + '</div>' +
                '<p class="text-xs text-red-500 mb-3" data-error="metode"></p>' +
                '<button type="submit" id="btn-bayar" class="btn w-full bg-[#2d4499] hover:bg-[#1e2d6b] text-white">Bayar Sekarang</button>' +
                '</form>';

            document.getElementById('form-bayar').addEventListener('submit', function (e) {
                e.preventDefault();
                bayar(t.biaya_periksa);
            });
        }

        // Menampilkan seluruh data transaksi
        function render(t) {
            const lunas = t.status_bayar === 'Lunas';
            document.getElementById('badge-status').innerHTML = lunas
                ? '<span class="badge badge-success text-white">Lunas</span>'
                : '<span class="badge badge-warning">Belum Dibayar</span>';

            isiTeks('tgl-periksa', formatTanggal(t.tgl_periksa, true));
            isiTeks('nama-dokter', t.dokter ? t.dokter.nama : '-');
            isiTeks('nama-poli', t.dokter && t.dokter.poli ? t.dokter.poli.nama_poli : '-');
            isiTeks('catatan', t.catatan || '-');
            isiTeks('total-obat', formatRupiah(t.total_obat));
            isiTeks('jasa-dokter', formatRupiah(t.jasa_dokter));
            isiTeks('total-biaya', formatRupiah(t.biaya_periksa));

            tampilObat(t.obat || []);
            tampilPembayaran(t);
        }

        // READ: GET /api/pasien/transaksi/{id}
        async function muat() {
            try {
                const res = await api('/pasien/transaksi/' + TRANSAKSI_ID);
                render(res.data);
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Transaksi tidak ditemukan.' : err.message);
            }
        }

        // Bayar: POST /api/pasien/transaksi/{id}/bayar
        async function bayar(jumlah) {
            hapusErrorForm();

            const dipilih = document.querySelector('input[name="metode"]:checked');
            if (!dipilih) {
                tampilErrorForm({ metode: ['Pilih metode pembayaran.'] });
                return;
            }
            if (!confirm('Bayar ' + formatRupiah(jumlah) + ' sekarang?')) return;

            const tombol = document.getElementById('btn-bayar');
            tombol.disabled = true;

            try {
                const res = await api('/pasien/transaksi/' + TRANSAKSI_ID + '/bayar', {
                    method: 'POST',
                    body: { metode: dipilih.value },
                });

                tampilAlert('success', res.message);
                render(res.data);                 // status langsung berubah menjadi Lunas
            } catch (err) {
                tampilAlert('error', err.message);
                if (err.status === 422) muat();   // mis. sudah dibayar: segarkan tampilan
                else tombol.disabled = false;
            }
        }

        muat();
    </script>
    @endpush

</x-layouts.app>