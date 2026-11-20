@extends('layouts.admin.app')

@section('title', 'Log WhatsApp')

@section('content')
<div class="min-h-screen bg-gray-50 p-4 lg:p-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log & Antrean Pesan WhatsApp</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau status pengiriman pesan otomatis dan sinkronisasi status langsung dengan WhatsApp Gateway.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.whatsapp.sync') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm inline-flex items-center gap-1.5 cursor-pointer" title="Tarik dan samakan status pesan terkini langsung dari server WhatsApp Gateway">
                    <i class="ti ti-refresh"></i> Sinkronkan Status dari Gateway
                </button>
            </form>
            <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs transition shadow-sm inline-flex items-center gap-1.5" style="background-color: #0f766e !important; color: #ffffff !important;">
                <i class="ti ti-arrow-left"></i> Kembali ke Gateway
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="ti ti-x"></i></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center justify-between">
            <span>{{ session('warning') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-800"><i class="ti ti-x"></i></button>
        </div>
    @endif

    <form class="mb-4 grid gap-3 rounded-xl bg-white p-4 shadow-sm border border-gray-200 md:grid-cols-4 text-xs">
        <input name="search" value="{{ request('search') }}" placeholder="Nomor, nama, referensi..." class="rounded-lg border-gray-300 px-3 py-2 text-xs">
        <select name="status" class="rounded-lg border-gray-300 px-3 py-2 text-xs">
            <option value="">Semua Status</option>
            <option value="sent" @selected(request('status') === 'sent')>Terkirim (Sent)</option>
            <option value="failed" @selected(request('status') === 'failed')>Gagal / Error</option>
            <option value="pending" @selected(request('status') === 'pending')>Antrean (Pending)</option>
        </select>
        <select name="modul" class="rounded-lg border-gray-300 px-3 py-2 text-xs">
            <option value="">Semua Modul</option>
            @foreach($moduls as $modul)
                <option value="{{ $modul }}" @selected(request('modul') === $modul)>{{ $modul }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs py-2 shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5" style="background-color: #0f766e !important; color: #ffffff !important;">
                <i class="ti ti-filter"></i> Filter
            </button>
            <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold text-xs transition border border-gray-300 inline-flex items-center justify-center" style="background-color: #f3f4f6 !important; color: #374151 !important;">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm border border-gray-200">
        <table class="min-w-full text-xs text-left">
            <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-200">
                <tr>
                    <th class="p-3.5">Waktu</th>
                    <th class="p-3.5">Modul</th>
                    <th class="p-3.5">Penerima & Tujuan</th>
                    <th class="p-3.5 text-center">Status</th>
                    <th class="p-3.5">Isi Pesan (Preview)</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $message)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-3.5 whitespace-nowrap text-gray-500">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                {{ $message->modul ?: 'Umum' }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            <div class="font-bold text-gray-900">{{ $message->display_recipient_name }}</div>
                            <div class="font-mono text-gray-600 text-[11px]">{{ $message->recipient_number }}</div>
                            @if($message->gateway_message_id)
                                <div class="text-[10px] text-gray-400 font-mono">ID Gateway: #{{ $message->gateway_message_id }}</div>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            @if($message->status === 'sent')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" title="Pesan terkirim ke WhatsApp Gateway">
                                    ✓ Sent
                                </span>
                            @elseif($message->status === 'delivered')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800" title="Pesan berhasil sampai di perangkat penerima">
                                    ✓✓ Delivered
                                </span>
                            @elseif($message->status === 'read')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800" title="Pesan telah dibaca oleh penerima">
                                    ✓✓ Read
                                </span>
                            @elseif($message->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    ⏳ Pending
                                </span>
                            @elseif($message->status === 'failed')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800" title="{{ $message->error_message }}">
                                    ✕ Error / Gagal
                                </span>
                                @if($message->error_message)
                                    <div class="text-[10px] text-red-700 font-medium mt-1.5 max-w-[260px] mx-auto text-left bg-red-50 border border-red-200 rounded p-1.5 leading-tight break-words">
                                        <span class="font-bold text-red-800 block mb-0.5"><i class="ti ti-alert-triangle"></i> Info Error:</span>
                                        {{ $message->error_message }}
                                    </div>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                                    {{ ucfirst($message->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 max-w-xs truncate text-gray-600" title="{{ $message->message_body }}">
                            {{ Str::limit(strip_tags($message->message_body), 75) }}
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            @if($message->status === 'pending')
                                <form method="POST" action="{{ route('admin.whatsapp.send_now', $message->id) }}" class="inline"
                                      onsubmit="return confirm('Kirimkan pesan ini ke {{ $message->recipient_number }} sekarang?')">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs cursor-pointer inline-flex items-center gap-1">
                                        <i class="ti ti-send"></i> Kirim Sekarang
                                    </button>
                                </form>
                            @elseif($message->status === 'failed')
                                <form method="POST" action="{{ route('admin.whatsapp.send_now', $message->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs cursor-pointer inline-flex items-center gap-1">
                                        <i class="ti ti-rotate-clockwise-2"></i> Coba lagi
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Belum ada riwayat pesan WhatsApp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
