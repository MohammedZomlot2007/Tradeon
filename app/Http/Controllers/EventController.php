<?php

namespace App\Http\Controllers;

use App\Models\BizEvent;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = BizEvent::with('company')->orderBy('start_date', 'asc')->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }
}
