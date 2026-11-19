<x-layouts.app title="Data Pasien">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Data Pasien</h2>
        <a href="{{ route('pasien.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-plus text-xs"></i> Tambah Pasien
        </a>
    </div>

    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">No. KTP</th>
                            <th class="px-6 py-4">No. HP</th>
                            <th class="px-6 py-4">Alamat</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-body" class="text-sm text-slate-700">
                        <tr><td colspan="6" class="text-center py-12 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="pagination"></div>

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('pasien.edit', '__ID__') }}";

        const tabel = tabelBerhalaman({
            url: '/admin/pasien',
            kolom: 6,
            pesanKosong: 'Belum ada data pasien',
            cari: 'Cari nama, email, No. KTP, atau No. RM...',
            baris: function (p) {
                return '<tr class="border-t border-slate-100 hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(p.nama) + '</td>' +
                    '<td class="px-6 py-4">' + esc(p.email) + '</td>' +
                    '<td class="px-6 py-4">' + esc(p.no_ktp || '-') + '</td>' +
                    '<td class="px-6 py-4">' + esc(p.no_hp || '-') + '</td>' +
                    '<td class="px-6 py-4">' + esc(p.alamat || '-') + '</td>' +
                    tombolAksi(EDIT_URL, 'hapusPasien', p.id) +
                    '</tr>';
            },
        });

        // DELETE: DELETE /api/admin/pasien/{id}
        function hapusPasien(id) {
            hapusData('/admin/pasien/' + id, 'Yakin ingin menghapus pasien ini?', tabel.muat);
        }

        bacaFlash();
    </script>
    @endpush

</x-layouts.app>