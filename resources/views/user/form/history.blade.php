<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h1 class="text-2xl font-bold text-gray-700 p-6 border-b">Riwayat Pendaftaran</h1>
                <div class="p-6 text-gray-900">
                    @forelse ($userSubmissions as $submissionId => $data)
                        <div class="bg-white p-4 shadow rounded-md mb-4 flex items-center justify-between">
                            <!-- Konten Kiri -->
                            <div>
                                <h2 class="text-xl font-semibold">Pendaftaran #{{ $submissionId }}</h2>
                                <h6 class="text-sm font-semibold">
                                    {{ $data['registration_date'] ? \Carbon\Carbon::parse($data['registration_date'])->translatedFormat('l, d F Y') : 'Tidak ada' }}
                                </h6>
                                <!-- Status Pendaftaran -->
                                <p class="mt-1 text-sm">
                                    <strong>Status:</strong>
                                    <span class="px-2 py-1 rounded-md text-white
                                        {{ $data['status'] == 'diterima' ? 'bg-green-500' : ($data['status'] == 'ditolak' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                        {{ ucfirst($data['status']) }}
                                    </span>
                                </p>
                            </div>
    
                            <!-- Tombol Detail di Sebelah Kanan -->
                            <div>
                                <a href="{{ route('user.form_submission.detail_history', $submissionId) }}"
                                   class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada riwayat pendaftaran.</p>
                    @endforelse
                </div>                
            </div>
        </div>
    </div>
    
</x-app-layout>
