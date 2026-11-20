@extends('layouts.admin.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="mx-auto max-w-3xl py-6 px-4">
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold text-gray-900">Edit Pengguna</h1><p class="text-sm text-gray-600">Perbarui data akun wali/pengguna.</p></div>
            <a href="{{ route('admin.pengguna.index') }}" class="text-sm font-bold text-teal-700 hover:underline">Kembali</a>
        </div>
        <div class="rounded-xl bg-white p-6 shadow">
            <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div><label class="mb-2 block text-sm font-bold text-gray-700">Nama Wali / Pengguna</label><input name="name" value="{{ old('name', $pengguna->name) }}" class="w-full rounded-lg border-gray-300" required>@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div><label class="mb-2 block text-sm font-bold text-gray-700">Email</label><input type="email" name="email" value="{{ old('email', $pengguna->email) }}" class="w-full rounded-lg border-gray-300" required>@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Nomor WhatsApp</label>
                    <input name="no_wa" value="{{ old('no_wa', $pengguna->no_wa) }}" class="w-full rounded-lg border-gray-300 font-mono font-bold" placeholder="Contoh: 081234567890" required>
                    <p class="text-xs text-teal-700 mt-1">Nomor ini digunakan wali untuk login dan menerima kode OTP. Mengubah nomor ini otomatis memperbarui nomor orang tua pada seluruh data santri.</p>
                    @error('no_wa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">Password lama tidak dapat ditampilkan demi keamanan. Isi password baru hanya bila ingin menggantinya.</div>
                <div><label class="mb-2 block text-sm font-bold text-gray-700">Password Baru (opsional)</label><input type="password" name="password" class="w-full rounded-lg border-gray-300">@error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div><label class="mb-2 block text-sm font-bold text-gray-700">Konfirmasi Password Baru</label><input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300"></div>
                <div class="border-t pt-5"><p class="mb-2 text-sm font-bold text-gray-700">Santri Diterima</p>@forelse($pengguna->santri as $santri)<a href="{{ route('admin.santri.edit', $santri->id) }}" class="mr-2 inline-block rounded-lg bg-teal-50 px-3 py-2 text-sm font-bold text-teal-700 hover:bg-teal-100">{{ $santri->nama_lengkap }} - Diterima</a>@empty<p class="text-sm text-gray-500">Belum ada santri berstatus diterima.</p>@endforelse</div>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
