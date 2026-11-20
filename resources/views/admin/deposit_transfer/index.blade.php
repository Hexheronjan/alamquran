@extends('layouts.admin.app')

@section('title', 'Verifikasi Deposit Transfer')

@section('content')
<div class="bg-gray-100 min-h-screen px-4 py-6 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Verifikasi Deposit Transfer</h1>
        <p class="text-sm text-gray-600">Setujui deposit hanya setelah nominal dan bukti transfer sesuai.</p>
    </div>

    @if(session('success'))<div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">{{ session('success') }}</div>@endif
    @error('deposit')<div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">{{ $message }}</div>@enderror



    <form method="GET" class="mb-4">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
            <option value="">Semua status</option>
            <option value="menunggu_verifikasi" @selected(request('status') === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
            <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow desktop-only-table">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-600"><tr>
                <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Wali / Santri</th><th class="px-4 py-3">Nominal</th><th class="px-4 py-3">Bukti</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th>
            </tr></thead>
            <tbody class="divide-y">
                @forelse($deposit as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3"><b>{{ $item->user->name }}</b><br><span class="text-gray-500">{{ $item->santri->nama_lengkap }}</span></td>
                        <td class="px-4 py-3 font-bold">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-3"><a class="text-blue-600 underline" target="_blank" href="{{ route('admin.deposit_transfer.proof', $item) }}">Lihat bukti</a></td>
                        <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-bold">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>@if($item->catatan_admin)<p class="mt-1 text-xs text-gray-500">{{ $item->catatan_admin }}</p>@endif</td>
                        <td class="px-4 py-3">
                            @if($item->status === 'menunggu_verifikasi')
                                <form method="POST" action="{{ route('admin.deposit_transfer.approve', $item) }}" class="mb-2">@csrf @method('PUT')<input name="catatan_admin" placeholder="Catatan (opsional)" class="mb-1 w-full rounded border p-1 text-xs"><button type="submit" class="rounded px-3 py-1 text-xs font-bold text-white" style="background-color: #16a34a; color: #ffffff;">Setujui</button></form>
                                <form method="POST" action="{{ route('admin.deposit_transfer.reject', $item) }}">@csrf @method('PUT')<input name="catatan_admin" required placeholder="Alasan penolakan" class="mb-1 w-full rounded border p-1 text-xs"><button type="submit" class="rounded px-3 py-1 text-xs font-bold text-white" style="background-color: #dc2626; color: #ffffff;">Tolak</button></form>
                            @else
                                <span class="mr-2 text-xs text-gray-500">Sudah diproses</span>
                            @endif
                            <form method="POST" action="{{ route('admin.deposit_transfer.destroy', $item) }}" class="mt-2" onsubmit="return confirm('Hapus data deposit ini? Jika deposit sudah disetujui, saldo uang saku santri akan dikurangi kembali.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus deposit" aria-label="Hapus deposit" class="rounded px-2 py-1 text-xs text-white" style="background-color: #dc2626; color: #ffffff;"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada pengajuan deposit.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($deposit->hasPages())<div class="p-4">{{ $deposit->links() }}</div>@endif
    </div>
    <div class="mobile-data-cards mt-4 space-y-3">
        @forelse($deposit as $item)
            <article class="rounded-xl bg-white p-4 shadow">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400">{{ $item->created_at->format('d M Y H:i') }}</p>
                        <h3 class="font-bold text-gray-900">{{ $item->user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $item->santri->nama_lengkap }}</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-bold">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                </div>
                <p class="mt-3 text-lg font-extrabold text-teal-700">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</p>
                <a class="mt-2 inline-block text-sm text-blue-600 underline" target="_blank" href="{{ route('admin.deposit_transfer.proof', $item) }}">Lihat bukti transfer</a>
                @if($item->catatan_admin)<p class="mt-2 text-xs text-gray-500">Catatan: {{ $item->catatan_admin }}</p>@endif
                <div class="mt-3 pt-3 border-t border-gray-100">
                    @if($item->status === 'menunggu_verifikasi')
                        <form method="POST" action="{{ route('admin.deposit_transfer.approve', $item) }}" style="margin-bottom: 10px;">
                            @csrf @method('PUT')
                            <input name="catatan_admin" placeholder="Catatan (opsional)"
                                style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px; font-size:12px; margin-bottom:6px; box-sizing:border-box;">
                            <button type="submit"
                                style="width:100%; background-color:#16a34a; color:#ffffff; border:none; border-radius:6px; padding:9px 12px; font-size:13px; font-weight:700; cursor:pointer;">
                                ✓ Setujui
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.deposit_transfer.reject', $item) }}" style="margin-bottom: 10px;">
                            @csrf @method('PUT')
                            <input name="catatan_admin" required placeholder="Alasan penolakan"
                                style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px; font-size:12px; margin-bottom:6px; box-sizing:border-box;">
                            <button type="submit"
                                style="width:100%; background-color:#dc2626; color:#ffffff; border:none; border-radius:6px; padding:9px 12px; font-size:13px; font-weight:700; cursor:pointer;">
                                ✗ Tolak
                            </button>
                        </form>
                    @else
                        <p style="font-size:12px; color:#6b7280; margin-bottom:8px;">Sudah diproses.</p>
                    @endif
                    <form method="POST" action="{{ route('admin.deposit_transfer.destroy', $item) }}" style="margin-top:6px;"
                        onsubmit="return confirm('Hapus data deposit ini? Jika deposit sudah disetujui, saldo uang saku santri akan dikurangi kembali.');">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="background-color:#dc2626; color:#ffffff; border:none; border-radius:6px; padding:8px 14px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-white py-8 text-center text-sm text-gray-500 shadow">Belum ada pengajuan deposit.</p>
        @endforelse
        @if($deposit->hasPages())<div class="rounded-xl bg-white p-4 shadow">{{ $deposit->links() }}</div>@endif
    </div>
</div>
@endsection
