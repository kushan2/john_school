<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suny Chat | Password Change</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #F5F5F5; --surface: #F1F1F1; --border: #1e232b; --accent: #4f8ef7; --text: #e2e6f0; --text-sub: #7c8798; --danger: #f87171; --success: #6ee7b7; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .wrap { width: 100%; max-width: 380px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
        .card-title { font-size: 16px; font-weight: 500; color:black; margin-bottom: 8px; }
        .card-desc { font-size: 13px; color: var(--text-sub); margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 11px; font-weight: 500; letter-spacing: .08em; text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
        input { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: #0f4c81; font-family: inherit; font-size: 14px; outline: none; transition: border-color .15s, box-shadow .15s; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
        .btn { width: 100%; padding: 11px; border-radius: 7px; border: none; background: var(--accent); color: #fff; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; transition: opacity .15s; margin-top: 6px; }
        .btn:hover { opacity: .88; }
        .alert { padding: 11px 14px; border-radius: 7px; font-size: 13px; margin-bottom: 18px; border: 1px solid; }
        .alert-error { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.25); color: var(--danger); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="card-title">Set new password</div>
        <div class="card-desc">Choose a strong password for your account.</div>

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Min. 8 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</div>
</body>
</html>
