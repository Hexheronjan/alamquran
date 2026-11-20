@extends('layouts.admin.app')

@section('title', 'Riwayat Aktivitas Admin')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 mb-2">
                    <i class="ti ti-arrow-left"></i> Kembali ke Manajemen Admin
                </a>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                    <i class="ti ti-history text-blue-600"></i> Riwayat Aktivitas Admin
                </h1>
                <p class="text-sm text-gray-500 mt-1">Audit trail seluruh tindakan penting yang dilakukan oleh para administrator dalam sistem.</p>
            </div>
            <div>
                <a href="{{ route('admin.admins.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition shadow-sm">
                    <i class="ti ti-users-group text-lg"></i> Daftar Admin
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm border border-gray-200/80">
            <form method="GET" action="{{ route('admin.admins.logs') }}" class="flex flex-col md:flex-row gap-3">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari deskripsi, nama admin, atau alamat IP..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                </div>

                {{-- Filter Admin --}}
                <div class="w-full md:w-56">
                    <select name="admin_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Administrator</option>
                        @foreach($admins as $adm)
                            <option value="{{ $adm->id }}" {{ request('admin_id') == $adm->id ? 'selected' : '' }}>
                                {{ $adm->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Action Type --}}
                <div class="w-full md:w-56">
                    <select name="action" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Jenis Aksi</option>
                        @foreach($actionTypes as $actKey => $actLabel)
                            <option value="{{ $actKey }}" {{ request('action') === $actKey ? 'selected' : '' }}>
                                {{ $actLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit & Reset --}}
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                        <i class="ti ti-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.admins.logs') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-bold flex items-center gap-1">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Log Aktivitas --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200/80">
            <div class="border-b border-gray-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="font-bold text-gray-800 text-base">Catatan Log Aktivitas ({{ $logs->total() }})</h2>
                <span class="text-xs text-gray-400">Diurutkan dari yang paling baru</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-left text-xs font-bold uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3.5">Waktu</th>
                            <th class="px-6 py-3.5">Administrator</th>
                            <th class="px-6 py-3.5">Jenis Aksi</th>
                            <th class="px-6 py-3.5">Keterangan / Aktivitas</th>
                            <th class="px-6 py-3.5">Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php
                                $actionColors = [
                                    'login'                => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'login_failed'         => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'logout'               => 'bg-gray-100 text-gray-700 border-gray-300',
                                    'create_admin'         => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'update_admin'         => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'toggle_status_admin'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'reset_password_admin' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'delete_admin'         => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'update_santri_status' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'update_santri_data'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'delete_santri'        => 'bg-red-50 text-red-700 border-red-200',
                                    'approve_deposit'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'reject_deposit'       => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'delete_deposit'       => 'bg-gray-50 text-gray-700 border-gray-200',
                                ];
                                $actBadge = $actionColors[$log->action] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                $actLabel = $actionTypes[$log->action] ?? ucwords(str_replace('_', ' ', $log->action));
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <p class="font-bold text-gray-800">{{ $log->created_at->translatedFormat('d M Y, H:i:s') }}</p>
                                    <p class="text-gray-400 text-[11px]">{{ $log->created_at->diffForHumans() }}</p>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($log->admin_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800">{{ $log->admin_name }}</p>
                                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                                                {{ $log->admin_role ? ucwords(str_replace('_', ' ', $log->admin_role)) : 'Admin' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $actBadge }}">
                                        {{ $actLabel }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-xs text-gray-700 font-medium leading-relaxed">
                                    {{ $log->description }}
                                    @if($log->target_type)
                                        <span class="text-gray-400 font-mono text-[11px] ml-1">[{{ $log->target_type }} #{{ $log->target_id }}]</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <i class="ti ti-clipboard-x text-4xl block mb-2"></i>
                                    Belum ada catatan aktivitas yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection