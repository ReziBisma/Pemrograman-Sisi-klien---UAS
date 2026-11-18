<x-layouts.app title="Detail Riwayat Pasien">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('riwayat-pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Detail Riwayat</h2>
    </div>

    <div class="w-full flex flex-col gap-5">

        {{-- Informasi Pasien --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Informasi Pasien</h3>
                </div>
                <div class="px-6 py-5 flex flex-col gap-3 text-sm">
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">Nama Pasien</span><span id="nama-pasien" class="text-slate-800 font-semibold">-</span></div>
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">No. Antrian</span><span id="no-antrian" class="text-slate-800">-</span></div>
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">Keluhan</span><span id="keluhan" class="text-slate-800">-</span></div>
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">Poli</span><span id="poli" class="text-slate-800">-</span></div>
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">Dokter</span><span id="nama-dokter" class="text-slate-800">-</span></div>
                    <div class="flex gap-2"><span class="w-36 text-slate-500 font-medium">Tanggal Periksa</span><span id="tgl-periksa" class="text-slate-800">-</span></div>
                </div>
            </div>
        </div>

        {{-- Catatan Dokter --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Catatan Dokter</h3>
                </div>
                <div id="catatan" class="px-6 py-5 text-sm text-slate-600">-</div>
            </div>
        </div>

        {{-- Obat yang Diresepkan --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Obat yang Diresepkan</h3>
                </div>
                <div id="daftar-obat"><div class="px-6 py-5 text-sm text-slate-400">Memuat data...</div></div>
            </div>
        </div>

        {{-- Total Biaya --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body px-6 py-5">
                <div class="flex justify-between text-sm text-slate-500"><span>Total Obat</span><span id="total-obat">-</span></div>
                <div class="flex justify-between text-sm text-slate-500"><span>Jasa Dokter</span><span id="jasa-dokter">-</span></div>
                <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-200">
                    <span class="font-bold text-slate-700">Total Biaya Periksa</span>
                    <span id="total-biaya" class="text-2xl font-bold text-[#2d4499]">-</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const RIWAYAT_ID = {{ (int) $id }};

        function isiTeks(id, teks) {
            document.getElementById(id).textContent = teks;
        }

        function tampilObat(daftar) {
            const box = document.getElementById('daftar-obat');

            if (daftar.length === 0) {
                box.innerHTML = '<div class="px-6 py-5 text-sm text-slate-400">Tidak ada obat yang diresepkan</div>';
                return;
            }

            let baris = '';
            daftar.forEach(function (o, i) {
                baris += '<tr class="hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 text-slate-500">' + (i + 1) + '</td>' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(o.nama_obat) + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + formatRupiah(o.harga) + '</td></tr>';
            });

            box.innerHTML = '<table class="table table-zebra w-full">' +
                '<thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider"><tr>' +
                '<th class="px-6 py-4">#</th><th class="px-6 py-4">Nama Obat</th><th class="px-6 py-4">Harga</th>' +
                '</tr></thead><tbody>' + baris + '</tbody></table>';
        }

        // READ satu data: GET /api/dokter/riwayat-pasien/{id}
        (async function () {
            try {
                const res = await api('/dokter/riwayat-pasien/' + RIWAYAT_ID);
                const r = res.data;

                isiTeks('nama-pasien', r.pasien ? r.pasien.nama : '-');
                isiTeks('no-antrian', r.no_antrian);
                isiTeks('keluhan', r.keluhan || '-');
                isiTeks('poli', r.dokter && r.dokter.poli ? r.dokter.poli.nama_poli : '-');
                isiTeks('nama-dokter', r.dokter ? r.dokter.nama : '-');
                isiTeks('tgl-periksa', formatTanggal(r.tgl_periksa, true));
                isiTeks('catatan', r.catatan || 'Tidak ada catatan');
                isiTeks('total-obat', formatRupiah(r.total_obat));
                isiTeks('jasa-dokter', formatRupiah(r.jasa_dokter));
                isiTeks('total-biaya', formatRupiah(r.biaya_periksa));
                tampilObat(r.obat || []);
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Riwayat tidak ditemukan.' : err.message);
            }
        })();
    </script>
    @endpush

</x-layouts.app>