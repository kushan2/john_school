<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /** Number of recent messages loaded when the page first opens. */
    private const INITIAL_LIMIT = 60;

    public function index()
    {
        return view('pages.dashboard', [
            'emojis' => ChatMessage::EMOJIS,
        ]);
    }

    /**
     * JSON feed used by the client poller.
     * ?after=<id>  → messages newer than that id (live updates).
     * ?after=0     → the most recent batch (initial page load).
     */
    public function fetch(Request $request)
    {
        $after = (int) $request->query('after', 0);

        if ($after > 0) {
            $messages = ChatMessage::with(['user', 'reactions'])
                ->where('id', '>', $after)
                ->orderBy('id')
                ->limit(200)
                ->get();
        } else {
            $messages = ChatMessage::with(['user', 'reactions'])
                ->latest()
                ->limit(self::INITIAL_LIMIT)
                ->get()
                ->reverse()
                ->values();
        }

        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->serialize($m)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = Auth::user()->chatMessages()->create([
            'campus' => Auth::user()->campus,
            'body'   => $validated['body'],
        ]);

        $message->load(['user', 'reactions']);

        return response()->json($this->serialize($message), 201);
    }

    /** Toggle the current user's reaction of a given emoji on a message. */
    public function react(Request $request, ChatMessage $message)
    {
        $emoji = (string) $request->input('emoji');
        abort_unless(in_array($emoji, ChatMessage::EMOJIS, true), 422);

        $existing = $message->reactions()
            ->where('user_id', Auth::id())
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $message->reactions()->create([
                'user_id' => Auth::id(),
                'emoji'   => $emoji,
            ]);
        }

        $message->load(['user', 'reactions']);

        return response()->json($this->serialize($message));
    }

    /** Shape a message (with aggregated reactions) for the client. */
    private function serialize(ChatMessage $message): array
    {
        $counts = [];
        $reacted = [];

        foreach ($message->reactions as $reaction) {
            $counts[$reaction->emoji] = ($counts[$reaction->emoji] ?? 0) + 1;
            if ($reaction->user_id === Auth::id()) {
                $reacted[] = $reaction->emoji;
            }
        }

        $name = $message->user->name ?? 'Unknown';

        return [
            'id'        => $message->id,
            'name'      => $name,
            'campus'    => $message->campus,
            'initial'   => strtoupper(mb_substr($name, 0, 1)),
            'body'      => $message->body,
            'time'      => $message->created_at->diffForHumans(),
            'mine'      => $message->user_id === Auth::id(),
            'reactions' => $counts,
            'reacted'   => $reacted,
        ];
    }
}
