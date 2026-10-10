<?php

use App\Models\Company;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function verificationCompany(User $user): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => 'Test Company '.$user->id,
        'slug' => 'test-company-'.$user->id,
        'country' => 'Palestine',
        'city' => 'Ramallah',
        'business_types' => ['Manufacturer'],
        'industry' => 'Technology',
    ]);
}

it('requires a company before submitting verification', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);

    $this->actingAs($user)->post('/verification', [
        'verification_type' => 'company',
        'document_type' => 'License',
        'document' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ])->assertNotFound();
});

it('accepts a valid verification document into private storage', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    verificationCompany($user);

    $this->actingAs($user)->post('/verification', [
        'verification_type' => 'company',
        'document_type' => 'Commercial license',
        'document' => UploadedFile::fake()->create('license.pdf', 500, 'application/pdf'),
    ])->assertRedirect();

    $verification = Verification::first();
    expect($verification->status)->toBe('pending');
    Storage::disk('local')->assertExists($verification->document_url);
});

it('rejects executable uploads', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    verificationCompany($user);

    $this->actingAs($user)->post('/verification', [
        'verification_type' => 'company',
        'document_type' => 'Evil',
        'document' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
    ])->assertSessionHasErrors('document');

    expect(Verification::count())->toBe(0);
});

it('blocks duplicate pending verifications of the same type', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = verificationCompany($user);
    Verification::create([
        'company_id' => $company->id,
        'verification_type' => 'company',
        'document_type' => 'License',
        'document_url' => 'verifications/1/old.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->post('/verification', [
        'verification_type' => 'company',
        'document_type' => 'License 2',
        'document' => UploadedFile::fake()->create('license2.pdf', 100, 'application/pdf'),
    ])->assertConflict();
});

it('lets the owner download their document but blocks strangers', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = verificationCompany($user);
    Storage::disk('local')->put('verifications/1/secret.pdf', 'secret-bytes');
    $verification = Verification::create([
        'company_id' => $company->id,
        'verification_type' => 'company',
        'document_type' => 'License',
        'document_url' => 'verifications/1/secret.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->get("/verification/{$verification->id}/document")->assertOk();

    $stranger = User::factory()->create();
    giveActiveSubscription($stranger);
    $this->actingAs($stranger)->get("/verification/{$verification->id}/document")->assertForbidden();
});

it('approves verifications with notes and notifies the owner', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $company = verificationCompany($user);
    $verification = Verification::create([
        'company_id' => $company->id,
        'verification_type' => 'factory',
        'document_type' => 'ISO',
        'document_url' => 'verifications/1/iso.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->post("/admin/verifications/{$verification->id}/approve", ['admin_notes' => 'Looks good'])
        ->assertRedirect();

    expect($verification->fresh()->status)->toBe('approved')
        ->and($verification->fresh()->admin_notes)->toBe('Looks good')
        ->and($company->fresh()->verified_tier)->toBe('factory_verified')
        ->and($user->notifications()->count())->toBe(1);
});
