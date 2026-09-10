@extends('layouts.app')
@section('title', 'Groups & Clubs')

@section('content')
<style>
    .gr-wrap { max-width: 940px; }

    .gr-toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
    .gr-search { flex: 1; min-width: 200px; display: flex; gap: 8px; }
    .gr-input { flex: 1; padding: 10px 14px; background: #fff; border: 1px solid var(--border);
        border-radius: 7px; color: var(--text); font-family: inherit; font-size: 14px; outline: none; }
    .gr-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }

    .gr-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; }
    .gr-pill { padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
        text-decoration: none; border: 1px solid #d5dbe3; color: var(--text); background: #fff; }
    .gr-pill.active { background: var(--surface); color: #fff; border-color: var(--surface); }

    .gr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 16px; }
    .gr-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 18px; display: flex; flex-direction: column; }
    .gr-badge { align-self: flex-start; font-size: 10.5px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); margin-bottom: 10px; }
    .gr-name { font-size: 16px; font-weight: 800; text-decoration: none; color: var(--text); }
    .gr-name:hover { color: var(--surface); }
    .gr-desc { font-size: 13.5px; line-height: 1.5; color: #4a5260; margin: 6px 0 12px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .gr-meta { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace;
        margin-top: auto; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .gr-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--text-sub); }

    .gr-card-foot { display: flex; gap: 8px; align-items: center; margin-top: 14px; }
    .gr-btn-sm { padding: 7px 16px; font-size: 12.5px; }
    .gr-owner-tag { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
        color: var(--accent2); background: rgba(110,231,183,.12); padding: 5px 10px; border-radius: 6px; }
    .gr-btn-leave { background: rgba(248,113,113,.12); color: #c0392b; }
    .gr-btn-leave:hover { background: rgba(248,113,113,.22); }
    .gr-open { text-decoration: none; color: var(--surface); font-size: 12.5px; font-weight: 600; padding: 7px 4px; }

    .gr-empty { text-align: center; color: var(--text-sub); padding: 56px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }

    /* Create modal */
    .gr-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200;
        display: flex; align-items: center; justify-content: center; padding: 20px; }
    .gr-modal { background: #fff; border-radius: 14px; padding: 26px; width: 100%; max-width: 460px; }
    .gr-modal h3 { font-size: 18px; font-weight: 800; margin-bottom: 18px; }
    .gr-field { margin-bottom: 14px; }
    .gr-field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
    .gr-field .gr-input, .gr-field select, .gr-field textarea { width: 100%; padding: 10px 14px;
        background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; }
    .gr-field textarea { resize: vertical; min-height: 84px; }
    .gr-modal-foot { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    .chat-hidden { display: none; }
</style>

<div class="page-header">
    <div class="page-title">GROUPS &amp; CLUBS</div>
    <div class="page-sub">study groups · clubs · student unions — join or start your own</div>
</div>

<div class="gr-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Toolbar ─────────────────────────────────────────── --}}
    <div class="gr-toolbar">
        <button type="button" class="btn btn-primary" onclick="grToggleModal(true)">+ Create group</button>
        <form method="GET" action="{{ route('pages.groups') }}" class="gr-search">
            @if($filters['category'])<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif
            @if($filters['tab'] !== 'all')<input type="hidden" name="tab" value="{{ $filters['tab'] }}">@endif
            <input type="text" name="q" class="gr-input" placeholder="Search groups…" value="{{ $filters['search'] }}">
            <button type="submit" class="btn btn-ghost gr-btn-sm">Search</button>
        </form>
    </div>

    {{-- ── Filters + tabs ──────────────────────────────────── --}}
    <div class="gr-filters">
        <a href="{{ route('pages.groups', array_filter(['tab' => $filters['tab'] === 'mine' ? 'mine' : null])) }}"
           class="gr-pill {{ !$filters['category'] ? 'active' : '' }}">All types</a>
        @foreach($categories as $value => $label)
            <a href="{{ route('pages.groups', array_filter(['category' => $value, 'tab' => $filters['tab'] === 'mine' ? 'mine' : null, 'q' => $filters['search']])) }}"
               class="gr-pill {{ $filters['category'] === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach

        <span style="width:1px;height:22px;background:#d5dbe3;margin:0 4px;"></span>

        <a href="{{ route('pages.groups', array_filter(['category' => $filters['category'], 'q' => $filters['search']])) }}"
           class="gr-pill {{ $filters['tab'] !== 'mine' ? 'active' : '' }}">Discover</a>
        <a href="{{ route('pages.groups', array_filter(['tab' => 'mine', 'category' => $filters['category'], 'q' => $filters['search']])) }}"
           class="gr-pill {{ $filters['tab'] === 'mine' ? 'active' : '' }}">My groups</a>
    </div>

    {{-- ── Grid ────────────────────────────────────────────── --}}
    @if($groups->count())
        <div class="gr-grid">
            @foreach($groups as $group)
                @php $isOwner = $group->user_id === auth()->id(); $isMember = in_array($group->id, $myGroupIds); @endphp
                <div class="gr-card">
                    <span class="gr-badge">{{ $group->categoryLabel() }}</span>
                    <a href="{{ route('groups.show', $group) }}" class="gr-name">{{ $group->name }}</a>
                    @if($group->description)
                        <div class="gr-desc">{{ $group->description }}</div>
                    @else
                        <div class="gr-desc" style="opacity:.5;">No description yet.</div>
                    @endif

                    <div class="gr-meta">
                        <span>{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</span>
                        <span class="gr-dot"></span>
                        <span>{{ $group->campus ?? '—' }}</span>
                    </div>

                    <div class="gr-card-foot">
                        @if($isOwner)
                            <span class="gr-owner-tag">Owner</span>
                            <a href="{{ route('groups.show', $group) }}" class="gr-open">Manage →</a>
                        @elseif($isMember)
                            <form method="POST" action="{{ route('groups.leave', $group) }}">
                                @csrf
                                <button type="submit" class="btn gr-btn-sm gr-btn-leave">Leave</button>
                            </form>
                            <a href="{{ route('groups.show', $group) }}" class="gr-open">Open →</a>
                        @else
                            <form method="POST" action="{{ route('groups.join', $group) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary gr-btn-sm">Join</button>
                            </form>
                            <a href="{{ route('groups.show', $group) }}" class="gr-open">Details →</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:22px;">{{ $groups->links() }}</div>
    @else
        <div class="gr-empty">No groups here yet — be the first to create one.</div>
    @endif

</div>

{{-- ── Create modal ────────────────────────────────────────── --}}
<div class="gr-overlay chat-hidden" id="gr-overlay">
    <div class="gr-modal">
        <h3>Create a group</h3>
        <form method="POST" action="{{ route('groups.store') }}">
            @csrf
            <div class="gr-field">
                <label>Name</label>
                <input type="text" name="name" class="gr-input" maxlength="120"
                       placeholder="e.g. CS Study Crew" value="{{ old('name') }}" required>
            </div>
            <div class="gr-field">
                <label>Type</label>
                <select name="category" required>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gr-field">
                <label>Description</label>
                <textarea name="description" maxlength="2000"
                          placeholder="What's this group about? Who should join?">{{ old('description') }}</textarea>
            </div>
            <div class="gr-modal-foot">
                <button type="button" class="btn btn-ghost" onclick="grToggleModal(false)">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
    function grToggleModal(show) {
        document.getElementById('gr-overlay').classList.toggle('chat-hidden', !show);
    }
    document.getElementById('gr-overlay').addEventListener('click', function (e) {
        if (e.target === this) grToggleModal(false);
    });
    @if($errors->any() && old('name'))
        grToggleModal(true);
    @endif
</script>
@endsection
