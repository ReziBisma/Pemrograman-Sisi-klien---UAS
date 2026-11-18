<x-layouts.app title="Edit Poli">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('polis.index') }}"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Poli</h2>
    </div>

    <div id="alert-area"></div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form id="form-poli">

                {{-- Nama Poli --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Nama Poli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_poli" placeholder="Masukkan nama poli..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300 focus:border-primary focus:outline-none"
                        required>
                    <p class="text-xs text-red-500 mt-1" data-error="nama_poli"></p>
                </div>

                {{-- Keterangan --}}
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Keterangan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="keterangan" rows="4" placeholder="Masukkan keterangan poli..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300 focus:border-primary focus:outline-none"
                        required></textarea>
                    <p class="text-xs text-red-500 mt-1" data-error="keterangan"></p>
                </div>

                {{-- Button --}}
                <div class="flex gap-3">
                    <button type="submit" id="btn-simpan"
                        class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('polis.index') }}"
                        class="px-6 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const POLI_ID = {{ (int) $id }};
        const form = document.getElementById('form-poli');
        const btnSimpan = document.getElementById('btn-simpan');

        // READ satu data: GET /api/admin/polis/{id}, isi form
        async function muatPoli() {
            try {
                const res = await api('/admin/polis/' + POLI_ID);
                form.nama_poli.value = res.data.nama_poli;
                form.keterangan.value = res.data.keterangan ?? '';
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Data poli tidak ditemukan.' : err.message);
                btnSimpan.disabled = true;
            }
        }

        // UPDATE: PUT /api/admin/polis/{id}
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            hapusErrorForm();
            btnSimpan.disabled = true;

            try {
                await api('/admin/polis/' + POLI_ID, {
                    method: 'PUT',
                    body: {
                        nama_poli: form.nama_poli.value,
                        keterangan: form.keterangan.value,
                    },
                });

                simpanFlash('success', 'Poli berhasil diperbarui.');
                window.location.href = "{{ route('polis.index') }}";
            } catch (err) {
                if (err.status === 422) {
                    tampilErrorForm(err.errors);
                } else {
                    tampilAlert('error', err.message);
                }
                btnSimpan.disabled = false;
            }
        });

        muatPoli();
    </script>
    @endpush

</x-layouts.app>