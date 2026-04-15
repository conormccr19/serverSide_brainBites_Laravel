<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->can('view', $post), 403);

        if ($request->user()->isAdmin()) {
            return back()->with('status', 'Admin accounts cannot submit reports.');
        }

        if ($post->user_id === $request->user()->id) {
            return back()->with('status', 'You cannot report your own post.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $report = PostReport::query()
            ->where('post_id', $post->id)
            ->where('reporter_id', $request->user()->id)
            ->first();

        if ($report && $report->status === 'pending') {
            return back()->with('status', 'You already reported this post. Our team will review it soon.');
        }

        if ($report) {
            $report->update([
                'reason' => trim((string) $data['reason']),
                'details' => filled($data['details'] ?? null) ? trim((string) $data['details']) : null,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
            ]);

            return back()->with('status', 'Your report was re-opened and sent to admins.');
        }

        PostReport::create([
            'post_id' => $post->id,
            'reporter_id' => $request->user()->id,
            'reason' => trim((string) $data['reason']),
            'details' => filled($data['details'] ?? null) ? trim((string) $data['details']) : null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Report submitted. Thank you for helping keep the community safe.');
    }
}
