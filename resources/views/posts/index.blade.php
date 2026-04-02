@extends('layouts.site')

@section('title', 'BrainBites | Curiosity, explained')

@section('content')
    <section class="bb-hero-grid mb-8">
        <div class="bb-hero-content">
            <p class="bb-kicker">Immersive Science Discovery</p>
            <h1 class="bb-title-font text-4xl leading-tight text-white sm:text-5xl lg:text-6xl">
                Not another wall of text.
                <span class="block text-cyan-200">A visual playground for curious minds.</span>
            </h1>

            <p class="mt-5 max-w-xl text-sm text-cyan-50/90 sm:text-base">
                Explore animated, image-rich explanations for "why", "how", and "when" questions.
                BrainBites is built to pull students, parents, and casual browsers into discovery in seconds.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#latest" class="bb-button">Dive Into Questions</a>
                @guest
                    <a href="{{ route('register') }}" class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20">Start Contributing</a>
                @else
                    <a href="{{ route('posts.create') }}" class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20">Create Your Post</a>
                @endguest
            </div>
        </div>

        <div class="bb-hero-visual" data-hero-visual>
            <canvas id="brainbites-hero-canvas" class="bb-hero-canvas" aria-hidden="true"></canvas>
            <span class="bb-orbit-chip -a">Live 3D Orbit</span>
            <span class="bb-orbit-chip -b">Interactive Cards</span>
            <span class="bb-orbit-chip -c">Fast Discovery</span>
        </div>
    </section>

    <section class="bb-glass bb-search-panel">
        <form action="{{ route('posts.index') }}" method="GET" class="grid gap-4 md:grid-cols-4 md:items-end">
            <div class="md:col-span-2">
                <label for="search" class="bb-label">Search Questions</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    class="bb-input"
                    value="{{ $search }}"
                    placeholder="Try: Why is the sky blue?"
                >
            </div>

            <div>
                <label for="category" class="bb-label">Category</label>
                <select id="category" name="category" class="bb-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item->slug }}" @selected($selectedCategory === $item->slug)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sort" class="bb-label">Sort</label>
                <select id="sort" name="sort" class="bb-select">
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="popular" @selected($sort === 'popular')>Most liked</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                </select>
            </div>

            <div class="md:col-span-4 flex flex-wrap gap-2">
                <button class="bb-button" type="submit">Apply</button>
                <a class="bb-button-secondary" href="{{ route('posts.index') }}">Reset</a>
            </div>
        </form>
    </section>

    @if ($featuredPosts->isNotEmpty())
        <section class="mb-10">
            <h2 class="mb-4 text-xl font-bold text-slate-900">Featured Visual Stories</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($featuredPosts as $featured)
                    <article class="bb-feature-card" data-tilt-card>
                        <div data-tilt-glare class="bb-post-glare"></div>
                        <img
                            src="{{ str_starts_with($featured->image_path, 'http') ? $featured->image_path : Storage::url($featured->image_path) }}"
                            alt="{{ $featured->title }}"
                            class="mb-4 h-40 w-full rounded-xl object-cover"
                        >

                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-cyan-200">{{ $featured->category->name }}</p>
                        <h3 class="text-lg font-bold text-white">{{ $featured->title }}</h3>
                        <p class="mt-2 text-sm text-cyan-50/85">{{ $featured->summary }}</p>
                        <div class="mt-4 flex items-center justify-between text-xs text-cyan-100/75">
                            <span>{{ $featured->likes_count }} likes</span>
                            <a href="{{ route('posts.show', $featured) }}" class="font-semibold text-lime-200">Read</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section id="latest">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Latest Question Capsules</h2>
            @auth
                <a href="{{ route('posts.create') }}" class="bb-button">Create Post</a>
            @endauth
        </div>

        @if ($posts->isEmpty())
            <div class="bb-card">
                <p class="text-slate-600">No posts match your search yet. Try another keyword or category.</p>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="bb-post-card flex flex-col" data-tilt-card>
                    <div data-tilt-glare class="bb-post-glare"></div>

                    <img
                        src="{{ str_starts_with($post->image_path, 'http') ? $post->image_path : Storage::url($post->image_path) }}"
                        alt="{{ $post->title }}"
                        class="h-44 w-full rounded-xl object-cover"
                    >

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span class="bb-chip">{{ $post->category->name }}</span>
                        @if (! $post->is_public)
                            <span class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-800">Draft</span>
                        @endif
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $post->title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $post->summary }}</p>

                    <div class="mt-5 flex items-center justify-between text-xs text-slate-500">
                        <span>By {{ $post->user->name }}</span>
                        <span>{{ $post->likes_count }} likes</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>

                        @auth
                            <form action="{{ route('posts.like', $post) }}" method="POST">
                                @csrf
                                <button type="submit" class="bb-button-secondary">
                                    {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like' }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
