@extends('layouts.site')

@section('title', 'BrainBites | Your Learned Terms')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">Personal Glossary</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">Your Learned Terms</h1>
            <p class="mt-4 max-w-2xl text-sm text-cyan-100/90 sm:text-base">
                Terms you clicked while reading are saved here so revision is faster.
            </p>
        </div>

        <div class="bb-focus-card text-cyan-50">
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">How It Works</p>
            <p class="mt-2 text-sm text-cyan-100/90">Click highlighted glossary terms in any post body to add them automatically.</p>
        </div>
    </section>

    <section class="bb-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Saved Terms</h2>
            <button type="button" class="bb-button-secondary" id="clearLearnedTerms">Clear all</button>
        </div>
        <div id="learnedTermsList" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div>
        <p id="learnedTermsEmpty" class="text-sm text-slate-600">No terms saved yet. Open any post and click glossary words to build your list.</p>
    </section>
@endsection
