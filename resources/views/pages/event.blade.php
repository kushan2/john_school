@extends('layouts.app')
@section('title', $event->title)

@section('content')
<style>
    .ed-wrap { max-width: 720px; }
    .ed-back { display: inline-block; font-size: 13px; color: var(--text-sub);
        text-decoration: none; margin-bottom: 16px; font-family: 'DM Mono', monospace; }
    .ed-back:hover { color: var(--surface); }

    .ed-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 24px; margin-bottom: 20px; }
    .ed-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); }
    .ed-title { font-size: 24px; font-weight: 900; margin: 10px 0 12px; }
    .ed-facts { display: flex; flex-direction: column; gap: 8px; margin-bottom: 6px; }
    .ed-fact { display: flex; gap: 10px; align-items: center; font-size: 14px; color: #2a3340; }
    .ed-fact svg { color: var(--surface); flex-shrink: 0; }
    .ed-desc { font-size: 14.5px; line-height: 1.6; color: #2a3340; white-space: pre-wrap;
        margin-top: 16px; padding-top: 16px; border-top: 1px solid #eef1f5; }

    .ed-actions { display: flex; gap: 10px; margin-top: 20px; padding-top: 18px; border-top: 1px solid #eef1f5; }
    .ed-rbtn.going { background: var(--surface); color: #fff; }
    .ed-rbtn.interested { background: var(--border); color: #fff; }
    .ed-rbtn.off { background: var(--bg); color: var(--text); border: 1px solid #d5dbe3; }
    .ed-btn-del { background: rgba(248,113,113,.12); color: #c0392b; margin-left: auto; }
    .ed-btn-del:hover { background: rgba(248,113,113,.22); }

    .ed-card h3 { font-size: 13px; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 14px; }
    .ed-people { display: flex; flex-wrap: wrap; gap: 14px; }
    .ed-person { display: flex; align-items: center; gap: 9px; }
    .ed-av { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #0d0f12; }
    .ed-av img { width: 100%; height: 100%; object-fit: cover; }
    .ed-person-name { font-size: 13px; font-weight: 600; }
    .ed-person-sub { font-size: 11px; color: var(--text-sub); font-family: 'DM Mono', monospace; }
    .ed-none { font-size: 13px; color: var(--text-sub); font-family: 'DM Mono', monospace; }
</style>

<div class="ed-wrap">
    <a href="{{ route('pages.events', ['month' => $event->starts_at->format('Y-m')]) }}" class="ed-back">← Back to calendar</a>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    <div class="ed-card">
        <span class="ed-badge">{{ $event->categoryLabel() }}</span>
        <div class="ed-title">{{ $event->title }}</div>

        <div class="ed-facts">
            <div class="ed-fact">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ $event->starts_at->format('l, F j · g:ia') }}@if($event->ends_at) – {{ $event->ends_at->format('g:ia') }}@endif
            </div>
            @if($event->location)
                <div class="ed-fact">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $event->location }}
                </div>
            @endif
            <div class="ed-fact">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Hosted by {{ $event->organizer->name ?? 'Unknown' }}{{ $event->campus ? ' · ' . $event->campus : '' }}
            </div>
        </div>

        @if($event->description)
            <div class="ed-desc">{{ $event->description }}</div>
        @endif

        <div class="ed-actions">
            <form method="POST" action="{{ route('events.rsvp', $event) }}">
                @csrf <input type="hidden" name="status" value="going">
                <button class="btn ed-rbtn {{ $myStatus === 'going' ? 'going' : 'off' }}">{{ $myStatus === 'going' ? '✓ Going' : 'Going' }}</button>
            </form>
            <form method="POST" action="{{ route('events.rsvp', $event) }}">
                @csrf <input type="hidden" name="status" value="interested">
                <button class="btn ed-rbtn {{ $myStatus === 'interested' ? 'interested' : 'off' }}">{{ $myStatus === 'interested' ? '✓ Interested' : 'Interested' }}</button>
            </form>
            @if($isOwner)
                <form method="POST" action="{{ route('events.destroy', $event) }}"
                      onsubmit="return confirm('Delete this event?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn ed-btn-del">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="ed-card">
        <h3>Going · {{ $going->count() }}</h3>
        @if($going->count())
            <div class="ed-people">
                @foreach($going as $person)
                    <div class="ed-person">
                        <div class="ed-av">
                            @if($person->avatarUrl())<img src="{{ $person->avatarUrl() }}" alt="">@else{{ $person->initial() }}@endif
                        </div>
                        <div>
                            <div class="ed-person-name">{{ $person->name }}</div>
                            <div class="ed-person-sub">{{ $person->campus ?? '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ed-none">No one yet — be the first to RSVP.</div>
        @endif

        @if($interested->count())
            <h3 style="margin-top:22px;">Interested · {{ $interested->count() }}</h3>
            <div class="ed-people">
                @foreach($interested as $person)
                    <div class="ed-person">
                        <div class="ed-av">
                            @if($person->avatarUrl())<img src="{{ $person->avatarUrl() }}" alt="">@else{{ $person->initial() }}@endif
                        </div>
                        <div>
                            <div class="ed-person-name">{{ $person->name }}</div>
                            <div class="ed-person-sub">{{ $person->campus ?? '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
