<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Selamat Datang -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    Selamat datang di aplikasi PPDB {{ $schools->name }}
                </div>
            </div>

            <!-- Daftar Formulir dalam Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($formGroup as $form)
                
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800">{{ $form->name }}</h3>
                            <p class="text-gray-600 mt-2 text-sm">
                                {{ $form->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('user.form.show', ['id_form' => $form->id_form]) }}"
                                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow">
                                     Lihat Form
                                 </a>
                                 
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <p class="text-gray-600">Belum ada formulir yang tersedia.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
