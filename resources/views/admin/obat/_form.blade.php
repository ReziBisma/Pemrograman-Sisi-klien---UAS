{{-- Kolom form obat, dipakai oleh create dan edit --}}
@php $input = 'w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none'; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Obat <span class="text-red-500">*</span></label>
        <input type="text" name="nama_obat" placeholder="Masukkan nama obat..." class="{{ $input }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Kemasan</label>
        <input type="text" name="kemasan" placeholder="Contoh: Strip, Botol, Tube..." class="{{ $input }}">
    </div>
</div>

<div class="mb-8">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Harga <span class="text-red-500">*</span></label>
    <div class="flex items-center border-2 rounded-lg px-4 py-2 focus-within:border-primary">
        <span class="text-slate-500 text-sm font-semibold mr-2">Rp</span>
        <input type="number" name="harga" placeholder="0" min="0" step="1" class="w-full focus:outline-none" required>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
        <i class="fas fa-save mr-1"></i> Simpan
    </button>
    <a href="{{ route('obat.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition">Batal</a>
</div>