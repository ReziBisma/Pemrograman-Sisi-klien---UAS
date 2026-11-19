<x-layouts.app title="Data Obat">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Data Obat</h2>
        <a href="{{ route('obat.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-plus text-xs"></i> Tambah Obat
        </a>
    </div>

    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Obat</th>
                            <th class="px-6 py-4">Kemasan</th>
                            <th class="px-6 py-4">Harga</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-body" class="text-sm text-slate-700">
                        <tr><td colspan="4" class="text-center py-12 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="pagination"></div>

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('obat.edit', '__ID__') }}";

        const tabel = tabelBerhalaman({
            url: '/admin/obat',
            kolom: 4,
            pesanKosong: 'Belum ada data obat',
            cari: 'Cari nama atau kemasan obat...',
            filter: [
                { nama: 'harga_min', label: 'Harga minimum', jenis: 'angka' },
                { nama: 'harga_max', label: 'Harga maksimum', jenis: 'angka' },
            ],
            baris: function (o) {
                return '<tr class="border-t border-slate-100 hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(o.nama_obat) + '</td>' +
                    '<td class="px-6 py-4"><span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">' +
                        esc(o.kemasan || '-') + '</span></td>' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + formatRupiah(o.harga) + '</td>' +
                    tombolAksi(EDIT_URL, 'hapusObat', o.id) +
                    '</tr>';
            },
        });

        // DELETE: DELETE /api/admin/obat/{id}
        function hapusObat(id) {
            hapusData('/admin/obat/' + id, 'Yakin ingin menghapus obat ini?', tabel.muat);
        }

        bacaFlash();
    </script>
    @endpush

</x-layouts.app>