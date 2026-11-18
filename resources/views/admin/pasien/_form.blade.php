{{-- Kolom form pasien, dipakai oleh create dan edit. $edit = true saat edit --}}
@php
    $edit  = $edit ?? false;
    $input = 'w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none';
    $label = 'block text-sm font-semibold text-slate-700 mb-1';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="{{ $label }}">Nama Pasien <span class="text-red-500">*</span></label>
        <input type="text" name="nama" placeholder="Masukkan nama pasien..." class="{{ $input }}" required>
    </div>
    <div>
        <label class="{{ $label }}">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" placeholder="Masukkan email..." class="{{ $input }}" required>
    </div>
    <div>
        <label class="{{ $label }}">No. KTP <span class="text-red-500">*</span></label>
        <input type="number" name="no_ktp" placeholder="Masukkan No. KTP..." class="{{ $input }}" required>
    </div>
    <div>
        <label class="{{ $label }}">No. HP <span class="text-red-500">*</span></label>
        <input type="number" name="no_hp" placeholder="Masukkan No. HP..." class="{{ $input }}" required>
    </div>
</div>

<div class="mb-6">
    <label class="{{ $label }}">Alamat <span class="text-red-500">*</span></label>
    <textarea name="alamat" rows="3" placeholder="Masukkan alamat..." class="{{ $input }}" required></textarea>
</div>

<div class="mb-8">
    <label class="{{ $label }}">Password @unless($edit)<span class="text-red-500">*</span>@endunless</label>
    <input type="password" name="password" class="{{ $input }}"
        placeholder="{{ $edit ? 'Kosongkan jika tidak ingin mengubah...' : 'Minimal 6 karakter...' }}"
        {{ $edit ? '' : 'required' }}>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
        <i class="fas fa-save mr-1"></i> Simpan
    </button>
    <a href="{{ route('pasien.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition">Batal</a>
</div>