<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        abort_if($request->user()->isAdmin(), 403);

        $pinnablePosts = Post::query()
            ->where('user_id', $request->user()->id)
            ->where('is_public', true)
            ->latest('published_at')
            ->latest('created_at')
            ->take(24)
            ->get(['id', 'title', 'published_at', 'created_at']);

        $pinnedPostIds = $request->user()
            ->pinnedPosts()
            ->pluck('posts.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        return view('profile.edit', [
            'user' => $request->user(),
            'pinnablePosts' => $pinnablePosts,
            'pinnedPostIds' => $pinnedPostIds,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $user = $request->user();
        $data = $request->validated();

        unset($data['profile_photo'], $data['cover_image'], $data['pinned_posts'], $data['topic_badges']);

        $socialLinks = collect($request->input('social_links', []))
            ->only(['website', 'x', 'github', 'linkedin', 'youtube'])
            ->map(fn (?string $value): ?string => filled($value) ? trim($value) : null)
            ->filter();

        $topicBadges = collect(explode(',', (string) $request->input('topic_badges', '')))
            ->map(fn (string $badge): string => trim($badge))
            ->filter()
            ->map(fn (string $badge): string => mb_substr($badge, 0, 30))
            ->unique()
            ->values()
            ->take(8)
            ->all();

        $data['social_links'] = $socialLinks->isNotEmpty() ? $socialLinks->all() : null;
        $data['topic_badges'] = ! empty($topicBadges) ? $topicBadges : null;

        $user->fill($data);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($user->cover_image_path) {
                Storage::disk('public')->delete($user->cover_image_path);
            }

            $user->cover_image_path = $request->file('cover_image')->store('profile-covers', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $pinnedIds = collect($request->input('pinned_posts', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->take(3)
            ->values();

        $syncPayload = $pinnedIds
            ->mapWithKeys(fn (int $id, int $index): array => [$id => ['position' => $index + 1]])
            ->all();

        $user->pinnedPosts()->sync($syncPayload);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
