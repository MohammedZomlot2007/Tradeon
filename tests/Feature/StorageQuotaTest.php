<?php

use App\Http\Middleware\EnsureStorageQuota;
use App\Models\Company;
use App\Models\MediaUpload;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\StorageQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function quotaCompany(User $user): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => 'Quota Co '.$user->id,
        'slug' => 'quota-co-'.$user->id,
        'country' => 'Palestine',
        'city' => 'Ramallah',
        'business_types' => ['Manufacturer'],
        'industry' => 'Technology',
    ]);
}

function quotaImage(int $kilobytes = 100): UploadedFile
{
    // Note: ->image() needs the GD extension, so fake a JPEG by mime.
    return UploadedFile::fake()->create('photo.jpg', $kilobytes, 'image/jpeg');
}

it('grants 30 MB of free storage to users without a subscription', function () {
    $user = User::factory()->create();
    $service = app(StorageQuotaService::class);

    expect($service->quotaBytes($user))->toBe(30 * 1024 * 1024)
        ->and($service->usedBytes(null))->toBe(0)
        ->and($service->canStore($user, 1024))->toBeTrue()
        ->and($service->canStore($user, 31 * 1024 * 1024))->toBeFalse();
});

it('treats plans with storage_mb -1 as unlimited', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    SubscriptionPlan::where('slug', 'pro')->update(['limits' => ['storage_mb' => -1]]);

    expect(app(StorageQuotaService::class)->quotaBytes($user))->toBeNull();
});

it('creates a free subscription automatically at registration', function () {
    $this->post('/register', [
        'name' => 'Quota User',
        'email' => 'quota@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'business_types' => ['Buyer'],
        'industry' => 'Technology',
    ])->assertRedirect(route('onboarding.company', absolute: false));

    $user = User::where('email', 'quota@example.com')->first();
    expect($user)->not->toBeNull();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'status' => 'active',
    ]);
    expect($user->subscriptions()->where('status', 'active')->first()->plan->slug)->toBe('free');
    expect(app(StorageQuotaService::class)->quotaBytes($user))->toBe(30 * 1024 * 1024);
});

it('uploads company media under the quota and tracks the bytes', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    $response = $this->actingAs($user)->postJson('/media', ['file' => quotaImage(200)]);
    $response->assertCreated();
    $url = $response->json('url');
    expect($url)->toContain('/storage/media/'.$company->id);

    $this->assertDatabaseHas('media_uploads', ['company_id' => $company->id, 'disk' => 'public']);
    Storage::disk('public')->assertExists(MediaUpload::first()->path);
    expect(app(StorageQuotaService::class)->usedBytes($company))->toBeGreaterThan(0);
});

it('redirects an over-quota web upload to the pricing page', function () {
    Storage::fake('public');
    config(['billing.free_storage_mb' => 1]);
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    MediaUpload::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/old.jpg',
        'bytes' => 2 * 1024 * 1024,
        'mime' => 'image/jpeg',
    ]);

    $this->actingAs($user)->post('/media', ['file' => quotaImage(200)])
        ->assertRedirect('/pricing')
        ->assertSessionHas('error', EnsureStorageQuota::FLASH_MESSAGE);

    expect(MediaUpload::count())->toBe(1);
});

it('returns 402 with an upgrade url for over-quota api uploads', function () {
    Storage::fake('public');
    config(['billing.free_storage_mb' => 1]);
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    MediaUpload::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/old.jpg',
        'bytes' => 2 * 1024 * 1024,
        'mime' => 'image/jpeg',
    ]);

    $this->actingAs($user)->postJson('/media', ['file' => quotaImage(200)])
        ->assertStatus(402)
        ->assertJson([
            'message' => EnsureStorageQuota::JSON_MESSAGE,
            'upgrade_url' => route('billing.pricing'),
        ]);
});

it('redirects over-quota post publishing to the pricing page', function () {
    config(['billing.free_storage_mb' => 1]);
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    MediaUpload::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/old.jpg',
        'bytes' => 2 * 1024 * 1024,
        'mime' => 'image/jpeg',
    ]);

    $this->actingAs($user)->post('/posts', [
        'type' => 'business_update',
        'content' => 'Trying to publish while over quota',
    ])->assertRedirect('/pricing');
});

it('counts verification documents against the quota', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    $this->actingAs($user)->post('/verification', [
        'verification_type' => 'company',
        'document_type' => 'License',
        'document' => UploadedFile::fake()->create('license.pdf', 500, 'application/pdf'),
    ])->assertRedirect();

    $this->assertDatabaseHas('media_uploads', ['company_id' => $company->id, 'disk' => 'local']);
    expect(app(StorageQuotaService::class)->usedBytes($company))->toBeGreaterThanOrEqual(500 * 1024);
});

it('stores post images on our disk and counts them', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);

    $this->actingAs($user)->post('/posts', [
        'type' => 'business_update',
        'content' => 'Post with a real image upload',
        'media' => [quotaImage(150)],
    ])->assertRedirect();

    $post = $company->posts()->first();
    expect($post)->not->toBeNull()
        ->and($post->media_urls)->toHaveCount(1)
        ->and($post->media_urls[0])->toContain('/storage/media/');
    expect(MediaUpload::where('company_id', $company->id)->count())->toBe(1);
});

it('frees the quota when a media file is deleted', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = quotaCompany($user);
    $service = app(StorageQuotaService::class);

    $this->actingAs($user)->postJson('/media', ['file' => quotaImage(200)])->assertCreated();
    expect($service->usedBytes($company))->toBeGreaterThan(0);

    $upload = MediaUpload::first();
    $this->actingAs($user)->deleteJson("/media/{$upload->id}")->assertOk();

    expect(MediaUpload::count())->toBe(0)
        ->and($service->usedBytes($company))->toBe(0);
});

it('shows the storage usage on the billing page', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    quotaCompany($user);

    $this->actingAs($user)->get('/billing')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('storage')
            ->where('storage.has_company', true)
            ->where('storage.unlimited', false)
        );
});
