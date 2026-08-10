<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suny Chat | Password Reset</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #F5F5F5; --surface: #F1F1F1; --border: #1e232b; --accent: #4f8ef7; --text: #e2e6f0; --text-sub: #7c8798; --danger: #f87171; --success: #6ee7b7; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .wrap { width: 100%; max-width: 380px; }
        .back { display: inline-flex; align-items: center; gap: 6px; color: var(--text-sub); font-size: 13px; text-decoration: none; margin-bottom: 28px; }
        .back:hover { color: #0f4c81; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
        .card-title { font-size: 16px; font-weight: 500; color:black; margin-bottom: 8px; }
        .card-desc { font-size: 14px; color: #0f4c81; margin-bottom: 24px; line-height: 1.6; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; color: #000000; margin-bottom: 7px; }
        input[type=email] { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 7px; color: #0f4c81; font-family: inherit; font-size: 14px; outline: none; transition: border-color .15s, box-shadow .15s; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
        .btn { width: 100%; padding: 11px; border-radius: 7px; border: none; background: #0f4c81; color: #fff; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
        .btn:hover { opacity: .88; }
        .alert { padding: 11px 14px; border-radius: 7px; font-size: 13px; margin-bottom: 18px; border: 1px solid; }
        .alert-success { background: rgba(110,231,183,.08); border-color: rgba(110,231,183,.25); color: var(--success); }
        .alert-error { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.25); color: var(--danger); }
    </style>
</head>
<body>
<div class="wrap">
    <a href="{{ route('login') }}" class="back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to login
    </a>

    <div class="card">
        <div class="card-title">Reset your password</div>
        <div class="card-desc">Enter the email address associated with your server and an email with a password reset link will be sent (please check junk-folder and allow future emails).</div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@address.edu" required autofocus>
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
    </div>
</div>
</body>
</html>
