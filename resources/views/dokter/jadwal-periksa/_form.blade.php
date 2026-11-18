{{-- Kolom form jadwal periksa, dipakai oleh create dan edit --}}
@php
    $input = 'w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none';
    $label = 'block text-sm font-semibold text-slate-700 mb-1';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div>
        <label class="{{ $label }}">Hari <span class="text-red-500">*</span></label>
        <select name="hari" class="{{ $input }}" required>
            <option value="">Pilih Hari</option>
            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                <option value="{{ $hari }}">{{ $hari }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="{{ $label }}">Status <span class="text-red-500">*</span></label>
        <select name="status" class="{{ $input }}" required>
            <option value="Aktif">Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
        </select>
    </div>
    <div>
        <label class="{{ $label }}">Jam Mulai <span class="text-red-500">*</span></label>
        <input type="time" name="jam_mulai" class="{{ $input }}" required>
    </div>
    <div>
        <label class="{{ $label }}">Jam Selesai <span class="text-red-500">*</span></label>
        <input type="time" name="jam_selesai" class="{{ $input }}" required>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
        <i class="fas fa-save"></i> Simpan
    </button>
    <a href="{{ route('jadwal-periksa.index') }}" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg px-6">Batal</a>
</div>