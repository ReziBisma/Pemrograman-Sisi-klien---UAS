<x-layouts.app title="Data Obat">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Data Obat</h2>
        <a href="{{ route('obat.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-plus text-xs"></i> Tambah Obat
        </a>
    </div>

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

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('obat.edit', '__ID__') }}";
        const tbody = document.getElementById('data-body');

        // READ: GET /api/admin/obat
        async function muatData() {
            try {
                const res = await api('/admin/obat');
                isiTabel(tbody, res.data, 4, 'Belum ada data obat', function (o) {
                    return '<tr class="border-t border-slate-100 hover:bg-slate-50 transition">' +
                        '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(o.nama_obat) + '</td>' +
                        '<td class="px-6 py-4"><span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">' +
                            esc(o.kemasan || '-') + '</span></td>' +
                        '<td class="px-6 py-4 font-semibold text-slate-800">' + formatRupiah(o.harga) + '</td>' +
                        tombolAksi(EDIT_URL, 'hapusObat', o.id) +
                        '</tr>';
                });
            } catch (err) {
                tampilAlert('error', err.message);
            }
        }

        // DELETE: DELETE /api/admin/obat/{id}
        function hapusObat(id) {
            hapusData('/admin/obat/' + id, 'Yakin ingin menghapus obat ini?', muatData);
        }

        bacaFlash();
        muatData();
    </script>
    @endpush

</x-layouts.app>