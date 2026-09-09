@extends('layouts.app')
@section('title', 'Classifieds')

@section('content')
<style>
    .cl-wrap { max-width: 820px; }

    .cl-alert { margin-bottom: 20px; }

    /* Shared field styling for this page (textarea/select not covered by layout) */
    .cl-field { width: 100%; padding: 10px 14px; background: var(--bg);
        border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none;
        transition: border-color .15s, box-shadow .15s; }
    .cl-field:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
    textarea.cl-field { resize: vertical; min-height: 84px; }

    .cl-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 18px 20px; margin-bottom: 16px; }
    .cl-card-lead { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 18px 20px; margin-bottom: 24px; }

    .cl-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .cl-row > .cl-field { flex: 1; min-width: 160px; }

    .cl-collapse-btn { display: flex; align-items: center; justify-content: space-between;
        width: 100%; cursor: pointer; background: none; border: none; font-family: inherit;
        font-size: 15px; font-weight: 700; color: var(--text); }

    .cl-badge { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); }
    .cl-price { font-weight: 700; color: #0a7a4b; }

    .cl-title { font-size: 17px; font-weight: 800; margin: 8px 0 4px; }
    .cl-body { font-size: 14px; line-height: 1.55; white-space: pre-wrap; color: #2a3340; }
    .cl-meta { font-size: 12px; color: var(--text-sub); font-family: 'DM Mono', monospace;
        margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .cl-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--text-sub); }

    .cl-actions { display: flex; gap: 8px; margin-top: 14px; }
    .cl-btn-sm { padding: 6px 14px; font-size: 12.5px; }
    .cl-btn-danger { background: rgba(248,113,113,.12); color: #c0392b; }
    .cl-btn-danger:hover { background: rgba(248,113,113,.22); }
    .cl-btn-link { background: none; border: none; color: var(--surface); font-family: inherit;
        font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 6px 4px; }

    /* Filters */
    .cl-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 22px; }
    .cl-pill { padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
        text-decoration: none; border: 1px solid #d5dbe3; color: var(--text); background: #fff; }
    .cl-pill.active { background: var(--surface); color: #fff; border-color: var(--surface); }

    /* Replies */
    .cl-replies { margin-top: 16px; border-top: 1px solid #eef1f5; padding-top: 14px; }
    .cl-reply { display: flex; gap: 10px; margin-bottom: 12px; }
    .cl-reply-av { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: #0d0f12; }
    .cl-reply-bubble { background: var(--bg); border-radius: 9px; padding: 8px 12px; flex: 1; }
    .cl-reply-name { font-size: 12px; font-weight: 700; }
    .cl-reply-body { font-size: 13.5px; line-height: 1.5; white-space: pre-wrap; margin-top: 2px; }
    .cl-reply-meta { font-size: 11px; color: var(--text-sub); font-family: 'DM Mono', monospace; font-weight: 400; }

    .cl-reply-form { display: flex; gap: 8px; margin-top: 10px; }
    .cl-reply-form .cl-field { flex: 1; min-height: 0; padding: 9px 12px; }

    .cl-empty { text-align: center; color: var(--text-sub); padding: 48px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }

    .cl-hidden { display: none; }
    .cl-edit-form { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #dfe4ea; }
</style>

<div class="page-header">
    <div class="page-title">CLASSIFIEDS</div>
    <div class="page-sub">roommates · buy/sell · books · dorm furniture — across all 26 campuses</div>
</div>

<div class="cl-wrap">

    @if(session('success'))
        <div class="alert alert-success cl-alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error cl-alert">{{ $errors->first() }}</div>
    @endif

    {{-- ── New post ─────────────────────────────────────────── --}}
    <div class="cl-card-lead">
        <button type="button" class="cl-collapse-btn" onclick="clToggle('new-post')">
            <span>+ Post a classified</span>
            <span id="new-post-caret">▾</span>
        </button>

        <form method="POST" action="{{ route('classifieds.store') }}" id="new-post"
              class="{{ $errors->any() && old('title') ? '' : 'cl-hidden' }}" style="margin-top:16px;">
            @csrf
            <div class="cl-row" style="margin-bottom:12px;">
                <select name="category" class="cl-field" required>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="number" name="price" step="0.01" min="0" class="cl-field"
                       placeholder="Price (optional)" value="{{ old('price') }}">
            </div>
            <input type="text" name="title" class="cl-field" style="margin-bottom:12px;"
                   placeholder="Title — e.g. Looking for a roommate near North Campus"
                   maxlength="120" value="{{ old('title') }}" required>
            <textarea name="body" class="cl-field" style="margin-bottom:12px;"
                      placeholder="Describe what you're posting…" maxlength="4000" required>{{ old('body') }}</textarea>
            <button type="submit" class="btn btn-primary">Post</button>
        </form>
    </div>

    {{-- ── Filters ──────────────────────────────────────────── --}}
    <div class="cl-filters">
        <a href="{{ route('pages.classifieds') }}"
           class="cl-pill {{ !$filters['category'] ? 'active' : '' }}">All</a>
        @foreach($categories as $value => $label)
            <a href="{{ route('pages.classifieds', array_filter(['category' => $value, 'campus' => $filters['campus'], 'q' => $filters['search']])) }}"
               class="cl-pill {{ $filters['category'] === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('pages.classifieds') }}" class="cl-row" style="margin-bottom:24px;">
        @if($filters['category'])<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif
        <input type="text" name="q" class="cl-field" placeholder="Search titles & posts…"
               value="{{ $filters['search'] }}">
        <select name="campus" class="cl-field" onchange="this.form.submit()">
            <option value="">All campuses</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus }}" {{ $filters['campus'] === $campus ? 'selected' : '' }}>{{ $campus }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary cl-btn-sm">Search</button>
    </form>

    {{-- ── Feed ─────────────────────────────────────────────── --}}
    @forelse($classifieds as $post)
        <div class="cl-card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                <span class="cl-badge">{{ $post->categoryLabel() }}</span>
                @if(!is_null($post->price))
                    <span class="cl-price">${{ number_format($post->price, 2) }}</span>
                @endif
            </div>

            <div class="cl-title">{{ $post->title }}</div>
            <div class="cl-body">{{ $post->body }}</div>

            <div class="cl-meta">
                <span>{{ $post->user->name ?? 'Unknown' }}</span>
                <span class="cl-dot"></span>
                <span>{{ $post->campus ?? '—' }}</span>
                <span class="cl-dot"></span>
                <span>{{ $post->created_at->diffForHumans() }}</span>
                <span class="cl-dot"></span>
                <span>{{ $post->replies_count }} {{ Str::plural('reply', $post->replies_count) }}</span>
            </div>

            @if($post->user_id === auth()->id())
                <div class="cl-actions">
                    <button type="button" class="btn btn-ghost cl-btn-sm" onclick="clToggle('edit-{{ $post->id }}')">Edit</button>
                    <form method="POST" action="{{ route('classifieds.destroy', $post) }}"
                          onsubmit="return confirm('Delete this post?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn cl-btn-sm cl-btn-danger">Delete</button>
                    </form>
                </div>

                {{-- Inline edit --}}
                <form method="POST" action="{{ route('classifieds.update', $post) }}"
                      id="edit-{{ $post->id }}" class="cl-edit-form cl-hidden">
                    @csrf @method('PUT')
                    <div class="cl-row" style="margin-bottom:10px;">
                        <select name="category" class="cl-field" required>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ $post->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="price" step="0.01" min="0" class="cl-field"
                               placeholder="Price (optional)" value="{{ $post->price }}">
                    </div>
                    <input type="text" name="title" class="cl-field" style="margin-bottom:10px;"
                           maxlength="120" value="{{ $post->title }}" required>
                    <textarea name="body" class="cl-field" style="margin-bottom:10px;"
                              maxlength="4000" required>{{ $post->body }}</textarea>
                    <button type="submit" class="btn btn-primary cl-btn-sm">Save changes</button>
                </form>
            @endif

            {{-- ── Replies ── --}}
            <div class="cl-replies">
                @foreach($post->replies as $reply)
                    <div class="cl-reply">
                        <div class="cl-reply-av">{{ strtoupper(substr($reply->user->name ?? '?', 0, 1)) }}</div>
                        <div class="cl-reply-bubble">
                            <div class="cl-reply-name">{{ $reply->user->name ?? 'Unknown' }}
                                <span class="cl-reply-meta">· {{ $reply->user->campus ?? '' }} · {{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="cl-reply-body">{{ $reply->body }}</div>
                            @if($reply->user_id === auth()->id())
                                <form method="POST" action="{{ route('classifieds.replies.destroy', $reply) }}"
                                      onsubmit="return confirm('Remove this reply?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="cl-btn-link" style="color:#c0392b;">Remove</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('classifieds.replies.store', $post) }}" class="cl-reply-form">
                    @csrf
                    <input type="text" name="body" class="cl-field" placeholder="Reply…" maxlength="2000" required>
                    <button type="submit" class="btn btn-primary cl-btn-sm">Send</button>
                </form>
            </div>
        </div>
    @empty
        <div class="cl-empty">No classifieds yet — be the first to post.</div>
    @endforelse

    <div style="margin-top:20px;">
        {{ $classifieds->links() }}
    </div>

</div>

<script>
    function clToggle(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('cl-hidden');
        var caret = document.getElementById(id + '-caret');
        if (caret) caret.textContent = el.classList.contains('cl-hidden') ? '▾' : '▴';
    }
</script>
@endsection
