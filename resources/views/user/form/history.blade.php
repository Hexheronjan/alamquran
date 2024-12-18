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
                    @foreach ($userSubmissions as $submissionId => $fields)
                    <div class="bg-white p-4 shadow rounded-md mb-4">
                        <h2 class="text-xl font-semibold">Pendaftaran #{{ $submissionId }}</h2>
                
                        <!-- Tampilkan Status -->
                        <p class="mt-1 text-sm">
                            <strong>Status:</strong>
                            @php
                                $status = $fields->first()->status ?? 'Pending'; // Ambil status dari elemen pertama
                            @endphp
                            <span class="px-2 py-1 rounded-md text-white
                                {{ $status == 'diterima' ? 'bg-green-500' : ($status == 'ditolak' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                {{ ucfirst($status) }}
                            </span>
                        </p>
                
                        <!-- Daftar Field -->
                        <ul class="mt-2 space-y-2">
                            @foreach ($fields as $field)
                                <li>
                                    <strong>{{ $field->field_name }}:</strong>
                                    <span>{{ $field->value }}</span>
                                </li>
                                @if ($field->file_path)
                                    <img src="{{ asset('storage/' . $field->file_path) }}" alt="{{ $field->field_name }}" width="150">
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach                
                </div>
            </div>
        </div>
    </div>

</x-app-layout>