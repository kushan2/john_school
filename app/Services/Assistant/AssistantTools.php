<?php

namespace App\Services\Assistant;

use App\Models\Classified;
use App\Models\Event;
use App\Models\Group;
use App\Models\NewsPost;
use Anthropic\Messages\Tool;
use Illuminate\Support\Carbon;

/**
 * Declares the tools the assistant may call and executes them.
 *
 * Each handler reads ONLY through {@see AssistantAccess}, so every result is
 * already scoped to what the logged-in user is allowed to see. Handlers return
 * a compact JSON string that goes straight back to the model as a tool_result.
 */
class AssistantTools
{
    /** Hard cap so a single tool call can never dump the whole table. */
    private const MAX_LIMIT = 25;

    public function __construct(private readonly AssistantAccess $access)
    {
    }

    /**
     * Tool definitions sent to the Messages API.
     *
     * @return list<Tool>
     */
    public function definitions(): array
    {
        $q       = ['type' => 'string', 'description' => 'Optional free-text search over titles/descriptions.'];
        $limit   = ['type' => 'integer', 'description' => 'Max results (1-25, default 10).'];

        return [
            Tool::with(
                name: 'get_current_context',
                description: 'Get who the current user is (name, campus, major) and today\'s date. Call this first to ground answers about "my campus", "here", "today", etc.',
                inputSchema: ['type' => 'object', 'properties' => (object) [], 'additionalProperties' => false],
            ),
            Tool::with(
                name: 'search_events',
                description: 'Search upcoming events at the user\'s campus. Use for questions about what\'s happening, parties, sports, concerts, academic events.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'q' => $q,
                        'category' => ['type' => 'string', 'enum' => array_keys(Event::CATEGORIES)],
                        'include_past' => ['type' => 'boolean', 'description' => 'Include events that already started (default false).'],
                        'limit' => $limit,
                    ],
                    'additionalProperties' => false,
                ],
            ),
            Tool::with(
                name: 'search_groups',
                description: 'Search groups, clubs and student unions at the user\'s campus.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'q' => $q,
                        'category' => ['type' => 'string', 'enum' => array_keys(Group::CATEGORIES)],
                        'limit' => $limit,
                    ],
                    'additionalProperties' => false,
                ],
            ),
            Tool::with(
                name: 'search_classifieds',
                description: 'Search classifieds / roommate & marketplace listings at the user\'s campus.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'q' => $q,
                        'category' => ['type' => 'string', 'enum' => array_keys(Classified::CATEGORIES)],
                        'limit' => $limit,
                    ],
                    'additionalProperties' => false,
                ],
            ),
            Tool::with(
                name: 'search_news',
                description: 'Search campus news posts and announcements at the user\'s campus.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'q' => $q,
                        'category' => ['type' => 'string', 'enum' => array_keys(NewsPost::CATEGORIES)],
                        'limit' => $limit,
                    ],
                    'additionalProperties' => false,
                ],
            ),
            Tool::with(
                name: 'search_directory',
                description: 'Find other students at the user\'s campus by name or major. Returns public profile info only (name, major, bio) — never contact details.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'q' => ['type' => 'string', 'description' => 'Name to search for.'],
                        'major' => ['type' => 'string', 'description' => 'Filter by major.'],
                        'limit' => $limit,
                    ],
                    'additionalProperties' => false,
                ],
            ),
            Tool::with(
                name: 'get_my_stuff',
                description: 'Get the current user\'s own memberships: the groups they belong to, the events they have RSVP\'d to, and their accepted connections.',
                inputSchema: ['type' => 'object', 'properties' => (object) [], 'additionalProperties' => false],
            ),
        ];
    }

    /**
     * Execute a tool call. Returns a JSON string for the tool_result block.
     *
     * @param  array<string,mixed>  $input
     */
    public function handle(string $name, array $input): string
    {
        $result = match ($name) {
            'get_current_context' => $this->currentContext(),
            'search_events'       => $this->searchEvents($input),
            'search_groups'       => $this->searchGroups($input),
            'search_classifieds'  => $this->searchClassifieds($input),
            'search_news'         => $this->searchNews($input),
            'search_directory'    => $this->searchDirectory($input),
            'get_my_stuff'        => $this->myStuff(),
            default               => ['error' => "Unknown tool: {$name}"],
        };

        return json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /* ─────────────────────────  Handlers  ───────────────────────── */

    private function currentContext(): array
    {
        $u = $this->access->user();

        return [
            'name'   => $u->name,
            'campus' => $u->campus,
            'major'  => $u->major,
            'today'  => Carbon::now()->toDayDateTimeString(),
        ];
    }

    private function searchEvents(array $input): array
    {
        $query = $this->access->events();

        if (empty($input['include_past'])) {
            $query->where('starts_at', '>=', Carbon::now());
        }
        $this->applyText($query, $input, ['title', 'description', 'location']);
        $this->applyCategory($query, $input);

        $rows = $query->orderBy('starts_at')->limit($this->limit($input))->get();

        return $rows->map(fn (Event $e) => [
            'title'     => $e->title,
            'category'  => $e->categoryLabel(),
            'location'  => $e->location,
            'starts_at' => optional($e->starts_at)->toDayDateTimeString(),
            'ends_at'   => optional($e->ends_at)->toDayDateTimeString(),
            'about'     => $this->snippet($e->description),
        ])->all();
    }

    private function searchGroups(array $input): array
    {
        $query = $this->access->groups();
        $this->applyText($query, $input, ['name', 'description']);
        $this->applyCategory($query, $input);

        $rows = $query->withCount('members')->latest()->limit($this->limit($input))->get();

        return $rows->map(fn (Group $g) => [
            'name'     => $g->name,
            'category' => $g->categoryLabel(),
            'members'  => $g->members_count,
            'about'    => $this->snippet($g->description),
        ])->all();
    }

    private function searchClassifieds(array $input): array
    {
        $query = $this->access->classifieds();
        $this->applyText($query, $input, ['title', 'body']);
        $this->applyCategory($query, $input);

        $rows = $query->latest()->limit($this->limit($input))->get();

        return $rows->map(fn (Classified $c) => [
            'title'    => $c->title,
            'category' => $c->categoryLabel(),
            'price'    => $c->price,
            'about'    => $this->snippet($c->body),
        ])->all();
    }

    private function searchNews(array $input): array
    {
        $query = $this->access->news();
        $this->applyText($query, $input, ['title', 'body']);
        $this->applyCategory($query, $input);

        $rows = $query->latest()->limit($this->limit($input))->get();

        return $rows->map(fn (NewsPost $n) => [
            'title'     => $n->title,
            'category'  => $n->categoryLabel(),
            'posted_at' => optional($n->created_at)->toDayDateTimeString(),
            'excerpt'   => $n->excerpt(40),
        ])->all();
    }

    private function searchDirectory(array $input): array
    {
        $query = $this->access->directory();

        if (! empty($input['q'])) {
            $query->where('name', 'like', '%'.$input['q'].'%');
        }
        if (! empty($input['major'])) {
            $query->where('major', 'like', '%'.$input['major'].'%');
        }

        return $query->limit($this->limit($input))->get()->map(fn ($u) => [
            'name'  => $u->name,
            'major' => $u->major,
            'bio'   => $this->snippet($u->bio),
        ])->all();
    }

    private function myStuff(): array
    {
        return [
            'groups' => $this->access->myGroups()->get()->map(fn (Group $g) => [
                'name' => $g->name,
                'role' => $g->pivot->role ?? 'member',
            ])->all(),
            'events_rsvpd' => $this->access->myEvents()->get()->map(fn (Event $e) => [
                'title'     => $e->title,
                'status'    => $e->pivot->status ?? null,
                'starts_at' => optional($e->starts_at)->toDayDateTimeString(),
            ])->all(),
            'connections' => $this->access->myConnections()->map(fn ($u) => [
                'name'  => $u->name,
                'major' => $u->major,
            ])->all(),
        ];
    }

    /* ─────────────────────────  Helpers  ───────────────────────── */

    private function limit(array $input): int
    {
        $n = (int) ($input['limit'] ?? 10);

        return max(1, min(self::MAX_LIMIT, $n));
    }

    /** @param array<int,string> $columns */
    private function applyText($query, array $input, array $columns): void
    {
        $term = trim((string) ($input['q'] ?? ''));
        if ($term === '') {
            return;
        }

        $query->where(function ($q) use ($columns, $term) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', '%'.$term.'%');
            }
        });
    }

    private function applyCategory($query, array $input): void
    {
        if (! empty($input['category'])) {
            $query->where('category', $input['category']);
        }
    }

    private function snippet(?string $text, int $words = 40): ?string
    {
        if (! $text) {
            return null;
        }

        return \Illuminate\Support\Str::words(strip_tags($text), $words, '…');
    }
}
