<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\SavedItem;
use Inertia\Inertia;
use Inertia\Response;

class NetworkController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();

        $connections = Connection::where('user_id', $userId)
            ->where('type', 'connection')
            ->with(['targetCompany', 'targetUser'])
            ->get();

        $following = Connection::where('user_id', $userId)
            ->where('type', 'follow')
            ->with('targetCompany')
            ->get();

        $savedItems = SavedItem::where('user_id', $userId)
            ->where('item_type', 'company')
            ->with('company')
            ->latest()
            ->get();

        return Inertia::render('Network/Index', [
            'connections' => $connections,
            'following' => $following,
            'savedItems' => $savedItems,
        ]);
    }
}
