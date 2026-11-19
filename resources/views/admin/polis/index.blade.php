<x-layouts.app title="Data Poli">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Data Poli
        </h2>

        <a href="{{ route('polis.create') }}" class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                  text-white border-none rounded-lg px-5">
            <i class="fas fa-plus"></i>
            Tambah Poli
        </a>
    </div>

    {{-- Notifikasi --}}
    <div id="alert-area"></div>

    {{-- Card --}}
    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Poli</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Diisi oleh JavaScript dari API --}}
                    <tbody id="poli-body">
                        <tr>
                            <td colspan="3" class="text-center py-14 text-slate-400">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="pagination"></div>

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('polis.edit', '__ID__') }}";

        const tabel = tabelBerhalaman({
            tbody: 'poli-body',
            url: '/admin/polis',
            kolom: 3,
            pesanKosong: 'Belum ada data poli',
            cari: 'Cari nama atau keterangan poli...',
            baris: function (p) {
                return '<tr class="hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(p.nama_poli) + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + esc(p.keterangan) + '</td>' +
                    tombolAksi(EDIT_URL, 'hapusPoli', p.id) +
                    '</tr>';
            },
        });

        // DELETE: DELETE /api/admin/polis/{id}
        function hapusPoli(id) {
            hapusData('/admin/polis/' + id, 'Yakin ingin menghapus poli ini?', tabel.muat);
        }

        bacaFlash();
    </script>
    @endpush

</x-layouts.app>