<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\Report;
use App\Models\SavedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PostInteractionController extends Controller
{
    public function save(Post $post)
    {
        $removed = DB::transaction(function () use ($post) {
            $saved = SavedItem::where('user_id', auth()->id())
                ->where('item_type', 'post')
                ->where('item_id', $post->id)
                ->first();

            if ($saved) {
                $saved->delete();

                return true;
            }

            SavedItem::firstOrCreate([
                'user_id' => auth()->id(),
                'item_type' => 'post',
                'item_id' => $post->id,
            ], ['collection_name' => 'Default']);

            return false;
        });

        return back()->with('success', $removed ? __('flash.post_unsaved') : __('flash.post_saved'));
    }

    public function repost(Post $post)
    {
        $removed = DB::transaction(function () use ($post) {
            $repost = PostRepost::where('user_id', auth()->id())
                ->where('post_id', $post->id)
                ->first();

            if ($repost) {
                $repost->delete();
                Post::whereKey($post->id)->where('reposts_count', '>', 0)->decrement('reposts_count');

                return true;
            }

            PostRepost::firstOrCreate([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ]);
            $post->increment('reposts_count');

            return false;
        });

        return back()->with('success', $removed ? __('flash.repost_removed') : __('flash.reposted'));
    }

    public function report(Request $request, Post $post)
    {
        Gate::authorize('report', $post);
        $validated = $request->validate([
            'reason' => 'required|string|max:100',
            'details' => 'nullable|string|max:2000',
        ]);

        Report::firstOrCreate(
            [
                'reporter_id' => auth()->id(),
                'reportable_type' => 'post',
                'reportable_id' => $post->id,
                'status' => 'pending',
            ],
            [
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
            ]
        );

        return back()->with('success', __('flash.report_submitted'));
    }
}
