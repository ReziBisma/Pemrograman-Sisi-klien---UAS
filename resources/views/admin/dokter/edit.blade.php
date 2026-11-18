<x-layouts.app title="Edit Dokter">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dokter.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Dokter</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form id="form-data">
                @include('admin.dokter._form', ['edit' => true])
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const DOKTER_ID = {{ (int) $id }};
        const form = document.getElementById('form-data');

        (async function () {
            try {
                // 1) isi dropdown poli dulu, supaya nilai id_poli bisa dipilih
                const polis = await api('/admin/polis');
                isiDropdown(form.elements['id_poli'], polis.data, 'id', 'nama_poli');

                // 2) ambil data dokter: GET /api/admin/dokter/{id}
                const res = await api('/admin/dokter/' + DOKTER_ID);
                isiForm(form, res.data);
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Data dokter tidak ditemukan.' : err.message);
            }
        })();

        // UPDATE: PUT /api/admin/dokter/{id}
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/admin/dokter/' + DOKTER_ID,
                method: 'PUT',
                pesan: 'Dokter berhasil diperbarui.',
                redirect: "{{ route('dokter.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>