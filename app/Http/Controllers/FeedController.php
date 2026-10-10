<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Connection;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostRepost;
use App\Models\SavedItem;
use App\Models\UserBlock;
use App\Notifications\PostCommented;
use App\Notifications\PostLiked;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all'); // all, following, companies, rfq
        $search = $request->query('q');

        $query = Post::with(['company', 'user', 'likes', 'comments.company', 'comments.user']);

        if (auth()->check()) {
            $blockedUserIds = UserBlock::where('user_id', auth()->id())->pluck('blocked_user_id');
            if ($blockedUserIds->isNotEmpty()) {
                $query->whereNotIn('user_id', $blockedUserIds);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($filter === 'rfq') {
            $query->whereIn('type', ['rfq_sourcing', 'looking_for_supplier', 'looking_for_manufacturer', 'looking_for_distributor']);
        } elseif ($filter === 'companies') {
            $query->whereNotNull('company_id');
        } elseif ($filter === 'following' && auth()->check()) {
            $followedCompanyIds = Connection::where('user_id', auth()->id())
                ->where('type', 'follow')
                ->whereNotNull('target_company_id')
                ->pluck('target_company_id');
            $connectedUserIds = Connection::where('user_id', auth()->id())
                ->where('type', 'connection')
                ->whereNotNull('target_user_id')
                ->pluck('target_user_id');
            $query->where(function ($q) use ($followedCompanyIds, $connectedUserIds) {
                $q->whereIn('company_id', $followedCompanyIds->all() ?: [0])
                    ->orWhereIn('user_id', $connectedUserIds->all() ?: [0]);
            });
        }

        $posts = $query->latest()->paginate(15);

        if (auth()->check() && $posts->isNotEmpty()) {
            $postIds = $posts->getCollection()->pluck('id');
            $likedIds = PostLike::where('user_id', auth()->id())->whereIn('post_id', $postIds)->pluck('post_id')->flip();
            $savedIds = SavedItem::where('user_id', auth()->id())->where('item_type', 'post')->whereIn('item_id', $postIds)->pluck('item_id')->flip();
            $repostedIds = PostRepost::where('user_id', auth()->id())->whereIn('post_id', $postIds)->pluck('post_id')->flip();

            $posts->getCollection()->each(function ($post) use ($likedIds, $savedIds, $repostedIds) {
                $post->setAttribute('viewer_liked', $likedIds->has($post->id));
                $post->setAttribute('viewer_saved', $savedIds->has($post->id));
                $post->setAttribute('viewer_reposted', $repostedIds->has($post->id));
            });
        }

        // Standard user company
        $userCompany = auth()->check() ? Company::where('user_id', auth()->id())->first() : null;

        // Recommendations must come from persisted business data. Never hard-code demo companies in the UI.
        $recommendedManufacturers = Company::query()
            ->whereJsonContains('business_types', 'Manufacturer')
            ->orderByDesc('verified_tier')
            ->orderByDesc('followers_count')
            ->limit(3)
            ->get([
                'id', 'user_id', 'name', 'slug', 'logo', 'city', 'country', 'industry', 'verified_tier',
            ]);

        return Inertia::render('Feed', [
            'posts' => $posts,
            'filters' => [
                'currentFilter' => $filter,
                'search' => $search,
            ],
            'userCompany' => $userCompany,
            'recommendedManufacturers' => $recommendedManufacturers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:10000',
            'country' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:150',
            'media_urls' => ['nullable', 'array', 'max:10'],
            'media_urls.*' => ['string', 'url', 'max:2048'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'tags' => ['nullable', 'array', 'max:30'],
            'tags.*' => ['string', 'max:80'],
        ]);

        $userCompany = Company::where('user_id', auth()->id())->first();

        // Real image uploads are stored on our disk and count against the
        // company storage quota (already enforced by `storage.quota`).
        $storedMediaUrls = [];
        foreach ($validated['media'] ?? [] as $file) {
            $path = $file->storeAs(
                'media/'.($userCompany?->id ?? 'users/'.auth()->id()),
                Str::uuid().'.'.$file->extension(),
                'public'
            );

            if ($userCompany) {
                app(StorageQuotaService::class)->recordUpload(
                    $userCompany,
                    $request->user(),
                    $path,
                    'public',
                    (int) $file->getSize(),
                    $file->getMimeType()
                );
            }

            $storedMediaUrls[] = Storage::disk('public')->url($path);
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'company_id' => $userCompany?->id,
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'country' => $validated['country'] ?? $userCompany?->country,
            'industry' => $validated['industry'] ?? $userCompany?->industry,
            'media_urls' => array_merge($validated['media_urls'] ?? [], $storedMediaUrls),
            'tags' => $validated['tags'] ?? [],
        ]);

        return redirect()->back()->with('success', __('flash.post_created'));
    }

    public function like($id)
    {
        $post = Post::findOrFail($id);
        $this->abortIfBlocked($post->user_id);

        $liked = DB::transaction(function () use ($post) {
            $like = PostLike::where('user_id', auth()->id())->where('post_id', $post->id)->first();

            if ($like) {
                $like->delete();
                Post::whereKey($post->id)->where('likes_count', '>', 0)->decrement('likes_count');

                return false;
            }

            PostLike::firstOrCreate([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ]);
            $post->increment('likes_count');

            return true;
        });

        if ($liked && $post->user_id !== auth()->id()) {
            $post->user->notify(new PostLiked($post->fresh(), auth()->user()));
        }

        AuditLog::create(['user_id' => auth()->id(), 'action' => 'post.like.toggled', 'auditable_type' => Post::class, 'auditable_id' => $post->id, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return redirect()->back();
    }

    public function comment(Request $request, $id)
    {
        $request->validate(['comment' => 'required|string|max:2000']);

        $post = Post::findOrFail($id);
        $this->abortIfBlocked($post->user_id);

        $userCompany = Company::where('user_id', auth()->id())->first();

        DB::transaction(function () use ($post, $userCompany, $request) {
            PostComment::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'company_id' => $userCompany?->id,
                'comment' => $request->comment,
            ]);
            $post->increment('comments_count');
        });

        if ($post->user_id !== auth()->id()) {
            $post->user->notify(new PostCommented($post, auth()->user()));
        }

        return redirect()->back();
    }

    private function abortIfBlocked(int $otherUserId): void
    {
        $blocked = UserBlock::where(function ($query) use ($otherUserId) {
            $query->where('user_id', auth()->id())->where('blocked_user_id', $otherUserId);
        })->orWhere(function ($query) use ($otherUserId) {
            $query->where('user_id', $otherUserId)->where('blocked_user_id', auth()->id());
        })->exists();

        abort_if($blocked, 403, 'This interaction is not available.');
    }
}
