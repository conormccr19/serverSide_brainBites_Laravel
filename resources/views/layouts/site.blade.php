<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'BrainBites'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|instrument-serif:400" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="bb-atmosphere" aria-hidden="true"></div>

        <header class="bb-nav">
            <div class="bb-shell flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-[0_0_16px_rgba(72,242,255,0.9)]"></span>
                    Brain<span class="text-cyan-700">Bites</span>
                </a>

                <nav class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <a href="{{ route('posts.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Explore</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Dashboard</a>
                        <a href="{{ route('posts.create') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Add Post</a>
                        <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bb-button">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Log in</a>
                        <a href="{{ route('register') }}" class="bb-button">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="bb-shell py-8 sm:py-10 lg:py-12">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        <button type="button" class="bb-brainbot-toggle" id="brainbotToggle" aria-controls="brainbotPanel" aria-expanded="false">
            brainBot
        </button>

        <section class="bb-brainbot" id="brainbotPanel" aria-live="polite" hidden>
            <header class="bb-brainbot-header">
                <h2>brainBot</h2>
                <p>Web-aware learning assistant</p>
            </header>

            <div class="bb-brainbot-messages" id="brainbotMessages">
                <article class="bb-brainbot-message bot">
                    Ask me anything. I can search the web and summarize answers.
                </article>
            </div>

            <form class="bb-brainbot-form" id="brainbotForm">
                <label for="brainbotInput" class="sr-only">Ask brainBot</label>
                <input id="brainbotInput" type="text" maxlength="500" placeholder="Ask a question..." required>
                <button type="submit">Send</button>
            </form>
        </section>
    </body>
</html>
