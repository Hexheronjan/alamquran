@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <!-- Card Wrapper -->
    <div class="card">
        <div class="card-body flex flex-col gap-6 shadow-md">
            <h1 class="text-2xl font-bold text-gray-700">Data Sekolah</h1>
            
            <!-- Tombol Tambah Data Sekolah -->
            <div class="mb-4">
                <a href="{{ route('admin.schools.create') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fa fa-plus mr-2"></i> Tambah Data Sekolah
                </a>
            </div>
            
            <!-- Tabel Data Sekolah -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="myTable">
                    <thead>
                        <tr class="">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Sekolah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kepala Sekolah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schools as $index => $school)
                            <tr class="hover:bg-gray-50 text-sm">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $school->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $school->address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $school->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $school->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $school->principal_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                    <a href="{{ route('admin.schools.edit', $school->id_school) }}" 
                                       class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-red-800 focus:ring-2 focus:outline-none focus:ring-red-300">
                                       <i class="fa fa-edit"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.schools.destroy', $school->id_school) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus sekolah ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-2 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-800 focus:ring-2 focus:outline-none focus:ring-red-300">
                                                <i class="fa fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-500">Belum ada data sekolah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
            <script>
                $('#sidebar-drivers').addClass('active')
            </script>
            <script>
                $(document).ready(function() {
                    var table = $('#myTable').DataTable();
        
                    $('#lokasiFilter').on('change', function() {
                        var selectedValue = $(this).val();
                        table.column(6).search(selectedValue).draw(); // Misalnya, kolom lokasi adalah kolom kedua
                    });
                });
            </script>
            </div>
        </div>
    </div>
</div>
@endsection
