@extends('layouts.site')

@section('title', 'BrainBites | Create Post')

@section('content')
    <section class="bb-cosmic-banner mb-8" data-three-wrapper>
        <div>
            <p class="bb-chip">Create Mode</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Create a New BrainBite</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">Turn complex ideas into visual-first explanations that people actually enjoy reading.</p>
        </div>

        <div class="bb-model-visual min-h-[220px]">
            <canvas class="bb-model-canvas" data-three-model="crystal" aria-hidden="true"></canvas>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="bb-card">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @include('posts._form')
            </form>
        </div>

        <aside class="space-y-4">
            <div class="bb-model-card" data-tilt-card>
                <div data-tilt-glare class="bb-post-glare"></div>
                <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1000&q=80" alt="Creative science illustration" class="h-44 w-full rounded-xl object-cover">
                <h3 class="mt-4 text-lg font-bold text-slate-900">Write For Wonder</h3>
                <p class="mt-2 text-sm text-slate-600">Lead with a sharp question, then explain with clear visuals and simple language.</p>
            </div>

            <div class="bb-model-card" data-three-wrapper>
                <h3 class="text-lg font-bold text-slate-900">Live Visual Companion</h3>
                <p class="mt-2 text-sm text-slate-600">Keep the energy up while writing with a dynamic scene.</p>
                <div class="bb-model-visual mt-4 min-h-[180px]">
                    <canvas class="bb-model-canvas" data-three-model="atom" aria-hidden="true"></canvas>
                </div>
            </div>
        </aside>
    </section>
@endsection
