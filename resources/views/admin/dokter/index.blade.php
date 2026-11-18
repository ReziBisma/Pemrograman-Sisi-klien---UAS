<x-layouts.app title="Data Dokter">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Data Dokter</h2>
        <a href="{{ route('dokter.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-plus text-xs"></i> Tambah Dokter
        </a>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Dokter</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">No. KTP</th>
                            <th class="px-6 py-4">No. HP</th>
                            <th class="px-6 py-4">Alamat</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-body" class="text-sm text-slate-700">
                        <tr><td colspan="7" class="text-center py-16 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('dokter.edit', '__ID__') }}";
        const tbody = document.getElementById('data-body');

        // READ: GET /api/admin/dokter
        async function muatData() {
            try {
                const res = await api('/admin/dokter');
                isiTabel(tbody, res.data, 7, 'Belum ada data dokter', function (d) {
                    return '<tr class="border-b border-slate-100 hover:bg-slate-50 transition">' +
                        '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(d.nama) + '</td>' +
                        '<td class="px-6 py-4">' + esc(d.email) + '</td>' +
                        '<td class="px-6 py-4">' + esc(d.no_ktp || '-') + '</td>' +
                        '<td class="px-6 py-4">' + esc(d.no_hp || '-') + '</td>' +
                        '<td class="px-6 py-4">' + esc(d.alamat || '-') + '</td>' +
                        '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">' +
                            esc(d.poli ? d.poli.nama_poli : 'Belum Dipilih') + '</span></td>' +
                        tombolAksi(EDIT_URL, 'hapusDokter', d.id) +
                        '</tr>';
                });
            } catch (err) {
                tampilAlert('error', err.message);
            }
        }

        // DELETE: DELETE /api/admin/dokter/{id}
        function hapusDokter(id) {
            hapusData('/admin/dokter/' + id, 'Yakin ingin menghapus dokter ini?', muatData);
        }

        bacaFlash();
        muatData();
    </script>
    @endpush

</x-layouts.app>