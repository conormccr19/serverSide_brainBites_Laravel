@extends('layouts.site')

@section('title', 'BrainBites | Dashboard')

@section('content')
    <section class="mb-8 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="bb-title-font text-4xl text-slate-900">Contributor Dashboard</h1>
            <p class="mt-2 text-sm text-slate-600">Manage your posts and track engagement.</p>
        </div>

        <a href="{{ route('posts.create') }}" class="bb-button">Create New Post</a>
    </section>

    <section class="mb-8 grid gap-4 sm:grid-cols-3">
        <article class="bb-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Posts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_posts'] }}</p>
        </article>

        <article class="bb-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public Posts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['public_posts'] }}</p>
        </article>

        <article class="bb-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Likes</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_likes'] }}</p>
        </article>
    </section>

    <section class="bb-card overflow-x-auto">
        <h2 class="mb-4 text-xl font-bold text-slate-900">Your Posts</h2>

        @if ($posts->isEmpty())
            <p class="text-sm text-slate-600">You have not created any posts yet.</p>
        @else
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">Title</th>
                        <th class="px-3 py-2">Category</th>
                        <th class="px-3 py-2">Visibility</th>
                        <th class="px-3 py-2">Likes</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-3 font-semibold text-slate-800">{{ $post->title }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->category->name }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->is_public ? 'Public' : 'Draft' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->likes_count }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>
                                    <a href="{{ route('posts.edit', $post) }}" class="bb-button-secondary">Edit</a>

                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bb-button-secondary" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection
