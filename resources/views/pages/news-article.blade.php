@extends('layouts.app')
@section('title', $post->title)

@section('content')
<style>
    .na-wrap { max-width: 720px; }
    .na-back { display: inline-block; font-size: 13px; color: var(--text-sub);
        text-decoration: none; margin-bottom: 16px; font-family: 'DM Mono', monospace; }
    .na-back:hover { color: var(--surface); }

    .na-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius); padding: 30px; }
    .na-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; padding: 3px 9px; border-radius: 20px;
        background: rgba(15,76,129,.09); color: var(--surface); }
    .na-title { font-size: 28px; font-weight: 900; line-height: 1.2; margin: 14px 0 10px; }
    .na-meta { font-size: 13px; color: var(--text-sub); font-family: 'DM Mono', monospace;
        padding-bottom: 18px; border-bottom: 1px solid #eef1f5; margin-bottom: 20px; }
    .na-body { font-size: 16px; line-height: 1.7; color: #2a3340; white-space: pre-wrap; }

    .na-owner { display: flex; gap: 8px; margin-top: 24px; padding-top: 18px; border-top: 1px solid #eef1f5; }
    .na-btn-del { background: rgba(248,113,113,.12); color: #c0392b; }
    .na-btn-del:hover { background: rgba(248,113,113,.22); }

    .na-edit { margin-top: 20px; }
    .na-field { margin-bottom: 14px; }
    .na-field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
    .na-field input, .na-field select, .na-field textarea { width: 100%; padding: 10px 14px;
        background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; }
    .na-field textarea { resize: vertical; min-height: 200px; }
    .chat-hidden { display: none; }
</style>

<div class="na-wrap">
    <a href="{{ route('pages.news') }}" class="na-back">← All news</a>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    <div class="na-card">
        <span class="na-badge">{{ $post->categoryLabel() }}</span>
        <h1 class="na-title">{{ $post->title }}</h1>
        <div class="na-meta">
            By {{ $post->author->name ?? 'Unknown' }} · {{ $post->campus ?? '—' }} · {{ $post->created_at->format('M j, Y · g:ia') }}
        </div>
        <div class="na-body">{{ $post->body }}</div>

        @if($isOwner)
            <div class="na-owner">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('na-edit').classList.toggle('chat-hidden')">Edit</button>
                <form method="POST" action="{{ route('news.destroy', $post) }}"
                      onsubmit="return confirm('Delete this news post?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn na-btn-del">Delete</button>
                </form>
            </div>

            <form method="POST" action="{{ route('news.update', $post) }}" id="na-edit" class="na-edit chat-hidden">
                @csrf @method('PUT')
                <div class="na-field">
                    <label>Headline</label>
                    <input type="text" name="title" maxlength="180" value="{{ $post->title }}" required>
                </div>
                <div class="na-field">
                    <label>Category</label>
                    <select name="category" required>
                        @foreach(\App\Models\NewsPost::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" {{ $post->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="na-field">
                    <label>Story</label>
                    <textarea name="body" maxlength="8000" required>{{ $post->body }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </form>
        @endif
    </div>
</div>
@endsection
