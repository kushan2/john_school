@extends('layouts.app')
@section('title', 'Connections')

@section('content')
<style>
    .cn-wrap { max-width: 900px; }

    .cn-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 22px;
        border-bottom: 1px solid #e2e6ec; }
    .cn-tab { padding: 10px 14px; font-size: 13.5px; font-weight: 600; text-decoration: none;
        color: var(--text-sub); border-bottom: 2px solid transparent; margin-bottom: -1px; }
    .cn-tab:hover { color: var(--text); }
    .cn-tab.active { color: var(--surface); border-bottom-color: var(--surface); }
    .cn-count { display: inline-block; font-size: 11px; font-weight: 700; min-width: 18px;
        padding: 1px 6px; border-radius: 20px; background: rgba(15,76,129,.1); color: var(--surface); margin-left: 4px; }
    .cn-count.alert { background: var(--danger); color: #fff; }

    .cn-search { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .cn-input { flex: 1; min-width: 180px; padding: 10px 14px; background: #fff; border: 1px solid var(--border);
        border-radius: 7px; color: var(--text); font-family: inherit; font-size: 14px; outline: none; }
    .cn-pills { display: flex; gap: 6px; }
    .cn-pill { padding: 9px 14px; border-radius: 7px; font-size: 12.5px; font-weight: 600;
        text-decoration: none; border: 1px solid #d5dbe3; color: var(--text); background: #fff; white-space: nowrap; }
    .cn-pill.active { background: var(--surface); color: #fff; border-color: var(--surface); }

    .cn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .cn-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .cn-person { display: flex; align-items: center; gap: 12px; }
    .cn-av { width: 44px; height: 44px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 700; color: #0d0f12; }
    .cn-av img { width: 100%; height: 100%; object-fit: cover; }
    .cn-name { font-size: 15px; font-weight: 700; }
    .cn-sub { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace; margin-top: 2px; }

    .cn-actions { display: flex; gap: 8px; }
    .cn-btn { flex: 1; text-align: center; padding: 8px; border-radius: 7px; font-size: 12.5px; font-weight: 600;
        cursor: pointer; border: none; font-family: inherit; text-decoration: none; }
    .cn-btn-primary { background: var(--surface); color: #fff; }
    .cn-btn-primary:hover { opacity: .9; }
    .cn-btn-ghost { background: var(--bg); color: var(--text); border: 1px solid #d5dbe3; }
    .cn-btn-ghost:hover { background: #e7eaef; }
    .cn-btn-danger { background: rgba(248,113,113,.12); color: #c0392b; }
    .cn-btn-danger:hover { background: rgba(248,113,113,.22); }

    .cn-empty { text-align: center; color: var(--text-sub); padding: 50px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }
</style>

<div class="page-header">
    <div class="page-title">CONNECTIONS</div>
    <div class="page-sub">find & connect with students across all 26 campuses</div>
</div>

<div class="cn-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Tabs ────────────────────────────────────────────── --}}
    <div class="cn-tabs">
        <a href="{{ route('pages.connections', ['tab' => 'friends']) }}" class="cn-tab {{ $tab === 'friends' ? 'active' : '' }}">
            Friends <span class="cn-count">{{ $counts['friends'] }}</span>
        </a>
        <a href="{{ route('pages.connections', ['tab' => 'requests']) }}" class="cn-tab {{ $tab === 'requests' ? 'active' : '' }}">
            Requests <span class="cn-count {{ $counts['requests'] ? 'alert' : '' }}">{{ $counts['requests'] }}</span>
        </a>
        <a href="{{ route('pages.connections', ['tab' => 'discover']) }}" class="cn-tab {{ $tab === 'discover' ? 'active' : '' }}">Discover</a>
        <a href="{{ route('pages.connections', ['tab' => 'sent']) }}" class="cn-tab {{ $tab === 'sent' ? 'active' : '' }}">
            Sent <span class="cn-count">{{ $counts['sent'] }}</span>
        </a>
        <a href="{{ route('pages.connections', ['tab' => 'blocked']) }}" class="cn-tab {{ $tab === 'blocked' ? 'active' : '' }}">
            Blocked <span class="cn-count">{{ $counts['blocked'] }}</span>
        </a>
    </div>

    @php
        // Small helper to render a person's avatar + name block.
    @endphp

    {{-- ── Friends ─────────────────────────────────────────── --}}
    @if($tab === 'friends')
        @if($friends->count())
            <div class="cn-grid">
                @foreach($friends as $f)
                    <div class="cn-card">
                        <div class="cn-person">
                            <div class="cn-av">@if($f['user']->avatarUrl())<img src="{{ $f['user']->avatarUrl() }}" alt="">@else{{ $f['user']->initial() }}@endif</div>
                            <div>
                                <div class="cn-name">{{ $f['user']->name }}</div>
                                <div class="cn-sub">{{ $f['user']->campus ?? '—' }}{{ $f['user']->major ? ' · ' . $f['user']->major : '' }}</div>
                            </div>
                        </div>
                        <div class="cn-actions">
                            <form method="POST" action="{{ route('connections.destroy', $f['conn']) }}" style="flex:1;"
                                  onsubmit="return confirm('Remove this friend?')">
                                @csrf @method('DELETE')
                                <button class="cn-btn cn-btn-ghost" style="width:100%;">Remove</button>
                            </form>
                            <form method="POST" action="{{ route('connections.block', $f['user']) }}" style="flex:1;"
                                  onsubmit="return confirm('Block {{ $f['user']->name }}?')">
                                @csrf
                                <button class="cn-btn cn-btn-danger" style="width:100%;">Block</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cn-empty">No connections yet — head to <b>Discover</b> to find classmates.</div>
        @endif

    {{-- ── Requests (incoming) ─────────────────────────────── --}}
    @elseif($tab === 'requests')
        @if($incoming->count())
            <div class="cn-grid">
                @foreach($incoming as $conn)
                    <div class="cn-card">
                        <div class="cn-person">
                            <div class="cn-av">@if($conn->requester->avatarUrl())<img src="{{ $conn->requester->avatarUrl() }}" alt="">@else{{ $conn->requester->initial() }}@endif</div>
                            <div>
                                <div class="cn-name">{{ $conn->requester->name }}</div>
                                <div class="cn-sub">{{ $conn->requester->campus ?? '—' }}{{ $conn->requester->major ? ' · ' . $conn->requester->major : '' }}</div>
                            </div>
                        </div>
                        <div class="cn-actions">
                            <form method="POST" action="{{ route('connections.accept', $conn) }}" style="flex:1;">
                                @csrf
                                <button class="cn-btn cn-btn-primary" style="width:100%;">Accept</button>
                            </form>
                            <form method="POST" action="{{ route('connections.destroy', $conn) }}" style="flex:1;">
                                @csrf @method('DELETE')
                                <button class="cn-btn cn-btn-ghost" style="width:100%;">Decline</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cn-empty">No pending requests.</div>
        @endif

    {{-- ── Sent ────────────────────────────────────────────── --}}
    @elseif($tab === 'sent')
        @if($sent->count())
            <div class="cn-grid">
                @foreach($sent as $conn)
                    <div class="cn-card">
                        <div class="cn-person">
                            <div class="cn-av">@if($conn->recipient->avatarUrl())<img src="{{ $conn->recipient->avatarUrl() }}" alt="">@else{{ $conn->recipient->initial() }}@endif</div>
                            <div>
                                <div class="cn-name">{{ $conn->recipient->name }}</div>
                                <div class="cn-sub">{{ $conn->recipient->campus ?? '—' }} · pending</div>
                            </div>
                        </div>
                        <div class="cn-actions">
                            <form method="POST" action="{{ route('connections.destroy', $conn) }}" style="flex:1;">
                                @csrf @method('DELETE')
                                <button class="cn-btn cn-btn-ghost" style="width:100%;">Cancel request</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cn-empty">No sent requests.</div>
        @endif

    {{-- ── Blocked ─────────────────────────────────────────── --}}
    @elseif($tab === 'blocked')
        @if($blocked->count())
            <div class="cn-grid">
                @foreach($blocked as $conn)
                    <div class="cn-card">
                        <div class="cn-person">
                            <div class="cn-av">@if($conn->recipient->avatarUrl())<img src="{{ $conn->recipient->avatarUrl() }}" alt="">@else{{ $conn->recipient->initial() }}@endif</div>
                            <div>
                                <div class="cn-name">{{ $conn->recipient->name }}</div>
                                <div class="cn-sub">{{ $conn->recipient->campus ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="cn-actions">
                            <form method="POST" action="{{ route('connections.unblock', $conn->recipient) }}" style="flex:1;">
                                @csrf @method('DELETE')
                                <button class="cn-btn cn-btn-ghost" style="width:100%;">Unblock</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cn-empty">You haven't blocked anyone.</div>
        @endif

    {{-- ── Discover ────────────────────────────────────────── --}}
    @else
        <form method="GET" action="{{ route('pages.connections') }}" class="cn-search">
            <input type="hidden" name="tab" value="discover">
            <input type="text" name="q" class="cn-input" placeholder="Search by name or major…" value="{{ $filters['search'] }}">
            <div class="cn-pills">
                <a href="{{ route('pages.connections', array_filter(['tab' => 'discover', 'scope' => 'campus', 'q' => $filters['search']])) }}"
                   class="cn-pill {{ $filters['scope'] === 'campus' ? 'active' : '' }}">My campus</a>
                <a href="{{ route('pages.connections', array_filter(['tab' => 'discover', 'scope' => 'major', 'q' => $filters['search']])) }}"
                   class="cn-pill {{ $filters['scope'] === 'major' ? 'active' : '' }}">My major</a>
                <a href="{{ route('pages.connections', array_filter(['tab' => 'discover', 'scope' => 'all', 'q' => $filters['search']])) }}"
                   class="cn-pill {{ $filters['scope'] === 'all' ? 'active' : '' }}">All campuses</a>
            </div>
            <button type="submit" class="cn-pill">Search</button>
        </form>

        @if($discover->count())
            <div class="cn-grid">
                @foreach($discover as $person)
                    <div class="cn-card">
                        <div class="cn-person">
                            <div class="cn-av">@if($person->avatarUrl())<img src="{{ $person->avatarUrl() }}" alt="">@else{{ $person->initial() }}@endif</div>
                            <div>
                                <div class="cn-name">{{ $person->name }}</div>
                                <div class="cn-sub">{{ $person->campus ?? '—' }}{{ $person->major ? ' · ' . $person->major : '' }}</div>
                            </div>
                        </div>
                        <div class="cn-actions">
                            <form method="POST" action="{{ route('connections.request', $person) }}" style="flex:1;">
                                @csrf
                                <button class="cn-btn cn-btn-primary" style="width:100%;">Connect</button>
                            </form>
                            <form method="POST" action="{{ route('connections.block', $person) }}" style="flex:0 0 auto;"
                                  onsubmit="return confirm('Block {{ $person->name }}?')">
                                @csrf
                                <button class="cn-btn cn-btn-danger">Block</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:22px;">{{ $discover->links() }}</div>
        @else
            <div class="cn-empty">No students found here — try a different scope or search.</div>
        @endif
    @endif

</div>
@endsection
