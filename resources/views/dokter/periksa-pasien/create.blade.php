<x-layouts.app title="Periksa Pasien">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Periksa Pasien</h2>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            {{-- Info pasien (diisi JavaScript) --}}
            <div id="info-pasien" class="mb-6 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">
                Memuat data pasien...
            </div>

            <form id="form-periksa">

                {{-- Pilih Obat --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Obat <span class="text-slate-400 font-normal">(Opsional)</span></span>
                    </label>
                    <select id="select-obat" class="select select-bordered w-full rounded-lg border-2 px-4">
                        <option value="">-- Pilih Obat --</option>
                    </select>
                </div>

                {{-- Obat Terpilih --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Obat Terpilih</span>
                    </label>
                    <ul id="obat-terpilih" class="flex flex-col gap-2 mb-2 min-h-[48px]"></ul>
                </div>

                {{-- Rincian biaya (perkiraan; angka final dihitung server) --}}
                <div class="mb-5 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700">
                    <div class="flex justify-between"><span>Total Obat</span><span id="total-obat">Rp 0</span></div>
                    <div class="flex justify-between"><span>Jasa Dokter</span><span id="jasa-dokter"></span></div>
                    <div class="flex justify-between font-bold text-slate-800 mt-2 pt-2 border-t border-slate-200">
                        <span>Total Biaya</span><span id="total-biaya"></span>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-control mb-8">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Catatan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                    </label>
                    <textarea name="catatan" rows="4" placeholder="Masukkan catatan..."
                        class="textarea textarea-bordered w-full border-2 px-4 py-2 rounded-lg resize-none"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="btn-simpan" class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('periksa-pasien.index') }}" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg px-6">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const DAFTAR_ID   = {{ (int) $id }};
        const JASA_DOKTER = 150000;   // sama dengan Periksa::JASA_DOKTER di server

        const form       = document.getElementById('form-periksa');
        const btnSimpan  = document.getElementById('btn-simpan');
        const selectObat = document.getElementById('select-obat');
        const listObat   = document.getElementById('obat-terpilih');

        let semuaObat = [];   // seluruh obat dari API
        let terpilih  = [];   // obat yang dipilih dokter

        // Memuat data pendaftaran dan daftar obat
        async function muatData() {
            try {
                // 1) GET /api/dokter/periksa-pasien, cari pendaftaran dengan id ini
                const res = await api('/dokter/periksa-pasien');
                const dp = res.data.find(function (d) { return d.id === DAFTAR_ID; });

                if (!dp) {
                    tampilAlert('error', 'Data pendaftaran tidak ditemukan.');
                    btnSimpan.disabled = true;
                    return;
                }

                document.getElementById('info-pasien').innerHTML =
                    '<span class="font-semibold text-slate-800">' + esc(dp.pasien ? dp.pasien.nama : '-') + '</span>' +
                    ' &middot; No. Antrian ' + esc(dp.no_antrian) +
                    '<br>Keluhan: ' + esc(dp.keluhan || '-');

                if (dp.sudah_diperiksa) {
                    tampilAlert('error', 'Pasien ini sudah diperiksa.');
                    btnSimpan.disabled = true;
                    return;
                }

                // 2) GET /api/dokter/obat, isi dropdown
                const obat = await api('/dokter/obat');
                semuaObat = obat.data;
                semuaObat.forEach(function (o) {
                    const opt = document.createElement('option');
                    opt.value = o.id;
                    opt.textContent = o.nama_obat + ' - ' + formatRupiah(o.harga);
                    selectObat.appendChild(opt);
                });
            } catch (err) {
                tampilAlert('error', err.message);
                btnSimpan.disabled = true;
            }
        }

        // Pilih obat dari dropdown
        selectObat.addEventListener('change', function () {
            const id = parseInt(selectObat.value);
            const obat = semuaObat.find(function (o) { return o.id === id; });

            if (obat && !terpilih.some(function (o) { return o.id === id; })) {
                terpilih.push(obat);
                render();
            }
            selectObat.selectedIndex = 0;
        });

        function hapusObat(index) {
            terpilih.splice(index, 1);
            render();
        }

        // Tampilkan obat terpilih dan hitung perkiraan biaya
        function render() {
            listObat.innerHTML = '';
            let total = 0;

            terpilih.forEach(function (o, i) {
                total += o.harga;
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700';
                li.innerHTML =
                    '<span>' + esc(o.nama_obat) + ' — <span class="font-semibold">' + formatRupiah(o.harga) + '</span></span>' +
                    '<button type="button" onclick="hapusObat(' + i + ')" ' +
                    'class="btn btn-sm bg-red-500 hover:bg-red-600 text-white border-none rounded-lg px-3">' +
                    '<i class="fas fa-trash"></i></button>';
                listObat.appendChild(li);
            });

            document.getElementById('total-obat').textContent  = formatRupiah(total);
            document.getElementById('jasa-dokter').textContent = formatRupiah(JASA_DOKTER);
            document.getElementById('total-biaya').textContent = formatRupiah(total + JASA_DOKTER);
        }

        // CREATE: POST /api/dokter/periksa-pasien
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            hapusErrorForm();
            btnSimpan.disabled = true;

            try {
                const res = await api('/dokter/periksa-pasien', {
                    method: 'POST',
                    body: {
                        id_daftar_poli: DAFTAR_ID,
                        catatan: form.catatan.value,
                        obat: terpilih.map(function (o) { return o.id; }),
                    },
                });

                simpanFlash('success', 'Pemeriksaan berhasil disimpan. Total biaya: ' + formatRupiah(res.data.biaya_periksa));
                window.location.href = "{{ route('periksa-pasien.index') }}";
            } catch (err) {
                if (err.status === 422 && Object.keys(err.errors).length > 0) {
                    tampilErrorForm(err.errors);
                } else {
                    tampilAlert('error', err.message);
                }
                btnSimpan.disabled = false;
            }
        });

        render();
        muatData();
    </script>
    @endpush

</x-layouts.app>