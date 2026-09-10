@extends('layouts.app')
@section('title', 'News')

@section('content')
<style>
    .nw-wrap { max-width: 780px; }

    .nw-toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
    .nw-search { flex: 1; min-width: 200px; display: flex; gap: 8px; }
    .nw-input { flex: 1; padding: 10px 14px; background: #fff; border: 1px solid var(--border);
        border-radius: 7px; color: var(--text); font-family: inherit; font-size: 14px; outline: none; }
    .nw-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }

    .nw-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; }
    .nw-pill { padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
        text-decoration: none; border: 1px solid #d5dbe3; color: var(--text); background: #fff; }
    .nw-pill.active { background: var(--surface); color: #fff; border-color: var(--surface); }

    .nw-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 20px 22px; margin-bottom: 16px; }
    .nw-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); }
    .nw-title { font-size: 19px; font-weight: 800; text-decoration: none; color: var(--text);
        display: block; margin: 10px 0 6px; }
    .nw-title:hover { color: var(--surface); }
    .nw-meta { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace; margin-bottom: 10px; }
    .nw-excerpt { font-size: 14px; line-height: 1.6; color: #4a5260; }
    .nw-read { display: inline-block; margin-top: 10px; font-size: 13px; font-weight: 600;
        color: var(--surface); text-decoration: none; }

    .nw-empty { text-align: center; color: var(--text-sub); padding: 56px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }

    .gr-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200;
        display: flex; align-items: center; justify-content: center; padding: 20px; }
    .gr-modal { background: #fff; border-radius: 14px; padding: 26px; width: 100%; max-width: 520px; }
    .gr-modal h3 { font-size: 18px; font-weight: 800; margin-bottom: 18px; }
    .gr-field { margin-bottom: 14px; }
    .gr-field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
    .gr-field input, .gr-field select, .gr-field textarea { width: 100%; padding: 10px 14px;
        background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; }
    .gr-field textarea { resize: vertical; min-height: 140px; }
    .gr-modal-foot { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
    .chat-hidden { display: none; }
</style>

<div class="page-header">
    <div class="page-title">NEWS</div>
    <div class="page-sub">announcements & headlines from across the SUNY campuses</div>
</div>

<div class="nw-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    <div class="nw-toolbar">
        <button type="button" class="btn btn-primary" onclick="grToggleModal(true)">+ Post news</button>
        <form method="GET" action="{{ route('pages.news') }}" class="nw-search">
            @if($filters['category'])<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif
            <input type="text" name="q" class="nw-input" placeholder="Search news…" value="{{ $filters['search'] }}">
            <button type="submit" class="btn btn-ghost">Search</button>
        </form>
    </div>

    <div class="nw-filters">
        <a href="{{ route('pages.news') }}" class="nw-pill {{ !$filters['category'] ? 'active' : '' }}">All</a>
        @foreach($categories as $value => $label)
            <a href="{{ route('pages.news', array_filter(['category' => $value, 'q' => $filters['search']])) }}"
               class="nw-pill {{ $filters['category'] === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @forelse($posts as $post)
        <div class="nw-card">
            <span class="nw-badge">{{ $post->categoryLabel() }}</span>
            <a href="{{ route('news.show', $post) }}" class="nw-title">{{ $post->title }}</a>
            <div class="nw-meta">{{ $post->author->name ?? 'Unknown' }} · {{ $post->campus ?? '—' }} · {{ $post->created_at->diffForHumans() }}</div>
            <div class="nw-excerpt">{{ $post->excerpt() }}</div>
            <a href="{{ route('news.show', $post) }}" class="nw-read">Read full story →</a>
        </div>
    @empty
        <div class="nw-empty">No news yet — post the first announcement.</div>
    @endforelse

    <div style="margin-top:20px;">{{ $posts->links() }}</div>

</div>

{{-- ── Post modal ──────────────────────────────────────────── --}}
<div class="gr-overlay chat-hidden" id="gr-overlay">
    <div class="gr-modal">
        <h3>Post news</h3>
        <form method="POST" action="{{ route('news.store') }}">
            @csrf
            <div class="gr-field">
                <label>Headline</label>
                <input type="text" name="title" maxlength="180" placeholder="What's the news?" value="{{ old('title') }}" required>
            </div>
            <div class="gr-field">
                <label>Category</label>
                <select name="category" required>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gr-field">
                <label>Story</label>
                <textarea name="body" maxlength="8000" placeholder="Write the full story…" required>{{ old('body') }}</textarea>
            </div>
            <div class="gr-modal-foot">
                <button type="button" class="btn btn-ghost" onclick="grToggleModal(false)">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish</button>
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
