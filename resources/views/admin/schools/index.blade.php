@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <div class="mb-5 my-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-3xl font-semibold">Data Madrasah</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-sm" href="{{ route('admin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-primary text-sm">Data Madrasah</li>
            </ol>
        </nav>
    </div>

    <!-- Card Wrapper -->
    <div class="rounded-lg">
        @forelse ($schools as $school)
        <div class="bg-white rounded-2xl shadow-md p-4 sm:p-6 border border-gray-100 mb-6">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-school text-teal-600 text-2xl"></i> {{ $school->name }}
                </h3>
            </div>

            <!-- Desktop View Table -->
            <div class="overflow-x-auto desktop-only-table">
                <table class="w-full border-collapse border border-gray-200 text-sm rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 text-left text-gray-700">
                            <th class="border border-gray-200 px-4 py-3 w-1/3 font-bold">Informasi</th>
                            <th class="border border-gray-200 px-4 py-3 font-bold">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="border border-gray-200 px-4 py-3 font-semibold text-gray-600">Nama Madrasah</td>
                            <td class="border border-gray-200 px-4 py-3 font-bold text-gray-900">{{ $school->name }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 px-4 py-3 font-semibold text-gray-600">Telepon / Hotline</td>
                            <td class="border border-gray-200 px-4 py-3 text-gray-800">{{ $school->phone }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 px-4 py-3 font-semibold text-gray-600">Alamat Lengkap</td>
                            <td class="border border-gray-200 px-4 py-3 text-gray-800">{{ $school->address }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 px-4 py-3 font-semibold text-gray-600">Kepala Madrasah</td>
                            <td class="border border-gray-200 px-4 py-3 text-gray-800">{{ $school->principal_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View Cards -->
            <div class="mobile-data-cards space-y-3">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase">Nama Madrasah</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $school->name }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase">Telepon / Hotline</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5"><i class="fas fa-phone mr-1 text-teal-600"></i>{{ $school->phone }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase">Alamat Lengkap</p>
                    <p class="text-sm text-gray-800 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-teal-600"></i>{{ $school->address }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase">Kepala Madrasah</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5"><i class="fas fa-user-tie mr-1 text-teal-600"></i>{{ $school->principal_name }}</p>
                </div>
            </div>

            <!-- Tombol Edit Sekolah di Kanan Bawah -->
            <div class="mt-5 flex justify-end">
                <a href="{{ route('admin.schools.edit', $school->id_school) }}" 
                    style="background-color: #2563eb; color: #ffffff;"
                    class="px-5 py-2.5 text-xs sm:text-sm font-bold rounded-xl hover:opacity-90 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa fa-edit"></i> Edit Madrasah
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl p-8 text-center text-gray-500 shadow border border-gray-100">
            <i class="ti ti-school-off text-4xl text-gray-300 mb-2 block"></i>
            <p class="font-bold text-gray-700">Tidak ada data madrasah yang tersedia.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
