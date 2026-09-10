@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<style>
    .set-wrap { max-width: 640px; }
    .set-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 24px; margin-bottom: 20px; }
    .set-card h3 { font-size: 13px; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 18px; }

    .set-field { margin-bottom: 16px; }
    .set-field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
    .set-input { width: 100%; padding: 10px 14px; background: var(--bg);
        border: 1px solid var(--border); border-radius: 7px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .set-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
    textarea.set-input { resize: vertical; min-height: 90px; }

    .avatar-row { display: flex; align-items: center; gap: 18px; margin-bottom: 22px; }
    .avatar-preview { width: 74px; height: 74px; border-radius: 50%; flex-shrink: 0;
        object-fit: cover; background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; font-weight: 700; color: #0d0f12; overflow: hidden; }
    .avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-hint { font-size: 12px; color: var(--text-sub); margin-top: 6px; font-family: 'DM Mono', monospace; }
    .file-btn { display: inline-block; padding: 8px 16px; border-radius: 7px; cursor: pointer;
        background: var(--border); color: #fff; font-size: 13px; font-weight: 500; }
    .file-btn:hover { background: #252932; }
    input[type=file] { display: none; }

    .set-links { display: flex; flex-direction: column; gap: 10px; }
    .set-link { display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px; border-radius: 8px; background: var(--bg);
        text-decoration: none; color: var(--text); font-size: 14px; font-weight: 500; border: none;
        width: 100%; font-family: inherit; cursor: pointer; text-align: left; }
    .set-link:hover { background: #e7eaef; }
    .set-link.danger { color: #c0392b; }

    .danger-zone { margin-top: 8px; padding-top: 16px; border-top: 1px dashed #e2c3c3; }
    .chat-hidden { display: none; }
</style>

<div class="page-header">
    <div class="page-title">SETTINGS</div>
    <div class="page-sub">your profile — how other campuses see you</div>
</div>

<div class="set-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Profile ─────────────────────────────────────────── --}}
    <form class="set-card" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <h3>Profile</h3>

        <div class="avatar-row">
            <div class="avatar-preview" id="avatar-preview">
                @if($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="avatar" id="avatar-img">
                @else
                    <span id="avatar-initial">{{ $user->initial() }}</span>
                @endif
            </div>
            <div>
                <label class="file-btn" for="avatar-input">Upload avatar</label>
                <input type="file" name="avatar" id="avatar-input" accept="image/png,image/jpeg,image/webp">
                <div class="avatar-hint">JPG, PNG or WEBP · up to 4 MB</div>
            </div>
        </div>

        <div class="set-field">
            <label>Name</label>
            <input type="text" name="name" class="set-input" value="{{ old('name', $user->name) }}" maxlength="255" required>
        </div>

        <div class="set-field">
            <label>Campus / School</label>
            <select name="campus" class="set-input" required>
                @foreach($campuses as $campus)
                    <option value="{{ $campus }}" {{ old('campus', $user->campus) === $campus ? 'selected' : '' }}>{{ $campus }}</option>
                @endforeach
            </select>
        </div>

        <div class="set-field">
            <label>Major</label>
            <input type="text" name="major" class="set-input" value="{{ old('major', $user->major) }}"
                   placeholder="e.g. Computer Science" maxlength="120">
        </div>

        <div class="set-field">
            <label>Bio &amp; interests</label>
            <textarea name="bio" class="set-input" maxlength="1000"
                      placeholder="Tell other students about yourself…">{{ old('bio', $user->bio) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>

    {{-- ── Account ─────────────────────────────────────────── --}}
    <div class="set-card">
        <h3>Account</h3>
        <div class="set-links">
            <a href="{{ route('help.ticket') }}" class="set-link">Help ticket <span>→</span></a>
            <a href="{{ route('pages.change-password') }}" class="set-link">Change password <span>→</span></a>

            <form method="POST" action="{{ route('user.logout') }}">
                @csrf
                <button type="submit" class="set-link">Log out <span>→</span></button>
            </form>

            <div class="danger-zone">
                <button type="button" class="set-link danger" onclick="document.getElementById('delete-form').classList.toggle('chat-hidden')">
                    Delete account <span>⚠</span>
                </button>
                <form id="delete-form" method="POST" action="{{ route('profile.destroy') }}"
                      class="chat-hidden" style="margin-top:12px;"
                      onsubmit="return confirm('This permanently deletes your account and all your posts. Continue?')">
                    @csrf @method('DELETE')
                    <div class="set-field">
                        <label>Confirm your password to delete</label>
                        <input type="password" name="password" class="set-input" placeholder="Your password" required>
                    </div>
                    <button type="submit" class="btn" style="background:#c0392b;color:#fff;">Permanently delete account</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    // Live avatar preview
    document.getElementById('avatar-input').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        const box = document.getElementById('avatar-preview');
        box.innerHTML = '<img src="' + url + '" alt="avatar preview">';
    });
</script>
@endsection
