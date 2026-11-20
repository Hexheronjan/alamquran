<div class="id-card-frame">
    {{-- Card Header - Green Gradient --}}
    <div class="relative px-5 pt-5 pb-14 text-center text-white"
        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #22c55e 100%);">
        <div class="flex items-center justify-center gap-1.5 mb-1">
            <i class="ti ti-building text-base opacity-85"></i>
            <p class="text-[10px] font-semibold uppercase tracking-widest opacity-90">Pesantren Tahfizh</p>
        </div>
        <h2 class="text-base font-bold leading-tight">Pesantren Tahfizh Alam Qur'an</h2>
        <p class="text-[11px] opacity-80 mt-0.5">Kartu Santri Digital</p>
    </div>

    {{-- Photo - overlapping header --}}
    <div class="flex justify-center -mt-10 mb-2 relative z-10">
        <img
            src="{{ $s->foto_url }}"
            alt="{{ $s->nama_lengkap }}"
            class="w-20 h-20 rounded-full border-4 border-white object-cover shadow bg-gray-100"
            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($s->nama_lengkap) }}&background=16a34a&color=fff&size=80'"
        >
    </div>

    {{-- Card Body --}}
    <div class="px-5 pb-5 text-center flex-1 flex flex-col justify-between space-y-3">
        {{-- Name & Status --}}
        <div>
            <h3 class="text-base font-bold text-gray-900 leading-snug line-clamp-1">{{ $s->nama_lengkap }}</h3>
            <div class="mt-1 flex justify-center">
                @if(strtolower($s->status_santri ?? $s->status ?? '') === 'aktif' || strtolower($s->status ?? '') === 'diterima')
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-green-200">
                        <i class="ti ti-circle-check text-xs"></i> Santri Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] font-semibold px-2.5 py-0.5 rounded-full border border-gray-200">
                        <i class="ti ti-circle-x text-xs"></i> {{ ucfirst($s->status_santri ?? $s->status ?? 'Santri') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Detail Info Table --}}
        <div class="bg-gray-50 rounded-xl p-3 text-left space-y-1.5 border border-gray-100 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-id-badge-2 text-sm"></i></div>
                <div class="flex-1 min-w-0 flex justify-between items-center">
                    <span class="text-[11px] text-gray-400">NIS</span>
                    <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nis ?? '-' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-hash text-sm"></i></div>
                <div class="flex-1 min-w-0 flex justify-between items-center">
                    <span class="text-[11px] text-gray-400">NISN</span>
                    <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nisn ?? '-' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-school text-sm"></i></div>
                <div class="flex-1 min-w-0 flex justify-between items-center">
                    <span class="text-[11px] text-gray-400">Jenjang</span>
                    <span class="text-xs font-bold text-gray-800">{{ $s->kelas_label ?? $s->kelas ?? '-' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-calendar text-sm"></i></div>
                <div class="flex-1 min-w-0 flex justify-between items-center">
                    <span class="text-[11px] text-gray-400">Tahun Ajaran</span>
                    <span class="text-xs font-bold text-gray-800">
                        {{ $s->tahun_ajaran ?? ($s->angkatan->nama_angkatan ?? ($s->tahun_masuk ? 'Tahun ' . $s->tahun_masuk : '-')) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- QR Code & Footer --}}
        <div class="flex flex-col items-center gap-1 pt-1">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($s->nis ?? $s->id) }}"
                onerror="this.src='https://quickchart.io/qr?size=140&text={{ urlencode($s->nis ?? $s->id) }}'"
                alt="QR Santri"
                class="rounded-lg border border-gray-200 bg-white p-1"
                width="100"
                height="100"
            >
            <p class="font-mono text-[10px] text-gray-500 font-semibold">{{ $s->nis ?? $s->id }}</p>
            <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">Scan untuk Verifikasi Resmi</p>
        </div>
    </div>
</div>
