@php
    $indentClass = match ($depth ?? 0) {
        0 => 'ml-0',
        1 => 'ml-6',
        2 => 'ml-12',
        default => 'ml-12',
    };
@endphp

<article class="{{ $indentClass }} rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}" class="h-10 w-10 rounded-full border border-slate-200 object-cover">
            <div>
                <p class="font-semibold text-slate-900">{{ $comment->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $comment->created_at?->diffForHumans() }}</p>
            </div>
        </div>

        @auth
            @if (auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                <form action="{{ route('comments.destroy', [$post, $comment]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">Delete</button>
                </form>
            @endif
        @endauth
    </div>

    <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700">{{ $comment->body }}</p>

    @auth
        <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <summary class="cursor-pointer text-sm font-semibold text-cyan-700">Reply</summary>
            <form action="{{ route('comments.store', $post) }}" method="POST" class="mt-3 grid gap-3">
                @csrf
                <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
                <div>
                    <label class="sr-only" for="replyBody-{{ $comment->id }}">Reply to {{ $comment->user->name }}</label>
                    <textarea id="replyBody-{{ $comment->id }}" name="body" rows="3" class="bb-textarea" maxlength="1000" required placeholder="Write a reply to {{ $comment->user->name }}...">{{ old('body') }}</textarea>
                    @error('body')<p class="bb-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <button type="submit" class="bb-button">Post Reply</button>
                </div>
            </form>
        </details>
    @endauth

    @if ($comment->replies->isNotEmpty())
        <div class="mt-4 space-y-4 border-l-2 border-slate-200 pl-4">
            @foreach ($comment->replies->sortBy('created_at') as $reply)
                @include('posts.partials.comment', ['post' => $post, 'comment' => $reply, 'depth' => ($depth ?? 0) + 1])
            @endforeach
        </div>
    @endif
</article>