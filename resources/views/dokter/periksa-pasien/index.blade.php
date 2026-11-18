<x-layouts.app title="Periksa Pasien">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Periksa Pasien</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-body">
                        <tr><td colspan="5" class="text-center py-14 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const PERIKSA_URL = "{{ route('periksa-pasien.create', '__ID__') }}";
        const tbody = document.getElementById('data-body');

        // READ: GET /api/dokter/periksa-pasien
        async function muatData() {
            try {
                const res = await api('/dokter/periksa-pasien');
                let nomor = 0;

                isiTabel(tbody, res.data, 5, 'Tidak ada data pasien periksa', function (dp) {
                    nomor++;

                    const aksi = dp.sudah_diperiksa
                        ? '<span class="badge bg-green-100 text-green-700 border border-green-200 rounded-lg px-3 py-1 text-xs font-semibold">' +
                          '<i class="fas fa-circle-check mr-1"></i>Sudah Diperiksa</span>'
                        : '<a href="' + PERIKSA_URL.replace('__ID__', dp.id) + '" ' +
                          'class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg px-4">' +
                          '<i class="fas fa-stethoscope"></i> Periksa</a>';

                    return '<tr class="hover:bg-slate-50 transition">' +
                        '<td class="px-6 py-4 text-slate-500">' + nomor + '</td>' +
                        '<td class="px-6 py-4 font-semibold text-slate-800">' + esc(dp.pasien ? dp.pasien.nama : '-') + '</td>' +
                        '<td class="px-6 py-4 text-slate-500">' + esc(dp.keluhan || '-') + '</td>' +
                        '<td class="px-6 py-4 text-slate-500">' + esc(dp.no_antrian) + '</td>' +
                        '<td class="px-6 py-4 text-right">' + aksi + '</td>' +
                        '</tr>';
                });
            } catch (err) {
                tampilAlert('error', err.message);
            }
        }

        bacaFlash();
        muatData();
    </script>
    @endpush

</x-layouts.app>