<x-layouts.app title="Riwayat Pasien">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pasien</h2>
    </div>

    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Biaya</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-body">
                        <tr><td colspan="6" class="text-center py-14 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="pagination"></div>

    @push('scripts')
    <script>
        const DETAIL_URL = "{{ route('riwayat-pasien.show', '__ID__') }}";

        const tabel = tabelBerhalaman({
            url: '/dokter/riwayat-pasien',
            kolom: 6,
            pesanKosong: 'Belum ada riwayat pemeriksaan',
            cari: 'Cari nama pasien atau keluhan...',
            filter: [
                { nama: 'dari', label: 'Dari tanggal', jenis: 'tanggal' },
                { nama: 'sampai', label: 'Sampai tanggal', jenis: 'tanggal' },
            ],
            baris: function (r) {
                return '<tr class="hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 text-slate-500">' + esc(r.no_antrian) + '</td>' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(r.pasien ? r.pasien.nama : '-') + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + esc(r.keluhan || '-') + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + formatTanggal(r.tgl_periksa) + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + formatRupiah(r.biaya_periksa) + '</td>' +
                    '<td class="px-6 py-4 text-right">' +
                        '<a href="' + DETAIL_URL.replace('__ID__', r.id) + '" ' +
                        'class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg px-4">' +
                        '<i class="fas fa-eye"></i> Detail</a>' +
                    '</td></tr>';
            },
        });
    </script>
    @endpush

</x-layouts.app>