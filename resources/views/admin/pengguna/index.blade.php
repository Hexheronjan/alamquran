@extends('layouts.admin.app')

@section('title', 'Pengguna')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Pengguna</h1>
            <p class="text-sm text-gray-600">Kelola akun wali dan santri yang sudah diterima.</p>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
        @endif

        <div class="mb-6 rounded-xl bg-white p-4 shadow">
            <form method="GET" action="{{ route('admin.pengguna.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama wali, nama santri, email, atau WhatsApp..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
                </div>
                <div class="w-full md:w-52">
                    <select name="tahun_ajaran" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" {{ request('tahun_ajaran') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" style="background-color: #0d9488; color: #ffffff;" class="px-5 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition border-0 cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.pengguna.index') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-bold flex items-center gap-1">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="font-bold text-gray-800">Daftar Pengguna — Santri Diterima ({{ $pengguna->total() }})</h2>
                @if(request('tahun_ajaran'))
                    <span class="text-xs font-semibold bg-teal-50 text-teal-700 px-2.5 py-1 rounded border border-teal-200">
                        Filter TA: <strong>{{ request('tahun_ajaran') }}</strong>
                    </span>
                @endif
            </div>
            <div class="overflow-x-auto desktop-only-table">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3.5">Nama Wali / Pengguna</th>
                            <th class="px-6 py-3.5">Nama Santri</th>
                            <th class="px-6 py-3.5">Tahun Ajaran</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">WhatsApp</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pengguna as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $user->name }}</td>
                                <td class="px-6 py-4">
                                    @forelse($user->santri as $santri)
                                        <a href="{{ route('admin.santri.edit', $santri->id) }}" class="mb-1 block font-medium text-teal-700 hover:underline">{{ $santri->nama_lengkap }}</a>
                                    @empty
                                        <span class="text-gray-400">Belum mendaftarkan santri</span>
                                    @endforelse
                                </td>
                                <td class="px-6 py-4">
                                    @forelse($user->santri as $santri)
                                        <span class="inline-block px-2.5 py-1 text-xs font-mono font-bold rounded bg-teal-50 text-teal-800 border border-teal-200 mb-1">{{ $santri->tahun_ajaran ?? '-' }}</span>
                                    @empty
                                        <span class="text-gray-400">-</span>
                                    @endforelse
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $user->no_wa }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
                                            <form action="{{ route('admin.impersonate', $user->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Buka portal sebagai wali ananda {{ addslashes($user->name) }} tanpa kirim OTP?')">
                                                @csrf
                                                <button type="submit" style="background-color: #d97706; color: #ffffff;"
                                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold shadow-sm hover:opacity-90 transition border-0 cursor-pointer"
                                                        title="Login otomatis sebagai akun wali ini (Tanpa kirim OTP)">
                                                    <i class="fas fa-sign-in-alt"></i> Login Akun
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.pengguna.edit', $user) }}" style="background-color: #2563eb; color: #ffffff;" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold shadow-sm hover:opacity-90 transition">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada pengguna dengan santri berstatus diterima.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-data-cards p-4 space-y-3">
                @forelse($pengguna as $user)
                    <article class="rounded-xl border border-gray-200 p-4 shadow-sm bg-white">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400">Wali / Pengguna</p>
                                <h3 class="font-bold text-gray-900">{{ $user->name }}</h3>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
                                    <form action="{{ route('admin.impersonate', $user->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Buka portal sebagai wali ananda {{ addslashes($user->name) }} tanpa kirim OTP?')">
                                        @csrf
                                        <button type="submit" style="background-color: #d97706; color: #ffffff;"
                                                class="rounded-lg px-2.5 py-1.5 text-xs font-bold inline-flex items-center gap-1 shadow-sm border-0 cursor-pointer"
                                                title="Login otomatis tanpa OTP">
                                            <i class="fas fa-sign-in-alt"></i> Login
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.pengguna.edit', $user) }}" style="background-color: #2563eb; color: #ffffff;" class="rounded-lg px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1 shadow-sm"><i class="fas fa-edit"></i> Edit</a>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-gray-400">Santri Diterima</p>
                                @forelse($user->santri as $santri)
                                    <a href="{{ route('admin.santri.edit', $santri->id) }}" class="block font-medium text-teal-700">{{ $santri->nama_lengkap }}</a>
                                @empty
                                    <span class="text-gray-400">Belum ada</span>
                                @endforelse
                            </div>
                            <div>
                                <p class="text-gray-400">Tahun Ajaran</p>
                                @forelse($user->santri as $santri)
                                    <span class="font-mono font-bold text-teal-800">{{ $santri->tahun_ajaran ?? '-' }}</span><br>
                                @empty
                                    <span class="text-gray-400">-</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-600 space-y-1">
                            <p><span class="text-gray-400">Email:</span> {{ $user->email }}</p>
                            <p><span class="text-gray-400">WhatsApp:</span> {{ $user->no_wa }}</p>
                        </div>
                    </article>
                @empty
                    <p class="py-8 text-center text-sm text-gray-500 bg-white rounded-xl shadow p-4">Belum ada pengguna dengan santri berstatus diterima.</p>
                @endforelse
            </div>
            @if($pengguna->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">{{ $pengguna->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
