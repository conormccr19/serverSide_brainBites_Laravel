<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        if ($request->user()->is($user)) {
            return back()->with('status', 'You cannot follow yourself.');
        }

        $isFollowing = $request->user()
            ->followingUsers()
            ->whereKey($user->id)
            ->exists();

        if ($isFollowing) {
            $request->user()->followingUsers()->detach($user->id);

            return back()->with('status', 'User removed from following list.');
        }

        $request->user()->followingUsers()->attach($user->id);

        return back()->with('status', 'You are now following '.$user->name.'.');
    }
}
