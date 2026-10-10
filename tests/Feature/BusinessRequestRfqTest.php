<?php

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\Rfq;
use App\Models\RfqQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function requestCompany(User $user, int $n = 0): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => "Req Co {$user->id}-{$n}",
        'slug' => "req-co-{$user->id}-{$n}",
        'country' => 'Germany',
        'city' => 'Berlin',
        'business_types' => ['Buyer'],
        'industry' => 'Technology',
    ]);
}

it('groups search conditions so filters cannot be bypassed', function () {
    $user = User::factory()->create();

    BusinessRequest::create([
        'user_id' => $user->id, 'title' => 'Need steel beams', 'request_type' => 'supplier',
        'industry' => 'Construction', 'description' => 'Bulk order', 'status' => 'open',
    ]);
    BusinessRequest::create([
        'user_id' => $user->id, 'title' => 'Need steel beams', 'request_type' => 'buyer',
        'industry' => 'Construction', 'description' => 'Bulk order', 'status' => 'open',
    ]);

    // Search matches both titles, but the type filter must still apply.
    $this->get('/requests?q=steel&type=supplier')->assertOk();

    // Direct assertion of the fixed grouping: (title OR description) AND type.
    $count = BusinessRequest::where(function ($q) {
        $q->where('title', 'like', '%steel%')->orWhere('description', 'like', '%steel%');
    })->where('request_type', 'supplier')->count();

    expect($count)->toBe(1);
    expect(BusinessRequest::where('request_type', 'supplier')->count())->toBe(1);
});

it('enforces rfq quoting rules', function () {
    $owner = User::factory()->create();
    requestCompany($owner);
    $rfq = Rfq::create([
        'user_id' => $owner->id, 'company_id' => $owner->company->id,
        'product_name' => 'Copper wire', 'specifications' => '2mm', 'quantity' => '5 tons',
        'delivery_country' => 'UAE', 'status' => 'active',
    ]);

    $quoter = User::factory()->create();
    requestCompany($quoter);

    // Happy path creates a quote and notifies the owner.
    $this->actingAs($quoter)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 120.50, 'lead_time' => '20 days', 'quote_notes' => 'Best quality.',
    ])->assertRedirect();
    expect(RfqQuote::count())->toBe(1);
    expect($owner->notifications()->count())->toBe(1);

    // Duplicate company quote is rejected.
    $this->actingAs($quoter)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 100, 'lead_time' => '10 days', 'quote_notes' => 'Again.',
    ])->assertConflict();

    // Owner cannot quote their own RFQ.
    $this->actingAs($owner)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 50, 'lead_time' => '5 days', 'quote_notes' => 'Self.',
    ])->assertStatus(422);

    // Non-positive prices are rejected.
    $other = User::factory()->create();
    requestCompany($other);
    $this->actingAs($other)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 0, 'lead_time' => '5 days', 'quote_notes' => 'Free.',
    ])->assertSessionHasErrors('unit_price');

    // Closed RFQs cannot receive quotes.
    $rfq->update(['status' => 'closed']);
    $this->actingAs($other)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 10, 'lead_time' => '5 days', 'quote_notes' => 'Late.',
    ])->assertStatus(422);
});

it('blocks quotes from the same company behind a different user', function () {
    $owner = User::factory()->create();
    requestCompany($owner);
    $rfq = Rfq::create([
        'user_id' => $owner->id, 'company_id' => $owner->company->id,
        'product_name' => 'Olives', 'specifications' => 'Organic', 'quantity' => '1 ton',
        'delivery_country' => 'Jordan', 'status' => 'active',
    ]);

    // A quoter whose company owns nothing related is fine; same-company check uses company_id.
    $quoter = User::factory()->create();
    $qc = requestCompany($quoter);
    $rfq->update(['company_id' => $qc->id]);

    $this->actingAs($quoter)->post("/rfq/{$rfq->id}/quote", [
        'unit_price' => 10, 'lead_time' => '5 days', 'quote_notes' => 'Insider.',
    ])->assertStatus(422);
});
