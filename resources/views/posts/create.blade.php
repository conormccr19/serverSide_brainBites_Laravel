@extends('layouts.site')

@section('title', 'BrainBites | Create Post')

@section('content')
    <section class="mb-6">
        <h1 class="bb-title-font text-4xl text-slate-900">Create a New BrainBite</h1>
        <p class="mt-2 text-sm text-slate-600">Share a clear answer to a question people often wonder about.</p>
    </section>

    <section class="bb-card">
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @include('posts._form')
        </form>
    </section>
@endsection
