@extends('layouts.app')
@section('title', 'Events')

@section('content')
<style>
    .ev-wrap { max-width: 940px; }

    .ev-head { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; flex-wrap: wrap; }
    .ev-month { font-size: 18px; font-weight: 800; min-width: 180px; }
    .ev-nav { display: flex; gap: 6px; }
    .ev-nav a { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
        border: 1px solid #d5dbe3; border-radius: 8px; text-decoration: none; color: var(--text);
        font-size: 16px; background: #fff; }
    .ev-nav a:hover { border-color: var(--accent); color: var(--surface); }
    .ev-today-link { font-size: 12.5px; color: var(--surface); text-decoration: none; font-weight: 600; align-self: center; }

    /* Calendar grid */
    .ev-cal { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        overflow: hidden; margin-bottom: 28px; }
    .ev-dow { display: grid; grid-template-columns: repeat(7, 1fr); background: var(--bg); }
    .ev-dow span { padding: 8px; text-align: center; font-size: 11px; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase; color: var(--text-sub); }
    .ev-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
    .ev-cell { min-height: 92px; border-top: 1px solid #eef1f5; border-left: 1px solid #eef1f5;
        padding: 6px; display: flex; flex-direction: column; gap: 3px; }
    .ev-cell:nth-child(7n+1) { border-left: none; }
    .ev-cell.dim { background: #fafbfc; }
    .ev-daynum { font-size: 12px; font-weight: 600; color: var(--text); align-self: flex-end; }
    .ev-cell.dim .ev-daynum { color: #c4ccd6; }
    .ev-cell.today .ev-daynum { background: var(--surface); color: #fff; width: 22px; height: 22px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .ev-chip { font-size: 11px; padding: 2px 6px; border-radius: 5px; text-decoration: none;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        background: rgba(15,76,129,.1); color: var(--surface); font-weight: 600; }
    .ev-chip:hover { background: rgba(15,76,129,.2); }
    .ev-more { font-size: 10.5px; color: var(--text-sub); font-family: 'DM Mono', monospace; }

    /* List */
    .ev-list-h { font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        color: var(--text-sub); margin-bottom: 14px; }
    .ev-item { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 16px 18px; margin-bottom: 12px; display: flex; gap: 16px; align-items: center; }
    .ev-date { text-align: center; flex-shrink: 0; width: 48px; }
    .ev-date .d { font-size: 20px; font-weight: 800; line-height: 1; }
    .ev-date .m { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--surface); }
    .ev-info { flex: 1; min-width: 0; }
    .ev-badge { font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
        padding: 2px 8px; border-radius: 20px; background: rgba(15,76,129,.09); color: var(--surface); }
    .ev-title { font-size: 15px; font-weight: 700; text-decoration: none; color: var(--text); }
    .ev-title:hover { color: var(--surface); }
    .ev-sub { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace; margin-top: 3px; }
    .ev-rsvp { display: flex; gap: 6px; flex-shrink: 0; }
    .ev-rbtn { padding: 6px 12px; font-size: 12px; }
    .ev-rbtn.going { background: var(--surface); color: #fff; }
    .ev-rbtn.interested { background: var(--border); color: #fff; }
    .ev-rbtn.off { background: var(--bg); color: var(--text); border: 1px solid #d5dbe3; }

    .ev-empty { text-align: center; color: var(--text-sub); padding: 40px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }

    .gr-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200;
        display: flex; align-items: center; justify-content: center; padding: 20px; }
    .gr-modal { background: #fff; border-radius: 14px; padding: 26px; width: 100%; max-width: 480px; }
    .gr-modal h3 { font-size: 18px; font-weight: 800; margin-bottom: 18px; }
    .gr-field { margin-bottom: 14px; }
    .gr-field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
    .gr-field input, .gr-field select, .gr-field textarea { width: 100%; padding: 10px 14px;
        background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; }
    .gr-field textarea { resize: vertical; min-height: 74px; }
    .gr-two { display: flex; gap: 12px; } .gr-two > div { flex: 1; }
    .gr-modal-foot { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
    .chat-hidden { display: none; }

    @media (max-width: 768px) {
        .ev-cell { min-height: 62px; }
        .ev-chip { display: none; }
        .ev-cell.has-events .ev-daynum::after { content: ''; display: block; width: 5px; height: 5px;
            border-radius: 50%; background: var(--accent); margin: 2px auto 0; }
        .ev-item { flex-wrap: wrap; }
    }
</style>

<div class="page-header">
    <div class="page-title">EVENTS</div>
    <div class="page-sub">parties · sports · concerts — happening across every campus</div>
</div>

<div class="ev-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    <div class="ev-head">
        <div class="ev-month">{{ $cursor->format('F Y') }}</div>
        <div class="ev-nav">
            <a href="{{ route('pages.events', ['month' => $prevMonth]) }}" title="Previous month">‹</a>
            <a href="{{ route('pages.events', ['month' => $nextMonth]) }}" title="Next month">›</a>
        </div>
        <a href="{{ route('pages.events') }}" class="ev-today-link">Today</a>
        <button type="button" class="btn btn-primary" style="margin-left:auto;" onclick="grToggleModal(true)">+ Create event</button>
    </div>

    {{-- ── Calendar ────────────────────────────────────────── --}}
    <div class="ev-cal">
        <div class="ev-dow">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)<span>{{ $dow }}</span>@endforeach
        </div>
        <div class="ev-grid">
            @foreach($weeks as $week)
                @foreach($week as $day)
                    @php $key = $day->format('Y-m-d'); $dayEvents = $byDate[$key] ?? collect(); @endphp
                    <div class="ev-cell {{ $day->month !== $cursor->month ? 'dim' : '' }} {{ $day->isSameDay($today) ? 'today' : '' }} {{ $dayEvents->count() ? 'has-events' : '' }}">
                        <span class="ev-daynum">{{ $day->day }}</span>
                        @foreach($dayEvents->take(3) as $ev)
                            <a href="{{ route('events.show', $ev) }}" class="ev-chip" title="{{ $ev->title }}">{{ $ev->starts_at->format('g:ia') }} {{ $ev->title }}</a>
                        @endforeach
                        @if($dayEvents->count() > 3)
                            <span class="ev-more">+{{ $dayEvents->count() - 3 }} more</span>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- ── Month list with RSVP ────────────────────────────── --}}
    <div class="ev-list-h">{{ $cursor->format('F') }} events</div>

    @forelse($events as $event)
        @php $my = $myRsvps[$event->id] ?? null; @endphp
        <div class="ev-item">
            <div class="ev-date">
                <div class="d">{{ $event->starts_at->format('j') }}</div>
                <div class="m">{{ $event->starts_at->format('M') }}</div>
            </div>
            <div class="ev-info">
                <span class="ev-badge">{{ $event->categoryLabel() }}</span>
                <div style="margin-top:5px;"><a href="{{ route('events.show', $event) }}" class="ev-title">{{ $event->title }}</a></div>
                <div class="ev-sub">
                    {{ $event->starts_at->format('D g:ia') }}
                    · {{ $event->location ?: ($event->campus ?? '—') }}
                    · {{ $event->going_count }} going
                </div>
            </div>
            <div class="ev-rsvp">
                <form method="POST" action="{{ route('events.rsvp', $event) }}">
                    @csrf <input type="hidden" name="status" value="going">
                    <button class="btn ev-rbtn {{ $my === 'going' ? 'going' : 'off' }}">Going</button>
                </form>
                <form method="POST" action="{{ route('events.rsvp', $event) }}">
                    @csrf <input type="hidden" name="status" value="interested">
                    <button class="btn ev-rbtn {{ $my === 'interested' ? 'interested' : 'off' }}">Interested</button>
                </form>
            </div>
        </div>
    @empty
        <div class="ev-empty">No events this month — create one!</div>
    @endforelse

</div>

{{-- ── Create modal ────────────────────────────────────────── --}}
<div class="gr-overlay chat-hidden" id="gr-overlay">
    <div class="gr-modal">
        <h3>Create an event</h3>
        <form method="POST" action="{{ route('events.store') }}">
            @csrf
            <div class="gr-field">
                <label>Title</label>
                <input type="text" name="title" maxlength="150" placeholder="e.g. Spring Concert on the Quad" value="{{ old('title') }}" required>
            </div>
            <div class="gr-two">
                <div class="gr-field">
                    <label>Type</label>
                    <select name="category" required>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gr-field">
                    <label>Location</label>
                    <input type="text" name="location" maxlength="200" placeholder="Where?" value="{{ old('location') }}">
                </div>
            </div>
            <div class="gr-two">
                <div class="gr-field">
                    <label>Starts</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required>
                </div>
                <div class="gr-field">
                    <label>Ends (optional)</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}">
                </div>
            </div>
            <div class="gr-field">
                <label>Description</label>
                <textarea name="description" maxlength="3000" placeholder="What's happening?">{{ old('description') }}</textarea>
            </div>
            <div class="gr-modal-foot">
                <button type="button" class="btn btn-ghost" onclick="grToggleModal(false)">Cancel</button>
                <button type="submit" class="btn btn-primary">Create event</button>
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
    @if($errors->any() && old('title'))
        grToggleModal(true);
    @endif
</script>
@endsection
