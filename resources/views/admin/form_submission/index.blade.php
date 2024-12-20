@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <!-- Card Wrapper -->
    <div class="card">
        <div class="card-body flex flex-col gap-6 shadow-md">
            <h1 class="text-2xl font-bold text-gray-700">Daftar Formulir PPDB</h1>
            @foreach ($formGroups as $group)
                <div class="bg-white p-4 shadow rounded-md mb-4">
                    <h2 class="text-xl font-semibold">{{ $group->name }}</h2>
                    <p class="text-gray-600">{{ $group->description }}</p>
                    
                    <p class="text-gray-500 mt-2">
                        Total Pendaftar: <strong>{{ $group->submission_count }}</strong>
                    </p>

                    <a href="{{ route('admin.form_submission.group', $group->id_form) }}" 
                    class="px-3 py-2 mt-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Lihat Pendaftar
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection