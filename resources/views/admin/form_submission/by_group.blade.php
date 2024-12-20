@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <h1 class="text-2xl font-bold text-gray-700">Pendaftar untuk {{ $formGroup->name }}</h1>

    @forelse ($submissions as $submissionId => $fields)
        <div class="bg-white p-4 shadow rounded-md mb-4">
            <h2 class="text-xl font-semibold">Pendaftaran #{{ $submissionId }}</h2>
            <ul class="mt-2 space-y-2">
                @foreach ($fields as $field)
                    <li>
                        <strong>{{ $field->field_name }}:</strong>
                        <span>{{ $field->value }}</span>
                        @if ($field->file_path)
                            <img src="{{ asset('storage/' . $field->file_path) }}" alt="{{ $field->field_name }}" width="150">
                        @endif
                    </li>
                @endforeach
            </ul>

            <!-- Form untuk mengupdate status -->
            @php
                $currentStatus = $fields->first()->status ?? 'pending';
            @endphp
            <form action="{{ route('admin.form_submission.updateStatus', $submissionId) }}" method="POST" class="mt-3">
                @csrf
                @method('PUT')
                <label for="status" class="block mb-2 font-medium text-gray-700">Status Pendaftaran</label>
                <select name="status" id="status" class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm">
                    <option value="pending" @selected($currentStatus == 'pending')>Pending</option>
                    <option value="diterima" @selected($currentStatus == 'diterima')>Diterima</option>
                    <option value="ditolak" @selected($currentStatus == 'ditolak')>Ditolak</option>
                </select>
                <button type="submit" class="px-3 py-2 mt-2 text-xs font-medium text-white bg-teal-500 rounded-md hover:bg-teal-700">
                    Update Status
                </button>
            </form>
        </div>
    @empty
        <p class="text-gray-500">Belum ada pendaftar untuk form ini.</p>
    @endforelse
</div>
@endsection
