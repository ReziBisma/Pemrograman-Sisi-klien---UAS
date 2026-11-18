<x-layouts.app title="Tambah Obat">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('obat.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Obat</h2>
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
        // CREATE: POST /api/admin/obat
        document.getElementById('form-data').addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/admin/obat',
                method: 'POST',
                pesan: 'Obat berhasil ditambahkan.',
                redirect: "{{ route('obat.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>