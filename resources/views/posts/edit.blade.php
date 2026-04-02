@extends('layouts.site')

@section('title', 'BrainBites | Edit Post')

@section('content')
    <section class="bb-cosmic-banner mb-8" data-three-wrapper>
        <div>
            <p class="bb-chip">Refine Mode</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Edit Post</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">Polish your explanation, refresh visuals, and make every section easier to absorb.</p>
        </div>

        <div class="bb-model-visual min-h-[220px]">
            <canvas class="bb-model-canvas" data-three-model="galaxy" aria-hidden="true"></canvas>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="bb-card">
            <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                @include('posts._form', ['post' => $post])
            </form>
        </div>

        <aside class="space-y-4">
            <div class="bb-model-card" data-tilt-card>
                <div data-tilt-glare class="bb-post-glare"></div>
                <img
                    src="{{ str_starts_with($post->image_path, 'http') ? $post->image_path : Storage::url($post->image_path) }}"
                    alt="{{ $post->title }}"
                    class="h-44 w-full rounded-xl object-cover"
                >
                <h3 class="mt-4 text-lg font-bold text-slate-900">Current Visual</h3>
                <p class="mt-2 text-sm text-slate-600">Use this as your baseline and evolve the story design.</p>
            </div>

            <div class="bb-model-card" data-three-wrapper>
                <h3 class="text-lg font-bold text-slate-900">Creative Pulse</h3>
                <p class="mt-2 text-sm text-slate-600">A neon scene to keep momentum while editing.</p>
                <div class="bb-model-visual mt-4 min-h-[180px]">
                    <canvas class="bb-model-canvas" data-three-model="crystal" aria-hidden="true"></canvas>
                </div>
            </div>
        </aside>
    </section>
@endsection
