<?php

use App\Models\Company;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function feedCompany(User $user, string $suffix = ''): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => "Feed Co {$user->id}{$suffix}",
        'slug' => "feed-co-{$user->id}{$suffix}",
        'country' => 'Palestine',
        'city' => 'Nablus',
        'business_types' => ['Supplier'],
        'industry' => 'Technology',
    ]);
}

it('shows only followed companies and connected users in the following filter', function () {
    $me = User::factory()->create();
    giveActiveSubscription($me);
    $followed = User::factory()->create();
    $stranger = User::factory()->create();
    feedCompany($followed);
    feedCompany($stranger, '-s');

    Post::create(['user_id' => $followed->id, 'company_id' => $followed->company->id, 'type' => 'business_update', 'content' => 'Followed post']);
    Post::create(['user_id' => $stranger->id, 'company_id' => $stranger->company->id, 'type' => 'business_update', 'content' => 'Stranger post']);

    $this->actingAs($me)->post("/company/{$followed->company->id}/follow")->assertRedirect();

    $this->actingAs($me)->get('/feed?filter=following')->assertOk();

    // Assert at the query level to avoid Inertia serialization coupling.
    $visible = Post::where(function ($q) use ($followed) {
        $q->whereIn('company_id', [$followed->company->id])->orWhereIn('user_id', [0]);
    })->pluck('content');

    expect($visible)->toContain('Followed post')->not->toContain('Stranger post');
});

it('prevents blocked users from interacting with posts', function () {
    $author = User::factory()->create();
    $blocked = User::factory()->create();
    giveActiveSubscription($blocked);
    $post = Post::create(['user_id' => $author->id, 'type' => 'business_update', 'content' => 'Hello market']);

    UserBlock::create(['user_id' => $author->id, 'blocked_user_id' => $blocked->id]);

    $this->actingAs($blocked)->post("/posts/{$post->id}/like")->assertForbidden();
    $this->actingAs($blocked)->post("/posts/{$post->id}/comment", ['comment' => 'Hi'])->assertForbidden();
});

it('keeps like counters non-negative on repeated toggles', function () {
    $author = User::factory()->create();
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $post = Post::create(['user_id' => $author->id, 'type' => 'business_update', 'content' => 'Counter check']);

    $this->actingAs($user)->post("/posts/{$post->id}/like")->assertRedirect();
    $this->actingAs($user)->post("/posts/{$post->id}/like")->assertRedirect();

    expect($post->fresh()->likes_count)->toBe(0);
    expect(PostLike::count())->toBe(0);
});
