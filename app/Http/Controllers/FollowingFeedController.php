<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowingFeedController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_if($request->user()->isAdmin(), 403);

        $sort = trim((string) $request->string('sort', 'newest'));

        $followingIds = $request->user()->followingUsers()->pluck('users.id');

        $postsQuery = Post::query()
            ->public()
            ->with(['user', 'category', 'likes', 'bookmarks'])
            ->withCount(['likes', 'comments'])
            ->whereIn('user_id', $followingIds->all());

        if ($sort === 'popular') {
            $postsQuery->orderByDesc('likes_count')->orderByDesc('published_at');
        } elseif ($sort === 'oldest') {
            $postsQuery->orderBy('published_at')->orderBy('created_at');
        } else {
            $postsQuery->orderByDesc('published_at')->orderByDesc('created_at');
        }

        $posts = $postsQuery->paginate(12)->withQueryString();

        $followedUsers = $request->user()->followingUsers()
            ->withCount([
                'posts as public_posts_count' => fn ($query) => $query->public(),
                'followerUsers as followers_count',
            ])
            ->orderBy('name')
            ->get();

        $suggestedUsers = User::query()
            ->where('role', '!=', 'admin')
            ->whereKeyNot($request->user()->id)
            ->whereNotIn('id', $followingIds->all())
            ->withCount([
                'posts as public_posts_count' => fn ($query) => $query->public(),
                'followerUsers as followers_count',
            ])
            ->orderByDesc('public_posts_count')
            ->orderByDesc('followers_count')
            ->take(8)
            ->get();

        return view('posts.following', [
            'posts' => $posts,
            'followedUsers' => $followedUsers,
            'suggestedUsers' => $suggestedUsers,
            'sort' => $sort,
        ]);
    }
}
