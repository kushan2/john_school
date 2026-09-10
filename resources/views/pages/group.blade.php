@extends('layouts.app')
@section('title', $group->name)

@section('content')
<style>
    .gd-wrap { max-width: 720px; }
    .gd-back { display: inline-block; font-size: 13px; color: var(--text-sub);
        text-decoration: none; margin-bottom: 16px; font-family: 'DM Mono', monospace; }
    .gd-back:hover { color: var(--surface); }

    .gd-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 24px; margin-bottom: 20px; }
    .gd-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); }
    .gd-name { font-size: 24px; font-weight: 900; margin: 10px 0 4px; }
    .gd-meta { font-size: 12.5px; color: var(--text-sub); font-family: 'DM Mono', monospace; }
    .gd-desc { font-size: 14.5px; line-height: 1.6; color: #2a3340; white-space: pre-wrap; margin-top: 16px; }

    .gd-actions { display: flex; gap: 10px; margin-top: 20px; padding-top: 18px; border-top: 1px solid #eef1f5; }

    .gd-card h3 { font-size: 13px; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 16px; }
    .gd-member { display: flex; align-items: center; gap: 12px; padding: 8px 0; }
    .gd-av { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700; color: #0d0f12; }
    .gd-av img { width: 100%; height: 100%; object-fit: cover; }
    .gd-member-name { font-size: 14px; font-weight: 600; }
    .gd-member-sub { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace; }
    .gd-role { margin-left: auto; font-size: 10.5px; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px; }
    .gd-role.owner { color: var(--accent2); background: rgba(110,231,183,.14); }
    .gd-role.member { color: var(--text-sub); background: var(--bg); }

    .gd-btn-leave { background: rgba(248,113,113,.12); color: #c0392b; }
    .gd-btn-leave:hover { background: rgba(248,113,113,.22); }
</style>

<div class="gd-wrap">
    <a href="{{ route('pages.groups') }}" class="gd-back">← All groups</a>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Group header ────────────────────────────────────── --}}
    <div class="gd-card">
        <span class="gd-badge">{{ $group->categoryLabel() }}</span>
        <div class="gd-name">{{ $group->name }}</div>
        <div class="gd-meta">
            {{ $group->members->count() }} {{ Str::plural('member', $group->members->count()) }}
            · started by {{ $group->owner->name ?? 'Unknown' }}
            · {{ $group->campus ?? '—' }}
        </div>

        @if($group->description)
            <div class="gd-desc">{{ $group->description }}</div>
        @endif

        <div class="gd-actions">
            @if($isOwner)
                <form method="POST" action="{{ route('groups.destroy', $group) }}"
                      onsubmit="return confirm('Delete this group for everyone? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn gd-btn-leave">Delete group</button>
                </form>
            @elseif($isMember)
                <form method="POST" action="{{ route('groups.leave', $group) }}">
                    @csrf
                    <button type="submit" class="btn gd-btn-leave">Leave group</button>
                </form>
            @else
                <form method="POST" action="{{ route('groups.join', $group) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Join group</button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Members ─────────────────────────────────────────── --}}
    <div class="gd-card">
        <h3>Members</h3>
        @foreach($group->members as $member)
            <div class="gd-member">
                <div class="gd-av">
                    @if($member->avatarUrl())
                        <img src="{{ $member->avatarUrl() }}" alt="">
                    @else
                        {{ $member->initial() }}
                    @endif
                </div>
                <div>
                    <div class="gd-member-name">{{ $member->name }}</div>
                    <div class="gd-member-sub">{{ $member->campus ?? '—' }}{{ $member->major ? ' · ' . $member->major : '' }}</div>
                </div>
                <span class="gd-role {{ $member->pivot->role === 'owner' ? 'owner' : 'member' }}">{{ $member->pivot->role }}</span>
            </div>
        @endforeach
    </div>

</div>
@endsection
