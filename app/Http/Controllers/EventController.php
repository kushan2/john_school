<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Which month are we viewing? (?month=YYYY-MM, defaults to current)
        try {
            $cursor = $request->query('month')
                ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $e) {
            $cursor = Carbon::now()->startOfMonth();
        }

        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd   = $cursor->copy()->endOfMonth();

        $events = Event::with('organizer')
            ->withCount(['attendees as going_count' => fn ($q) => $q->where('event_rsvps.status', 'going')])
            ->whereBetween('starts_at', [$monthStart, $monthEnd->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        // Group events by calendar day for the grid.
        $byDate = $events->groupBy(fn ($e) => $e->starts_at->format('Y-m-d'));

        // Build the 6-week (max) grid, padded to whole weeks.
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $weeks = [];
        $day = $gridStart->copy();
        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $day->copy();
                $day->addDay();
            }
            $weeks[] = $week;
        }

        return view('pages.events', [
            'weeks'      => $weeks,
            'byDate'     => $byDate,
            'events'     => $events,
            'cursor'     => $cursor,
            'prevMonth'  => $cursor->copy()->subMonth()->format('Y-m'),
            'nextMonth'  => $cursor->copy()->addMonth()->format('Y-m'),
            'today'      => Carbon::today(),
            'categories' => Event::CATEGORIES,
            'myRsvps'    => Auth::user()->eventRsvps()->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->pivot->status]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'category'    => ['required', Rule::in(array_keys(Event::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:3000'],
            'location'    => ['nullable', 'string', 'max:200'],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $event = Event::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'category'    => $validated['category'],
            'description' => $validated['description'] ?? null,
            'location'    => $validated['location'] ?? null,
            'campus'      => Auth::user()->campus,
            'starts_at'   => $validated['starts_at'],
            'ends_at'     => $validated['ends_at'] ?? null,
        ]);

        // Organizer is automatically going.
        $event->attendees()->attach(Auth::id(), ['status' => 'going']);

        return redirect()->route('events.show', $event)
                         ->with('success', 'Event created.');
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'attendees']);

        $mine = $event->attendees->firstWhere('id', Auth::id());

        return view('pages.event', [
            'event'    => $event,
            'going'    => $event->attendees->where('pivot.status', 'going'),
            'interested' => $event->attendees->where('pivot.status', 'interested'),
            'myStatus' => $mine?->pivot->status,
            'isOwner'  => $event->user_id === Auth::id(),
        ]);
    }

    /** Toggle the current user's RSVP. Clicking the same status again clears it. */
    public function rsvp(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['going', 'interested'])],
        ]);
        $status = $validated['status'];

        $existing = $event->attendees()->where('users.id', Auth::id())->first();

        if ($existing) {
            if ($existing->pivot->status === $status) {
                $event->attendees()->detach(Auth::id());
            } else {
                $event->attendees()->updateExistingPivot(Auth::id(), ['status' => $status]);
            }
        } else {
            $event->attendees()->attach(Auth::id(), ['status' => $status]);
        }

        return back()->with('success', 'RSVP updated.');
    }

    public function destroy(Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $event->delete();

        return redirect()->route('pages.events')->with('success', 'Event deleted.');
    }
}
