<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $posts = $user->posts()
            ->with('category')
            ->withCount('likes')
            ->orderByDesc('created_at')
            ->paginate(10);

        $stats = [
            'total_posts' => $user->posts()->count(),
            'public_posts' => $user->posts()->where('is_public', true)->count(),
            'total_likes' => $user->posts()->withCount('likes')->get()->sum('likes_count'),
        ];

        return view('dashboard', [
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }
}
