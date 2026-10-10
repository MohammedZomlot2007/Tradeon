<?php

use App\Models\Company;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('notifies authors on likes and comments and serves the notifications page', function () {
    $author = User::factory()->create();
    $fan = User::factory()->create();
    giveActiveSubscription($fan);
    giveActiveSubscription($author);
    $post = Post::create(['user_id' => $author->id, 'type' => 'business_update', 'content' => 'Notify me']);

    $this->actingAs($fan)->post("/posts/{$post->id}/like")->assertRedirect();
    $this->actingAs($fan)->post("/posts/{$post->id}/comment", ['comment' => 'Great update!'])->assertRedirect();

    expect($author->notifications()->count())->toBe(2);

    $this->actingAs($author)->get('/notifications')->assertOk();

    $notification = $author->notifications()->first();
    $this->actingAs($author)->post("/notifications/{$notification->id}/read")->assertOk();
    expect($author->unreadNotifications()->count())->toBe(1);

    $this->actingAs($author)->post('/notifications/read-all')->assertOk();
    expect($author->unreadNotifications()->count())->toBe(0);
});

it('restricts the admin area and protects administrators', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin-one']);

    $this->actingAs($user)->get('/admin')->assertForbidden();
    $this->actingAs($admin)->get('/admin')->assertOk();

    // Admins cannot suspend themselves.
    $this->actingAs($admin)->post("/admin/users/{$admin->id}/suspension")->assertForbidden();

    // Suspending and restoring a regular user notifies them.
    $this->actingAs($admin)->post("/admin/users/{$user->id}/suspension")->assertRedirect();
    expect($user->fresh()->status)->toBe('suspended');
    expect($user->notifications()->count())->toBe(1);

    $this->actingAs($admin)->post("/admin/users/{$user->id}/suspension")->assertRedirect();
    expect($user->fresh()->status)->toBe('active');
});

it('moderates reports through the admin workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $reporter = User::factory()->create();
    $report = Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => 'post',
        'reportable_id' => 999,
        'reason' => 'spam',
        'details' => 'Looks like spam.',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->post("/admin/reports/{$report->id}", ['status' => 'resolved'])->assertRedirect();
    expect($report->fresh()->status)->toBe('resolved');

    $this->actingAs($reporter)->post("/admin/reports/{$report->id}", ['status' => 'resolved'])->assertForbidden();
});

it('supports email-only login and rejects usernames', function () {
    $user = User::factory()->create(['username' => 'tradehero']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
    $this->assertAuthenticated();
    $this->post('/logout');

    // A username is not a valid login identifier.
    $this->post('/login', ['email' => 'tradehero', 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
});

it('counts company profile views once per session window', function () {
    $owner = User::factory()->create();
    $company = Company::create([
        'user_id' => $owner->id, 'name' => 'Viewed Co', 'slug' => 'viewed-co',
        'country' => 'Palestine', 'city' => 'Gaza', 'business_types' => ['Retailer'], 'industry' => 'Other',
    ]);
    $viewer = User::factory()->create();

    $this->actingAs($viewer)->get('/company/viewed-co')->assertOk();
    $this->actingAs($viewer)->get('/company/viewed-co')->assertOk();

    expect($company->fresh()->profile_views)->toBe(1);
});
