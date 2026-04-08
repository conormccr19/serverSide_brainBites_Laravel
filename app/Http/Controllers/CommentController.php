<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->assertCanViewPost($request, $post);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Comment posted successfully.');
    }

    public function destroy(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->assertCanViewPost($request, $post);

        abort_unless($comment->post_id === $post->id, 404);
        abort_unless($request->user()->isAdmin() || $request->user()->id === $comment->user_id, 403);

        $comment->delete();

        return back()->with('status', 'Comment removed.');
    }

    private function assertCanViewPost(Request $request, Post $post): void
    {
        $isScheduledForFuture = $post->is_public
            && $post->published_at
            && $post->published_at->isFuture();

        if ((! $post->is_public || $isScheduledForFuture) && (! auth()->check() || auth()->user()->cannot('view', $post))) {
            abort(403);
        }
    }
}