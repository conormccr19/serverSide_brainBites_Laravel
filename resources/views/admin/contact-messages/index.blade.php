@extends('layouts.site')

@section('title', 'Admin | Moderation Inbox')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">Admin</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">Moderation Inbox</h1>
            <p class="mt-4 max-w-xl text-sm text-cyan-100/90 sm:text-base">
                Review contact messages, approve posts before they go public, process user reports, and ban inappropriate accounts.
            </p>
        </div>

        <div class="bb-focus-card text-cyan-50">
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Queue State</p>
            @if ($section === 'contacts')
                <p class="mt-2 text-lg font-semibold">{{ $messages->total() }} total messages</p>
            @elseif ($section === 'posts')
                <p class="mt-2 text-lg font-semibold">{{ $posts->total() }} posts in queue</p>
            @elseif ($section === 'reports')
                <p class="mt-2 text-lg font-semibold">{{ $reports->total() }} reports in queue</p>
            @else
                <p class="mt-2 text-lg font-semibold">{{ $users->total() }} users in scope</p>
            @endif
            <p class="mt-2 text-sm text-cyan-100/90">Section: {{ ucfirst($section) }}</p>
        </div>
    </section>

    <section class="bb-glass p-5 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.contact-messages.index', ['section' => 'contacts', 'filter' => 'open']) }}" class="bb-button-secondary {{ $section === 'contacts' ? 'border-cyan-500 text-cyan-700' : '' }}">Contact</a>
            <a href="{{ route('admin.contact-messages.index', ['section' => 'posts', 'filter' => 'pending']) }}" class="bb-button-secondary {{ $section === 'posts' ? 'border-cyan-500 text-cyan-700' : '' }}">Post Approvals</a>
            <a href="{{ route('admin.contact-messages.index', ['section' => 'reports', 'filter' => 'pending']) }}" class="bb-button-secondary {{ $section === 'reports' ? 'border-cyan-500 text-cyan-700' : '' }}">Reports</a>
            <a href="{{ route('admin.contact-messages.index', ['section' => 'users', 'filter' => 'active']) }}" class="bb-button-secondary {{ $section === 'users' ? 'border-cyan-500 text-cyan-700' : '' }}">Users</a>
            </div>

        @if ($section === 'contacts')
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contact-messages.index', ['section' => 'contacts', 'filter' => 'open']) }}" class="bb-button-secondary {{ $filter === 'open' ? 'border-cyan-500 text-cyan-700' : '' }}">Open</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'contacts', 'filter' => 'resolved']) }}" class="bb-button-secondary {{ $filter === 'resolved' ? 'border-cyan-500 text-cyan-700' : '' }}">Resolved</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'contacts', 'filter' => 'all']) }}" class="bb-button-secondary {{ $filter === 'all' ? 'border-cyan-500 text-cyan-700' : '' }}">All</a>
            </div>

            @if ($messages->isEmpty())
                <p class="text-sm text-slate-600">No messages for this filter.</p>
            @else
                <div class="space-y-4">
                    @foreach ($messages as $message)
                        <article class="bb-card">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-cyan-700">{{ $message->topic }}</p>
                                    <h2 class="text-lg font-bold text-slate-900">
                                        {{ $message->name }}
                                        <a
                                            href="mailto:{{ $message->email }}?subject={{ urlencode('Re: ' . $message->topic) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm font-medium text-cyan-700 underline decoration-cyan-400 underline-offset-2 hover:text-cyan-800"
                                        >
                                            ({{ $message->email }})
                                        </a>
                                    </h2>
                                </div>
                                <span class="bb-chip">{{ $message->is_resolved ? 'Resolved' : 'Open' }}</span>
                            </div>

                            <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700">{{ $message->message }}</p>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                                <span>Received: {{ $message->created_at?->format('M d, Y H:i') }}</span>
                                @if ($message->is_resolved)
                                    <span>
                                        Resolved by {{ $message->resolver?->name ?? 'Unknown' }} on {{ $message->resolved_at?->format('M d, Y H:i') }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a
                                    href="mailto:{{ $message->email }}?subject={{ urlencode('Re: ' . $message->topic) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="bb-button-secondary"
                                >
                                    Email
                                </a>

                                <form method="POST" action="{{ route('admin.contact-messages.resolve', $message) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bb-button-secondary">
                                        {{ $message->is_resolved ? 'Mark as Open' : 'Mark as Resolved' }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $messages->links() }}
                </div>
            @endif
        @elseif ($section === 'posts')
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contact-messages.index', ['section' => 'posts', 'filter' => 'pending']) }}" class="bb-button-secondary {{ $postFilter === 'pending' ? 'border-cyan-500 text-cyan-700' : '' }}">Pending</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'posts', 'filter' => 'approved']) }}" class="bb-button-secondary {{ $postFilter === 'approved' ? 'border-cyan-500 text-cyan-700' : '' }}">Approved</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'posts', 'filter' => 'rejected']) }}" class="bb-button-secondary {{ $postFilter === 'rejected' ? 'border-cyan-500 text-cyan-700' : '' }}">Rejected</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'posts', 'filter' => 'all']) }}" class="bb-button-secondary {{ $postFilter === 'all' ? 'border-cyan-500 text-cyan-700' : '' }}">All</a>
            </div>

            @if ($posts->isEmpty())
                <p class="text-sm text-slate-600">No posts in this queue.</p>
            @else
                <div class="space-y-4">
                    @foreach ($posts as $post)
                        <article class="bb-card">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-cyan-700">{{ $post->category?->name ?? 'No Category' }}</p>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $post->title }}</h2>
                                    <p class="mt-1 text-sm text-slate-600">By {{ $post->user?->name ?? 'Unknown' }} | Status: {{ ucfirst($post->approval_status) }}</p>
                                </div>
                                <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">Review</a>
                            </div>

                            <p class="mt-3 text-sm text-slate-700">{{ $post->summary }}</p>

                            @if ($post->review_notes)
                                <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    Latest review note: {{ $post->review_notes }}
                                </p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('admin.posts.approve', $post) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bb-button-secondary">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('admin.posts.reject', $post) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="text"
                                        name="review_notes"
                                        class="bb-input max-w-xs"
                                        maxlength="1000"
                                        placeholder="Optional rejection note"
                                    >
                                    <button type="submit" class="bb-button-secondary">Reject</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.ban', $post->user) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="text"
                                        name="ban_reason"
                                        class="bb-input max-w-xs"
                                        maxlength="1000"
                                        placeholder="Ban reason"
                                    >
                                    <button type="submit" class="bb-button-secondary">Ban Author</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            @endif
        @elseif ($section === 'reports')
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contact-messages.index', ['section' => 'reports', 'filter' => 'pending']) }}" class="bb-button-secondary {{ $reportFilter === 'pending' ? 'border-cyan-500 text-cyan-700' : '' }}">Pending</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'reports', 'filter' => 'actioned']) }}" class="bb-button-secondary {{ $reportFilter === 'actioned' ? 'border-cyan-500 text-cyan-700' : '' }}">Actioned</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'reports', 'filter' => 'dismissed']) }}" class="bb-button-secondary {{ $reportFilter === 'dismissed' ? 'border-cyan-500 text-cyan-700' : '' }}">Dismissed</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'reports', 'filter' => 'all']) }}" class="bb-button-secondary {{ $reportFilter === 'all' ? 'border-cyan-500 text-cyan-700' : '' }}">All</a>
            </div>

            @if ($reports->isEmpty())
                <p class="text-sm text-slate-600">No reports in this queue.</p>
            @else
                <div class="space-y-4">
                    @foreach ($reports as $report)
                        <article class="bb-card">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-cyan-700">{{ ucfirst($report->status) }} Report</p>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $report->reason }}</h2>
                                    <p class="mt-1 text-sm text-slate-600">Reporter: {{ $report->reporter?->name ?? 'Unknown' }}</p>
                                    <p class="mt-1 text-sm text-slate-600">Post: {{ $report->post?->title ?? 'Deleted post' }}</p>
                                </div>
                                @if ($report->post)
                                    <a href="{{ route('posts.show', $report->post) }}" class="bb-button-secondary">Review Post</a>
                                @endif
                            </div>

                            @if ($report->details)
                                <p class="mt-3 text-sm text-slate-700">{{ $report->details }}</p>
                            @endif

                            @if ($report->review_notes)
                                <p class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                    Admin note: {{ $report->review_notes }}
                                </p>
                            @endif

                            <div class="mt-4 text-xs text-slate-500">
                                Submitted {{ $report->created_at?->diffForHumans() }}
                                @if ($report->reviewed_at)
                                    | Reviewed by {{ $report->reviewer?->name ?? 'Unknown' }} {{ $report->reviewed_at->diffForHumans() }}
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.post-reports.review', $report) }}" class="mt-4 flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="bb-select max-w-xs" required>
                                    <option value="actioned" @selected($report->status === 'actioned')>Actioned</option>
                                    <option value="dismissed" @selected($report->status === 'dismissed')>Dismissed</option>
                                </select>
                                <input
                                    type="text"
                                    name="review_notes"
                                    class="bb-input max-w-sm"
                                    maxlength="1000"
                                    placeholder="Optional admin note"
                                >
                                <button type="submit" class="bb-button-secondary">Save Review</button>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $reports->links() }}
                </div>
            @endif
        @else
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contact-messages.index', ['section' => 'users', 'filter' => 'active']) }}" class="bb-button-secondary {{ $userFilter === 'active' ? 'border-cyan-500 text-cyan-700' : '' }}">Active</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'users', 'filter' => 'banned']) }}" class="bb-button-secondary {{ $userFilter === 'banned' ? 'border-cyan-500 text-cyan-700' : '' }}">Banned</a>
                <a href="{{ route('admin.contact-messages.index', ['section' => 'users', 'filter' => 'all']) }}" class="bb-button-secondary {{ $userFilter === 'all' ? 'border-cyan-500 text-cyan-700' : '' }}">All</a>
            </div>

            @if ($users->isEmpty())
                <p class="text-sm text-slate-600">No users found for this filter.</p>
            @else
                <div class="space-y-4">
                    @foreach ($users as $user)
                        <article class="bb-card">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $user->name }} (@{{ $user->username }})</h2>
                                    <p class="text-sm text-slate-600">{{ $user->email }}</p>
                                    @if ($user->is_banned)
                                        <p class="mt-2 text-xs text-rose-700">Banned {{ $user->banned_at?->diffForHumans() }}: {{ $user->ban_reason }}</p>
                                    @endif
                                </div>
                                <span class="bb-chip">{{ $user->is_banned ? 'Banned' : 'Active' }}</span>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    @if (! $user->is_banned)
                                        <input
                                            type="text"
                                            name="ban_reason"
                                            class="bb-input max-w-sm"
                                            maxlength="1000"
                                            placeholder="Reason for ban"
                                        >
                                    @endif
                                    <button type="submit" class="bb-button-secondary">
                                        {{ $user->is_banned ? 'Unban User' : 'Ban User' }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection
