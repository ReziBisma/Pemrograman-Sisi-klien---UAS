<x-layouts.app title="Jadwal Periksa">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Jadwal Periksa</h2>
        <a href="{{ route('jadwal-periksa.create') }}"
            class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-5">
            <i class="fas fa-plus"></i> Tambah Jadwal Periksa
        </a>
    </div>

    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Mulai</th>
                            <th class="px-6 py-4">Jam Selesai</th>
                            <th class="px-6 py-4">Status</th>
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
        const EDIT_URL = "{{ route('jadwal-periksa.edit', '__ID__') }}";

        const tabel = tabelBerhalaman({
            url: '/dokter/jadwal-periksa',
            kolom: 6,
            pesanKosong: 'Belum ada Jadwal Periksa',
            filter: [
                { nama: 'hari', label: 'Semua Hari', pilihan: [
                    { nilai: 'Senin', teks: 'Senin' }, { nilai: 'Selasa', teks: 'Selasa' },
                    { nilai: 'Rabu', teks: 'Rabu' }, { nilai: 'Kamis', teks: 'Kamis' },
                    { nilai: 'Jumat', teks: 'Jumat' }, { nilai: 'Sabtu', teks: 'Sabtu' },
                    { nilai: 'Minggu', teks: 'Minggu' },
                ] },
                { nama: 'status', label: 'Semua Status', pilihan: [
                    { nilai: 'Aktif', teks: 'Aktif' }, { nilai: 'Tidak Aktif', teks: 'Tidak Aktif' },
                ] },
            ],
            baris: function (j) {
                const aktif = j.status === 'Aktif';
                return '<tr class="hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-4 text-slate-500">' + j.id + '</td>' +
                    '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(j.hari) + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + esc(j.jam_mulai) + '</td>' +
                    '<td class="px-6 py-4 text-slate-500">' + esc(j.jam_selesai) + '</td>' +
                    '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold ' +
                        (aktif ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500') + '">' +
                        esc(j.status) + '</span></td>' +
                    tombolAksi(EDIT_URL, 'hapusJadwal', j.id) +
                    '</tr>';
            },
        });

        // DELETE: DELETE /api/dokter/jadwal-periksa/{id}
        function hapusJadwal(id) {
            hapusData('/dokter/jadwal-periksa/' + id, 'Yakin ingin menghapus Data Jadwal Periksa ini?', tabel.muat);
        }

        bacaFlash();
    </script>
    @endpush

</x-layouts.app>