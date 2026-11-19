<x-layouts.app title="Transaksi">

    <div class="mb-6">
        <h2 class="text-2xl font-bold">Transaksi Saya</h2>
        <p class="text-sm text-slate-500">Riwayat biaya pemeriksaan dan status pembayaran.</p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-sm text-slate-500">Jumlah Transaksi</p>
                <p id="jumlah-transaksi" class="text-2xl font-bold">-</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-sm text-slate-500">Belum Dibayar</p>
                <p id="total-tagihan" class="text-2xl font-bold text-red-500">-</p>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div id="filter-bar"></div>
    <div class="card bg-base-100 shadow">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Dokter / Poli</th>
                        <th>Total Obat</th>
                        <th>Jasa Dokter</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-body">
                    <tr><td colspan="8" class="text-center py-8 text-slate-400">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div id="pagination"></div>

    @push('scripts')
    <script>
                const DETAIL_URL = "{{ route('pasien.transaksi.show', '__ID__') }}";

        const tabel = tabelBerhalaman({
            url: '/pasien/transaksi',
            kolom: 8,
            pesanKosong: 'Belum ada transaksi',
            filter: [
                { nama: 'status', label: 'Semua Status', pilihan: [
                    { nilai: 'belum', teks: 'Belum Dibayar' },
                    { nilai: 'lunas', teks: 'Lunas' },
                ] },
                { nama: 'dari', label: 'Dari tanggal', jenis: 'tanggal' },
                { nama: 'sampai', label: 'Sampai tanggal', jenis: 'tanggal' },
            ],
            setelahMuat: function (res) {
                document.getElementById('jumlah-transaksi').textContent = res.meta.total;
                document.getElementById('total-tagihan').textContent = formatRupiah(res.total_belum_dibayar);
            },
            baris: function (t, nomor) {
                const lunas = t.status_bayar === 'Lunas';
                const dokter = t.dokter;

                const badge = lunas
                    ? '<span class="badge badge-success text-white">Lunas</span>'
                    : '<span class="badge badge-warning">Belum Dibayar</span>';

                const tombol = '<a href="' + DETAIL_URL.replace('__ID__', t.id) + '" class="btn btn-sm ' +
                    (lunas ? 'btn-ghost bg-slate-100' : 'bg-[#2d4499] hover:bg-[#1e2d6b] text-white') + '">' +
                    (lunas ? 'Detail' : 'Bayar') + '</a>';

                return '<tr class="hover:bg-slate-50 transition">' +
                    '<td>' + nomor + '</td>' +
                    '<td>' + formatTanggal(t.tgl_periksa, true) + '</td>' +
                    '<td>' + esc(dokter ? dokter.nama : '-') + '<br><span class="text-xs text-slate-500">' +
                        esc(dokter && dokter.poli ? dokter.poli.nama_poli : '-') + '</span></td>' +
                    '<td>' + formatRupiah(t.total_obat) + '</td>' +
                    '<td>' + formatRupiah(t.jasa_dokter) + '</td>' +
                    '<td class="font-semibold">' + formatRupiah(t.biaya_periksa) + '</td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="text-center">' + tombol + '</td></tr>';
            },
        });
    </script>
    @endpush

</x-layouts.app>