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
            'parent_comment_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parentComment = null;

        if (! empty($data['parent_comment_id'])) {
            $parentComment = Comment::query()
                ->whereKey($data['parent_comment_id'])
                ->where('post_id', $post->id)
                ->firstOrFail();
        }

        Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'body' => $data['body'],
            'parent_comment_id' => $parentComment?->id,
        ]);

        return back()->with('status', 'Comment posted successfully.');
    }

    public function destroy(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->assertCanViewPost($request, $post);

        abort_unless($comment->post_id === $post->id, 404);
        abort_unless($request->user()->isAdmin() || $request->user()->id === $comment->user_id, 403);

        $this->deleteCommentTree($comment);

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

    private function deleteCommentTree(Comment $comment): void
    {
        Comment::query()
            ->where('parent_comment_id', $comment->id)
            ->get()
            ->each(function (Comment $reply): void {
                $this->deleteCommentTree($reply);
            });

        $comment->delete();
    }
}