<?php

namespace App\Http\Controllers;

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\Rfq;
use App\Models\RfqQuote;
use App\Models\UserBlock;
use App\Notifications\RfqQuoteReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BusinessRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:50'],
            'industry' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:open,fulfilled,closed'],
        ]);

        $search = $validated['q'] ?? null;
        $type = $validated['type'] ?? null;
        $industry = $validated['industry'] ?? null;
        $status = $validated['status'] ?? null;

        $query = BusinessRequest::with('company.user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($type) {
            $query->where('request_type', $type);
        }
        if ($industry) {
            $query->where('industry', $industry);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15);
        $rfqs = Rfq::with(['company', 'quotes'])->latest()->take(10)->get();

        return Inertia::render('Requests/Index', [
            'requests' => $requests,
            'rfqs' => $rfqs,
            'filters' => compact('search', 'type', 'industry'),
        ]);
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'request_type' => ['required', 'string', Rule::in(['supplier', 'manufacturer', 'distributor', 'buyer', 'partner'])],
            'industry' => 'required|string|max:100',
            'target_country' => 'nullable|string|max:100',
            'quantity' => 'nullable|string|max:100',
            'description' => 'required|string|max:10000',
            'budget_range' => 'nullable|string|max:100',
        ]);

        $userCompany = Company::where('user_id', auth()->id())->first();

        BusinessRequest::create([
            'user_id' => auth()->id(),
            'company_id' => $userCompany?->id,
            'title' => $validated['title'],
            'request_type' => $validated['request_type'],
            'industry' => $validated['industry'],
            'target_country' => $validated['target_country'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'description' => $validated['description'],
            'budget_range' => $validated['budget_range'] ?? null,
        ]);

        return redirect()->back()->with('success', __('flash.request_posted'));
    }

    public function storeQuote(Request $request, $rfqId)
    {
        $validated = $request->validate([
            'unit_price' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => ['nullable', 'string', 'size:3', 'in:USD,EUR,GBP,AED,SAR,TRY,CNY,JOD,EGP,QAR,KWD'],
            'lead_time' => 'required|string|max:255',
            'quote_notes' => 'required|string|max:5000',
        ]);

        $userCompany = Company::where('user_id', auth()->id())->first();

        if (! $userCompany) {
            return redirect()->back()->withErrors(['company' => 'You must create a company profile first to send quotations.']);
        }

        $rfq = Rfq::with(['user', 'company'])->findOrFail($rfqId);

        abort_if($rfq->status !== 'active', 422, 'This RFQ is no longer accepting quotations.');
        abort_if($rfq->user_id === auth()->id(), 422, 'You cannot quote your own RFQ.');
        abort_if($rfq->company_id && (int) $rfq->company_id === (int) $userCompany->id, 422, 'Your company owns this RFQ.');
        abort_if($rfq->quotes()->where('company_id', $userCompany->id)->exists(), 409, 'Your company has already quoted this RFQ.');
        $this->abortIfBlocked($rfq->user_id);

        $quote = DB::transaction(function () use ($rfq, $userCompany, $validated) {
            return RfqQuote::firstOrCreate(
                ['rfq_id' => $rfq->id, 'company_id' => $userCompany->id],
                [
                    'user_id' => auth()->id(),
                    'unit_price' => $validated['unit_price'],
                    'currency' => $validated['currency'] ?? 'USD',
                    'lead_time' => $validated['lead_time'],
                    'quote_notes' => $validated['quote_notes'],
                ]
            );
        });

        $rfq->user->notify(new RfqQuoteReceived($quote->load('company')));

        return redirect()->back()->with('success', __('flash.quote_submitted'));
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
