@extends('layouts.site')

@section('title', 'BrainBites | '.$post->title)

@section('content')
    <article class="mb-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center gap-3">
                <span class="bb-chip">{{ $post->category->name }}</span>
                <span class="text-xs text-slate-500">By {{ $post->user->name }}</span>
            </div>

            <h1 class="bb-title-font text-4xl leading-tight text-slate-900 sm:text-5xl">{{ $post->title }}</h1>
            <p class="mt-3 text-lg text-slate-600">{{ $post->summary }}</p>

            <img
                src="{{ str_starts_with($post->image_path, 'http') ? $post->image_path : Storage::url($post->image_path) }}"
                alt="{{ $post->title }}"
                class="mt-6 h-72 w-full rounded-2xl object-cover sm:h-96"
            >

            <div class="prose mt-6 max-w-none text-slate-700 prose-headings:text-slate-900 prose-a:text-cyan-700">
                {!! nl2br(e($post->body)) !!}
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->likes->count() }} likes</span>

                @auth
                    <form action="{{ route('posts.like', $post) }}" method="POST">
                        @csrf
                        <button class="bb-button-secondary" type="submit">
                            {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like this answer' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bb-button-secondary">Log in to like</a>
                @endauth

                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="bb-button-secondary">Edit</a>

                    <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button class="bb-button-secondary" type="submit">Delete</button>
                    </form>
                @endcan
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bb-card">
                <h2 class="text-lg font-bold text-slate-900">Post details</h2>
                <p class="mt-2 text-sm text-slate-600">Published: {{ optional($post->published_at)->format('M d, Y') ?? 'Draft' }}</p>
                <p class="mt-1 text-sm text-slate-600">Visibility: {{ $post->is_public ? 'Public' : 'Private draft' }}</p>
            </div>

            @if ($relatedPosts->isNotEmpty())
                <div class="bb-card">
                    <h2 class="text-lg font-bold text-slate-900">Related Questions</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($relatedPosts as $related)
                            <a href="{{ route('posts.show', $related) }}" class="block rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ $related->category->name }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $related->title }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </article>
@endsection
