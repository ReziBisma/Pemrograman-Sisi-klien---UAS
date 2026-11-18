<x-layouts.app title="Tambah Dokter">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dokter.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Dokter</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form id="form-data">
                @include('admin.dokter._form')
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const form = document.getElementById('form-data');

        // Isi dropdown poli: GET /api/admin/polis
        api('/admin/polis')
            .then(function (res) {
                isiDropdown(form.elements['id_poli'], res.data, 'id', 'nama_poli');
            })
            .catch(function (err) { tampilAlert('error', err.message); });

        // CREATE: POST /api/admin/dokter
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/admin/dokter',
                method: 'POST',
                pesan: 'Dokter berhasil ditambahkan.',
                redirect: "{{ route('dokter.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>