<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $me  = Auth::user();
        $tab = $request->query('tab', 'friends');

        // Accepted friendships can be stored in either direction.
        $accepted = Connection::where('status', 'accepted')
            ->where(fn ($q) => $q->where('user_id', $me->id)->orWhere('friend_id', $me->id))
            ->with(['requester', 'recipient'])
            ->get();

        $friends = $accepted->map(fn ($c) => [
            'user' => $c->user_id === $me->id ? $c->recipient : $c->requester,
            'conn' => $c,
        ])->filter(fn ($f) => $f['user'] !== null)->values();

        $incoming = Connection::where('friend_id', $me->id)->where('status', 'pending')
            ->with('requester')->latest()->get();
        $sent = Connection::where('user_id', $me->id)->where('status', 'pending')
            ->with('recipient')->latest()->get();
        $blocked = Connection::where('user_id', $me->id)->where('status', 'blocked')
            ->with('recipient')->latest()->get();

        // Discover: everyone I have no relationship with yet.
        $relatedIds = Connection::where('user_id', $me->id)->pluck('friend_id')
            ->merge(Connection::where('friend_id', $me->id)->pluck('user_id'))
            ->push($me->id)->unique()->all();

        $scope  = $request->query('scope', 'campus'); // campus | major | all
        $search = trim((string) $request->query('q', ''));

        $discoverQuery = User::whereNotIn('id', $relatedIds);
        if ($scope === 'campus' && $me->campus) {
            $discoverQuery->where('campus', $me->campus);
        } elseif ($scope === 'major' && $me->major) {
            $discoverQuery->where('major', $me->major);
        }
        if ($search !== '') {
            $discoverQuery->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                                               ->orWhere('major', 'like', "%{$search}%"));
        }
        $discover = $discoverQuery->orderBy('name')->paginate(18)->withQueryString();

        return view('pages.connections', [
            'tab'      => $tab,
            'friends'  => $friends,
            'incoming' => $incoming,
            'sent'     => $sent,
            'blocked'  => $blocked,
            'discover' => $discover,
            'counts'   => [
                'friends'  => $friends->count(),
                'requests' => $incoming->count(),
                'sent'     => $sent->count(),
                'blocked'  => $blocked->count(),
            ],
            'filters'  => ['scope' => $scope, 'search' => $search],
        ]);
    }

    public function request(User $user)
    {
        $me = Auth::user();

        if ($user->id === $me->id) {
            return back()->withErrors(['connect' => "You can't connect with yourself."]);
        }

        $existing = Connection::between($me->id, $user->id);

        if ($existing) {
            if ($existing->status === 'blocked') {
                return back()->withErrors(['connect' => 'This connection is blocked.']);
            }
            if ($existing->status === 'accepted') {
                return back()->with('success', "You're already connected.");
            }
            // Pending: if they already requested me, accept it instead of duplicating.
            if ($existing->friend_id === $me->id) {
                $existing->update(['status' => 'accepted']);
                return back()->with('success', "You're now connected with {$user->name}.");
            }
            return back()->with('success', 'Request already sent.');
        }

        Connection::create([
            'user_id'   => $me->id,
            'friend_id' => $user->id,
            'status'    => 'pending',
        ]);

        return back()->with('success', "Request sent to {$user->name}.");
    }

    public function accept(Connection $connection)
    {
        abort_unless($connection->friend_id === Auth::id() && $connection->status === 'pending', 403);

        $connection->update(['status' => 'accepted']);

        return back()->with('success', 'Connection accepted.');
    }

    /** Decline an incoming request, cancel a sent one, or remove a friend. */
    public function destroy(Connection $connection)
    {
        $isParty = $connection->user_id === Auth::id() || $connection->friend_id === Auth::id();
        abort_unless($isParty && $connection->status !== 'blocked', 403);

        $connection->delete();

        return back()->with('success', 'Connection removed.');
    }

    public function block(User $user)
    {
        $me = Auth::user();

        if ($user->id === $me->id) {
            return back()->withErrors(['connect' => "You can't block yourself."]);
        }

        // Clear any existing relationship between us, then record the block.
        Connection::where(fn ($q) => $q->where('user_id', $me->id)->where('friend_id', $user->id))
            ->orWhere(fn ($q) => $q->where('user_id', $user->id)->where('friend_id', $me->id))
            ->delete();

        Connection::create([
            'user_id'   => $me->id,
            'friend_id' => $user->id,
            'status'    => 'blocked',
        ]);

        return back()->with('success', "You blocked {$user->name}.");
    }

    public function unblock(User $user)
    {
        Connection::where('user_id', Auth::id())
            ->where('friend_id', $user->id)
            ->where('status', 'blocked')
            ->delete();

        return back()->with('success', "You unblocked {$user->name}.");
    }
}
