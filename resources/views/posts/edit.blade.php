@extends('layouts.site')

@section('title', 'BrainBites | Edit Post')

@section('content')
    <section class="mb-6">
        <h1 class="bb-title-font text-4xl text-slate-900">Edit Post</h1>
        <p class="mt-2 text-sm text-slate-600">Refine your explanation and keep information accurate.</p>
    </section>

    <section class="bb-card">
        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            @include('posts._form', ['post' => $post])
        </form>
    </section>
@endsection
