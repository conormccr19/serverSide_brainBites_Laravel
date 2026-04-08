<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $postsQuery = $user->isAdmin()
            ? Post::query()
            : $user->posts();

        $posts = $postsQuery
            ->with('user')
            ->with('category')
            ->withCount(['likes', 'comments'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $statsQuery = $user->isAdmin()
            ? Post::query()
            : $user->posts();

        $stats = [
            'total_posts' => (clone $statsQuery)->count(),
            'public_posts' => (clone $statsQuery)->where('is_public', true)->count(),
            'total_likes' => (clone $statsQuery)->withCount('likes')->get()->sum('likes_count'),
            'total_comments' => (clone $statsQuery)->withCount('comments')->get()->sum('comments_count'),
        ];

        return view('dashboard', [
            'posts' => $posts,
            'stats' => $stats,
            'isAdminView' => $user->isAdmin(),
        ]);
    }
}
