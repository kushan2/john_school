<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $me = Auth::user();
        $tab = $request->query('tab', 'all');

        $query = Group::with('owner')->withCount('members')->latest();

        $category = $request->query('category');
        if ($category && array_key_exists($category, Group::CATEGORIES)) {
            $query->where('category', $category);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($tab === 'mine') {
            $query->whereHas('members', fn ($q) => $q->where('users.id', $me->id));
        }

        return view('pages.groups', [
            'groups'     => $query->paginate(18)->withQueryString(),
            'categories' => Group::CATEGORIES,
            'myGroupIds' => $me->groups()->pluck('groups.id')->all(),
            'filters'    => ['category' => $category, 'search' => $search, 'tab' => $tab],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'category'    => ['required', Rule::in(array_keys(Group::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $group = Group::create([
            'user_id'     => Auth::id(),
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'description' => $validated['description'] ?? null,
            'campus'      => Auth::user()->campus,
        ]);

        // Creator is automatically the first member, with the owner role.
        $group->members()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('groups.show', $group)
                         ->with('success', 'Group created — you\'re the owner.');
    }

    public function show(Group $group)
    {
        $group->load(['owner', 'members' => fn ($q) => $q->orderByPivot('role')->orderBy('name')]);

        return view('pages.group', [
            'group'    => $group,
            'isMember' => $group->members->contains(Auth::id()),
            'isOwner'  => $group->user_id === Auth::id(),
        ]);
    }

    public function join(Group $group)
    {
        $group->members()->syncWithoutDetaching([Auth::id() => ['role' => 'member']]);

        return back()->with('success', "You joined {$group->name}.");
    }

    public function leave(Group $group)
    {
        if ($group->user_id === Auth::id()) {
            return back()->withErrors(['leave' => 'Owners can\'t leave — delete the group instead.']);
        }

        $group->members()->detach(Auth::id());

        return back()->with('success', "You left {$group->name}.");
    }

    public function destroy(Group $group)
    {
        abort_unless($group->user_id === Auth::id(), 403);

        $group->delete();

        return redirect()->route('pages.groups')->with('success', 'Group deleted.');
    }
}
