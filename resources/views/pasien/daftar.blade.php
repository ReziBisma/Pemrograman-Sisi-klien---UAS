<x-layouts.app title="Daftar Poli">
    <div class="flex items-center justify-center px-4">
        <div class="w-full max-w-3xl">

            {{-- Form pendaftaran --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="text-2xl font-bold text-center mb-6">🏥 Pendaftaran Poli</h2>

                    <form id="form-daftar">
                        {{-- Nomor RM --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">Nomor Rekam Medis</label>
                            <input type="text" id="no-rm" value="Memuat..."
                                class="w-full border-2 rounded-lg p-2 bg-gray-100" disabled>
                        </div>

                        {{-- Pilih Poli --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">Pilih Poli</label>
                            <select id="poliSelect" class="w-full border-2 rounded-lg p-2">
                                <option value="">-- Pilih Poli --</option>
                            </select>
                        </div>

                        {{-- Pilih Jadwal --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">Pilih Jadwal Periksa</label>
                            <select name="id_jadwal" id="jadwalSelect" class="w-full border-2 rounded-lg p-2">
                                <option value="">-- Pilih Jadwal --</option>
                            </select>
                        </div>

                        {{-- Keluhan --}}
                        <div class="mb-6">
                            <label class="font-semibold block mb-1">Keluhan</label>
                            <textarea name="keluhan" rows="3" class="w-full border-2 rounded-lg p-2"
                                placeholder="Tulis keluhan anda..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="btn-daftar"
                                class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                Daftar Poli
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Riwayat pendaftaran --}}
            <div class="card bg-base-100 shadow mt-6">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800">Riwayat Pendaftaran</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3">Jadwal</th>
                                    <th class="px-6 py-3">Dokter</th>
                                    <th class="px-6 py-3">Poli</th>
                                    <th class="px-6 py-3">No Antrian</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody id="riwayat-body">
                                <tr><td colspan="5" class="text-center py-8 text-slate-400">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="pagination"></div>

        </div>
    </div>

    @push('scripts')
    <script>
        // Ubah ke true jika pasien hanya boleh memilih jadwal berstatus Aktif
        const HANYA_JADWAL_AKTIF = false;

        const form = document.getElementById('form-daftar');
        const poliSelect = document.getElementById('poliSelect');
        const jadwalSelect = document.getElementById('jadwalSelect');
        const btnDaftar = document.getElementById('btn-daftar');
        let semuaJadwal = [];

        // Isi dropdown jadwal; jika poliId diisi, hanya jadwal dokter dari poli itu
        function tampilJadwal(poliId) {
            jadwalSelect.innerHTML = '<option value="">-- Pilih Jadwal --</option>';

            semuaJadwal.forEach(function (j) {
                const idPoliDokter = j.dokter ? String(j.dokter.id_poli) : '';
                if (poliId !== '' && idPoliDokter !== poliId) return;

                const opt = document.createElement('option');
                opt.value = j.id;
                opt.textContent = j.hari + ' ' + j.jam_mulai + ' - ' + j.jam_selesai +
                    ' Dr. ' + (j.dokter ? j.dokter.nama : '--');
                jadwalSelect.appendChild(opt);
            });
        }

        poliSelect.addEventListener('change', function () {
            tampilJadwal(this.value);
        });

        // Memuat nomor RM, daftar poli, dan jadwal
        async function muatForm() {
            try {
                const me = await api('/me');                  // GET /api/me
                document.getElementById('no-rm').value = me.data.no_rm || '-';

                const res = await api('/pasien/poli');       // GET /api/pasien/poli
                isiDropdown(poliSelect, res.polis, 'id', 'nama_poli');

                semuaJadwal = res.jadwals;
                if (HANYA_JADWAL_AKTIF) {
                    semuaJadwal = semuaJadwal.filter(function (j) { return j.status === 'Aktif'; });
                }
                tampilJadwal('');
            } catch (err) {
                tampilAlert('error', err.message);
            }
        }

                // READ riwayat: GET /api/pasien/pendaftaran (per halaman)
        const tabelRiwayat = tabelBerhalaman({
            tbody: 'riwayat-body',
            url: '/pasien/pendaftaran',
            kolom: 5,
            pesanKosong: 'Belum ada pendaftaran',
            baris: function (d) {
                const j = d.jadwal;
                const dokter = j && j.dokter ? j.dokter : null;
                const status = d.sudah_diperiksa
                    ? '<span class="badge bg-green-100 text-green-700 border border-green-200 rounded-lg px-3 py-1 text-xs font-semibold">Sudah Diperiksa</span>'
                    : '<span class="badge bg-amber-100 text-amber-700 border border-amber-200 rounded-lg px-3 py-1 text-xs font-semibold">Menunggu</span>';

                return '<tr class="hover:bg-slate-50 transition">' +
                    '<td class="px-6 py-3">' + esc(j ? j.hari + ' ' + j.jam_mulai + ' - ' + j.jam_selesai : '-') + '</td>' +
                    '<td class="px-6 py-3 font-semibold text-slate-800">' + esc(dokter ? dokter.nama : '-') + '</td>' +
                    '<td class="px-6 py-3">' + esc(dokter && dokter.poli ? dokter.poli.nama_poli : '-') + '</td>' +
                    '<td class="px-6 py-3">' + esc(d.no_antrian) + '</td>' +
                    '<td class="px-6 py-3">' + status + '</td></tr>';
            },
        });

        // CREATE: POST /api/pasien/pendaftaran (id pasien diambil server dari login)
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            hapusErrorForm();
            btnDaftar.disabled = true;

            try {
                const res = await api('/pasien/pendaftaran', {
                    method: 'POST',
                    body: {
                        id_jadwal: jadwalSelect.value,
                        keluhan: form.keluhan.value,
                    },
                });

                tampilAlert('success', 'Berhasil mendaftar ke poli. Nomor antrian Anda: ' + res.data.no_antrian);
                form.keluhan.value = '';
                jadwalSelect.value = '';
                tabelRiwayat.muat();
            } catch (err) {
                if (err.status === 422 && Object.keys(err.errors).length > 0) {
                    tampilErrorForm(err.errors);
                } else {
                    tampilAlert('error', err.message);
                }
            }

            btnDaftar.disabled = false;
        });

        muatForm();
    </script>
    @endpush
</x-layouts.app>