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

    @push('scripts')
    <script>
        const EDIT_URL = "{{ route('polis.edit', '__ID__') }}";
        const tbody = document.getElementById('poli-body');

        const BARIS_KOSONG =
            '<tr><td colspan="3" class="text-center py-14 text-slate-400">' +
            '<i class="fas fa-inbox text-3xl mb-3 block"></i>Belum ada data poli</td></tr>';

        // READ: GET /api/admin/polis
        async function muatPoli() {
            try {
                const res = await api('/admin/polis');
                tampilTabel(res.data);
            } catch (err) {
                tampilAlert('error', err.message);
                tbody.innerHTML =
                    '<tr><td colspan="3" class="text-center py-14 text-red-400">Gagal memuat data</td></tr>';
            }
        }

        function tampilTabel(polis) {
            if (polis.length === 0) {
                tbody.innerHTML = BARIS_KOSONG;
                return;
            }

            tbody.innerHTML = polis.map(function (p) {
                return `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-semibold text-slate-800">${esc(p.nama_poli)}</td>
                    <td class="px-6 py-4 text-slate-500">${esc(p.keterangan)}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="${EDIT_URL.replace('__ID__', p.id)}"
                                class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg px-4">
                                <i class="fas fa-pen-to-square"></i> Edit
                            </a>
                            <button type="button" onclick="hapusPoli(${p.id})"
                                class="btn btn-sm bg-red-500 hover:bg-red-600 text-white border-none rounded-lg px-4">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        // DELETE: DELETE /api/admin/polis/{id}
        async function hapusPoli(id) {
            if (!confirm('Yakin ingin menghapus poli ini?')) return;

            try {
                const res = await api('/admin/polis/' + id, { method: 'DELETE' });
                tampilAlert('success', res.message);
                muatPoli();
            } catch (err) {
                tampilAlert('error', err.message);
            }
        }

        bacaFlash();  // notifikasi dari halaman create/edit
        muatPoli();
    </script>
    @endpush

</x-layouts.app>