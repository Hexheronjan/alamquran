@extends('layouts.admin.app')
@section('content')

<div class="container full-container py-5 flex flex-col gap-6">
    <div class="grid grid-cols-1 xl:grid-cols-4 lg:grid-cols-2 gap-6">
        <!-- Card Data Formulir -->
        <div class="card overflow-hidden rounded-lg shadow-md bg-white">
            <div class="p-6 text-center">
                <div class="mt-2 text-center">
                      <i class="p-2 ti ti-file-text text-4xl text-blue-600"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mt-2 mb-2">Data Formulir</h6>
                <p class="text-sm text-gray-500 mb-4">Kelola data formulir pendaftaran.</p>
                <div class="p-4 text-center">
                    <a href="{{ route('admin.form_groups.index') }}" class="bg-blue-600 text-white py-2 px-4 rounded-md text-sm hover:bg-blue-700 transition block w-full">
                        Lihat Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Data Sekolah -->
        <div class="card overflow-hidden rounded-lg shadow-md bg-white">
            <div class="p-6 text-center">
                <div class="mt-2 text-center">
                       <i class="p-2 ti ti-building text-4xl text-blue-600"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mt-2 mb-2">Data Sekolah</h6>
                <p class="text-sm text-gray-500 mb-4">Atur informasi dan profil sekolah.</p>
                <div class="p-4 text-center">
                    <a href="{{ route('admin.schools.index') }}" class="bg-blue-600 text-white py-2 px-4 rounded-md text-sm hover:bg-blue-700 transition block w-full">
                        Lihat Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Data Pendaftaran -->
        <div class="card overflow-hidden rounded-lg shadow-md bg-white">
            <div class="p-6 text-center">
                <div class="mt-2 text-center">
                      <i class="p-2 ti ti-user-check text-4xl text-blue-600"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mt-2 mb-2">Data Pendaftaran</h6>
                <p class="text-sm text-gray-500 mb-4">Kelola pendaftaran siswa secara efisien.</p>
                <div class="p-4 text-center">
                    <a href="{{ route('admin.form_submission.index') }}" class="bg-blue-600 text-white py-2 px-4 rounded-md text-sm hover:bg-blue-700 transition block w-full">
                        Lihat Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Logout -->
        <div class="card overflow-hidden rounded-lg shadow-md bg-white">
            <div class="p-6 text-center">
                <div class="mt-2 text-center">
                      <i class="p-2 ti ti-logout text-4xl text-red-500"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mt-2 mb-2">Logout</h6>
                <p class="text-sm text-gray-500 mb-4">Keluar dari sesi admin dengan aman.</p>
                <div class="p-4 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-md text-sm hover:bg-red-600 transition block w-full">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

