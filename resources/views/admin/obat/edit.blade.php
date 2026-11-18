<x-layouts.app title="Edit Obat">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('obat.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Obat</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form id="form-data">
                @include('admin.obat._form')
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const OBAT_ID = {{ (int) $id }};
        const form = document.getElementById('form-data');

        // READ satu data: GET /api/admin/obat/{id}, lalu isi form
        (async function () {
            try {
                const res = await api('/admin/obat/' + OBAT_ID);
                isiForm(form, res.data);
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Data obat tidak ditemukan.' : err.message);
            }
        })();

        // UPDATE: PUT /api/admin/obat/{id}
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/admin/obat/' + OBAT_ID,
                method: 'PUT',
                pesan: 'Obat berhasil diperbarui.',
                redirect: "{{ route('obat.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>