<?php

namespace App\Services\Assistant;

use App\Models\Classified;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Group;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * The single access-control boundary for the AI assistant.
 *
 * Every piece of data the assistant can read passes through a method here, and
 * every method is scoped to what THIS user is allowed to see:
 *
 *   • Campus content (events, groups, classifieds, news, directory) is limited
 *     to the user's own campus — the same visibility rule the pages use.
 *   • "My" content (memberships, RSVPs, connections) is limited to rows that
 *     belong to the user.
 *   • Sensitive columns (emails, passwords, tokens) are never selected.
 *
 * The model and the tool layer cannot widen this — if a query isn't defined
 * here, the assistant simply cannot reach that data. To change what the
 * assistant may see, change this class and nothing else.
 */
class AssistantAccess
{
    public function __construct(private readonly User $user)
    {
    }

    public function user(): User
    {
        return $this->user;
    }

    public function campus(): ?string
    {
        return $this->user->campus;
    }

    /* ─────────────────────────  Campus-scoped content  ───────────────────────── */

    /** Events at the user's campus. */
    public function events(): Builder
    {
        return Event::query()->where('campus', $this->campus());
    }

    /** Groups / clubs at the user's campus. */
    public function groups(): Builder
    {
        return Group::query()->where('campus', $this->campus());
    }

    /** Classifieds / roommate listings at the user's campus. */
    public function classifieds(): Builder
    {
        return Classified::query()->where('campus', $this->campus());
    }

    /** News posts at the user's campus. */
    public function news(): Builder
    {
        return NewsPost::query()->where('campus', $this->campus());
    }

    /**
     * Other students at the user's campus (directory).
     * Only non-sensitive, profile-public columns are selected — never email.
     */
    public function directory(): Builder
    {
        return User::query()
            ->where('campus', $this->campus())
            ->whereKeyNot($this->user->getKey())
            ->select(['id', 'name', 'campus', 'major', 'bio']);
    }

    /* ─────────────────────────  "My" content  ───────────────────────── */

    /** Groups the user is a member of. */
    public function myGroups(): BelongsToMany
    {
        return $this->user->groups();
    }

    /** Events the user has RSVP'd to. */
    public function myEvents(): BelongsToMany
    {
        return $this->user->eventRsvps();
    }

    /**
     * The user's accepted connections (the "other" party of each row).
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function myConnections()
    {
        $id = $this->user->getKey();

        return Connection::query()
            ->where('status', 'accepted')
            ->where(fn ($q) => $q->where('user_id', $id)->orWhere('friend_id', $id))
            ->get()
            ->map(function (Connection $c) use ($id) {
                $otherId = $c->user_id === $id ? $c->friend_id : $c->user_id;

                return User::query()
                    ->select(['id', 'name', 'campus', 'major', 'bio'])
                    ->find($otherId);
            })
            ->filter()
            ->values();
    }
}
