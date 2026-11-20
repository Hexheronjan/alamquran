@extends('layouts.admin.app')

@section('title', 'WhatsApp Gateway')

@section('content')
<div class="min-h-screen bg-gray-100 p-4 lg:p-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">WhatsApp Gateway</h1>
            <p class="mt-1 text-sm text-gray-600">Satu antrean dan gateway untuk seluruh notifikasi aplikasi.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.whatsapp.sync') }}" class="inline">
                @csrf
                <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-sm font-bold text-white shadow transition inline-flex items-center gap-1.5 cursor-pointer" title="Tarik dan samakan status pesan terkini langsung dari server WhatsApp Gateway">
                    <i class="ti ti-refresh"></i> Sinkronkan Status dari Gateway
                </button>
            </form>
            <a href="{{ route('admin.whatsapp.logs') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-teal-700 shadow inline-flex items-center gap-1.5" style="color: #0f766e !important;">
                <i class="ti ti-list"></i> Lihat Log
            </a>
        </div>
    </div>

    @if(session('success'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif

    <div class="mb-6 grid gap-3 md:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs text-gray-500">Gateway</p><p class="mt-1 font-bold {{ $connection['connected'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $connection['message'] }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs text-gray-500">Antrean aktif</p><p class="mt-1 text-2xl font-bold">{{ $stats['pending'] + $stats['processing'] }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs text-gray-500">Terkirim hari ini</p><p class="mt-1 text-2xl font-bold">{{ $stats['today'] }}</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <form method="POST" action="{{ route('admin.whatsapp.settings') }}" class="rounded-xl bg-white p-6 shadow">
            @csrf
            <h2 class="mb-4 font-bold text-gray-900">Pengaturan Gateway</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="text-sm font-semibold">Provider<select name="whatsapp_provider" class="mt-1 w-full rounded border-gray-300"><option value="onesender" @selected($settings['provider']==='onesender')>Onesender</option><option value="fonnte" @selected($settings['provider']==='fonnte')>Fonnte</option><option value="wablas" @selected($settings['provider']==='wablas')>Wablas</option><option value="log" @selected($settings['provider']==='log')>Log (development)</option></select></label>
                <label class="text-sm font-semibold">Jam tenang<select name="quiet_hours_enabled" class="mt-1 w-full rounded border-gray-300"><option value="1" @selected($settings['quiet_hours_enabled']==='1')>Aktif</option><option value="0" @selected($settings['quiet_hours_enabled']==='0')>Nonaktif</option></select></label>
                <label class="text-sm font-semibold">Mulai jam tenang<input type="number" min="0" max="23" name="quiet_hours_start" value="{{ $settings['quiet_hours_start'] }}" class="mt-1 w-full rounded border-gray-300"></label>
                <label class="text-sm font-semibold">Berakhir jam tenang<input type="number" min="0" max="23" name="quiet_hours_end" value="{{ $settings['quiet_hours_end'] }}" class="mt-1 w-full rounded border-gray-300"></label>
                <label class="text-sm font-semibold md:col-span-2">URL API Onesender<input name="onesender_api_url" value="{{ $settings['onesender_api_url'] }}" class="mt-1 w-full rounded border-gray-300"></label>
                <label class="text-sm font-semibold md:col-span-2">API key Onesender<input type="password" name="onesender_api_key" placeholder="Biarkan kosong untuk mempertahankan key saat ini" class="mt-1 w-full rounded border-gray-300"></label>
                <label class="text-sm font-semibold md:col-span-2">Tanggal reminder SPP<input name="spp_reminder_days" value="{{ $settings['spp_reminder_days'] }}" placeholder="1,5,8,10" class="mt-1 w-full rounded border-gray-300"><span class="mt-1 block text-xs font-normal text-gray-500">Pisahkan tanggal dengan koma.</span></label>
            </div>
            
            <div class="mt-5 border-t pt-4">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-1">Webhook / Callback URL OneSender</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ url('/api/webhook/onesender') }}" id="webhook_url" class="w-full rounded border-gray-300 bg-gray-50 text-xs font-mono text-gray-700 py-1.5 px-2.5">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhook_url').value); alert('URL Webhook berhasil disalin!');" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded text-xs font-semibold text-gray-700 whitespace-nowrap cursor-pointer">
                        Salin URL
                    </button>
                </div>
                <p class="mt-1 text-[11px] text-gray-500">Pasang URL ini pada menu Webhook di OneSender agar status pesan (terkirim, sampai, dibaca, error) otomatis sinkron secara real-time.</p>
            </div>

            <button class="mt-5 rounded-lg bg-teal-700 hover:bg-teal-800 px-4 py-2 text-sm font-bold text-white transition shadow-sm cursor-pointer" style="background-color: #0f766e !important; color: #ffffff !important;">Simpan Pengaturan</button>
        </form>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.whatsapp.test') }}" class="rounded-xl bg-white p-6 shadow">
                @csrf
                <h2 class="mb-4 font-bold text-gray-900">Kirim Pesan Uji</h2>
                <input name="nomor" required placeholder="08xxxxxxxxxx" class="mb-3 w-full rounded border-gray-300">
                <textarea name="pesan" required rows="3" placeholder="Pesan uji" class="mb-3 w-full rounded border-gray-300"></textarea>
                <button class="rounded-lg bg-teal-700 hover:bg-teal-800 px-4 py-2 text-sm font-bold text-white transition shadow-sm cursor-pointer" style="background-color: #0f766e !important; color: #ffffff !important;">Masukkan ke Antrean</button>
            </form>
            <div class="rounded-xl bg-white p-6 shadow"><h2 class="mb-3 font-bold text-gray-900">Pesan Terbaru</h2>@forelse($recentLogs as $message)<div class="border-b py-2 text-sm"><strong>{{ $message->modul }}</strong> · {{ $message->recipient_number }} <span class="float-right text-xs">{{ $message->status }}</span></div>@empty<p class="text-sm text-gray-500">Belum ada pesan.</p>@endforelse</div>
        </div>
    </div>

    {{-- KELOLA TEMPLATE NOTIFIKASI WHATSAPP --}}
    <div class="mt-8 rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b pb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fab fa-whatsapp text-emerald-600 text-xl"></i> Template Notifikasi WhatsApp Otomatis
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Admin dapat menyesuaikan kalimat notifikasi. Gunakan variabel di bawah untuk menampilkan data santri/wali otomatis.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                {{ count($templates) }} Template Tersedia
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($templates as $tpl)
                <div class="border rounded-xl p-5 bg-gray-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                                {{ $tpl->modul }}
                            </span>
                            <span class="text-xs font-mono font-semibold text-gray-500">
                                Kode: {{ $tpl->kode }}
                            </span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-sm mb-1">{{ $tpl->nama }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $tpl->judul }}</p>

                        {{-- Petunjuk variabel khusus untuk template akun wali --}}
                        @if($tpl->kode === 'AKUN_WALI_BARU')
                            <div class="mb-3 rounded-lg bg-blue-50 border border-blue-100 p-2.5 text-xs text-blue-900">
                                <p class="font-bold mb-1">Variabel yang dapat digunakan:</p>
                                <div class="flex flex-wrap gap-1 font-mono text-[11px]">
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-blue-200">@{{nama_wali}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-blue-200">@{{nama_santri}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-blue-200">@{{no_wa}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-blue-200">@{{link_login}}</span>
                                </div>
                            </div>
                        @elseif($tpl->kode === 'TAHFIDZ_SETORAN')
                            <div class="mb-3 rounded-lg bg-emerald-50 border border-emerald-100 p-2.5 text-xs text-emerald-900">
                                <p class="font-bold mb-1">Variabel yang dapat digunakan:</p>
                                <div class="flex flex-wrap gap-1 font-mono text-[11px]">
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{nama_santri}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{kelas}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{surat}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{ayat}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200 font-bold text-emerald-700">@{{juz}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{jenis_setoran}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{hasil}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{catatan}}</span>
                                    <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200">@{{tanggal}}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.whatsapp.templates.update', $tpl->id) }}" class="mt-2">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Isi Pesan WhatsApp:</label>
                            <textarea name="konten" rows="8" required class="w-full text-xs font-sans rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 p-2.5 bg-white">{{ $tpl->konten }}</textarea>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t">
                            <label class="inline-flex items-center text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked($tpl->is_active) class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 mr-2">
                                Status Aktif
                            </label>
                            <button type="submit" class="rounded-lg bg-teal-700 hover:bg-teal-800 px-4 py-2 text-xs font-bold text-white transition shadow-sm cursor-pointer" style="background-color: #0f766e !important; color: #ffffff !important;">
                                Simpan Template
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
