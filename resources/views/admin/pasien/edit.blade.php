<x-layouts.app title="Edit Pasien">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Pasien</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form id="form-data">
                @include('admin.pasien._form', ['edit' => true])
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const PASIEN_ID = {{ (int) $id }};
        const form = document.getElementById('form-data');

        // READ satu data: GET /api/admin/pasien/{id}, lalu isi form
        (async function () {
            try {
                const res = await api('/admin/pasien/' + PASIEN_ID);
                isiForm(form, res.data);
            } catch (err) {
                tampilAlert('error', err.status === 404 ? 'Data pasien tidak ditemukan.' : err.message);
            }
        })();

        // UPDATE: PUT /api/admin/pasien/{id}
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/admin/pasien/' + PASIEN_ID,
                method: 'PUT',
                pesan: 'Pasien berhasil diperbarui.',
                redirect: "{{ route('pasien.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>