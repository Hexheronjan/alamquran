@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <!-- Card Wrapper -->
    <div class="card">
        <div class="card-body flex flex-col gap-6 shadow-md">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h1 class="text-2xl font-bold text-gray-700 border-b">Pendaftar untuk {{ $formGroup->name }}</h1>
<hr>
                @forelse ($submissions as $submissionId => $fields)
                    <div class="bg-white p-4 rounded-md mb-4 flex items-center justify-between shadow-md hover:shadow-lg transition-shadow duration-300 shadow-md">
                        <h2 class="text-lg font-semibold">Pendaftaran #{{ $submissionId }}</h2>
                        <a href="{{ route('admin.form_submission.detail_submission', $submissionId) }}" 
                           class="mt-3 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 text-center inline-block transition-colors duration-300">
                            Lihat Detail
                        </a>
                    </div>
                    <hr>
                @empty
                    <p class="text-gray-500">Belum ada pendaftar untuk form ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
