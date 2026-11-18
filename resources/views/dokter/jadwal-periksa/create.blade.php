<x-layouts.app title="Tambah Jadwal Periksa">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('jadwal-periksa.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Jadwal Periksa</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form id="form-data">
                @include('dokter.jadwal-periksa._form')
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // CREATE: POST /api/dokter/jadwal-periksa (id_dokter diambil server dari login)
        document.getElementById('form-data').addEventListener('submit', function (e) {
            e.preventDefault();
            kirimForm(this, {
                url: '/dokter/jadwal-periksa',
                method: 'POST',
                pesan: 'Jadwal periksa berhasil ditambahkan.',
                redirect: "{{ route('jadwal-periksa.index') }}",
            });
        });
    </script>
    @endpush

</x-layouts.app>