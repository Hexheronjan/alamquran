@extends('layouts.admin.app')

@section('title', $title)

@section('content')
<div class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-xl rounded-2xl bg-white p-10 text-center shadow">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-3xl text-blue-600">
            <i class="fas fa-clock"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
        <p class="mt-3 text-gray-600">Coming Soon</p>
    </div>
</div>
@endsection
