<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Perbaiki Data Pendaftaran</h2></x-slot>
    <div class="min-h-screen bg-gray-50 py-8 px-4"><div class="max-w-3xl mx-auto">
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 mb-6"><h3 class="font-bold text-amber-900">⚠️ Catatan Perbaikan</h3><p class="mt-2 text-sm text-amber-800">{{ $santri->correctionNoteOpen->catatan }}</p></div>
        <form method="POST" action="{{ route('santri.registration.correction.update', $santri->id) }}" class="bg-white rounded-2xl shadow p-6 space-y-6">
            @csrf @method('PUT')
            <p class="text-sm text-gray-500">Perbaiki hanya data yang diminta admin. Setelah disimpan, data akan dikirim kembali untuk verifikasi.</p>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach(['nama_lengkap'=>'Nama Lengkap','tempat_lahir'=>'Tempat Lahir','tanggal_lahir'=>'Tanggal Lahir','nik'=>'NIK','nisn'=>'NISN','sekolah_asal'=>'Sekolah Asal','npsn'=>'NPSN'] as $field => $label)
                <div><label class="block text-sm font-bold text-gray-700 mb-1">{{ $label }}</label><input type="{{ str_contains($field, 'tanggal') ? 'date' : 'text' }}" name="{{ $field }}" value="{{ old($field, $field === 'tanggal_lahir' ? optional($santri->$field)->format('Y-m-d') : $santri->$field) }}" class="w-full rounded-lg border-gray-300"></div>
                @endforeach
            </div>
            <h3 class="font-bold border-t pt-5">Data Orang Tua</h3>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach(['nama_ayah'=>'Nama Ayah/Wali','no_wa_ayah'=>'Nomor WhatsApp','nama_ibu'=>'Nama Ibu','alamat_domisili_ayah'=>'Alamat Domisili'] as $field => $label)
                <div class="{{ $field === 'alamat_domisili_ayah' ? 'md:col-span-2' : '' }}"><label class="block text-sm font-bold text-gray-700 mb-1">{{ $label }}</label>@if($field === 'alamat_domisili_ayah')<textarea name="{{ $field }}" rows="3" class="w-full rounded-lg border-gray-300">{{ old($field, $santri->orangTua?->$field) }}</textarea>@else<input type="text" name="{{ $field }}" value="{{ old($field, $santri->orangTua?->$field) }}" class="w-full rounded-lg border-gray-300">@endif</div>
                @endforeach
            </div>
            <div class="flex justify-end gap-3"><a href="{{ route('santri.registration.status', $santri->id) }}" class="px-4 py-2 rounded-lg bg-gray-100">Batal</a><button class="px-5 py-2 rounded-lg bg-teal-600 text-white font-bold">Simpan Perbaikan</button></div>
        </form>
    </div></div>
</x-app-layout>
