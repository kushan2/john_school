<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suny Chat | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #F5F5F5; --surface: #F1F1F1; --border: #1e232b;
            --accent: #4f8ef7; --text: #e2e6f0; --text-sub: #7c8798;
            --danger: #f87171; --success: #6ee7b7;
        }
        
body { 
    font-family: 'DM Sans', sans-serif; 
    background: var(--bg); 
    color: var(--text); 
    min-height: 100vh; 
    display: flex; align-items: center; 
    justify-content: center; 
    padding: 24px; 
  
    
}
        
    .wrap { width: 100%; max-width: 380px; }
        .logo { text-align: center; margin-bottom: 36px; }
        .logo-dot-row { display: flex; justify-content: center; gap: 6px; margin-bottom: 14px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot-1 { background: #4f8ef7; box-shadow: 0 0 8px #4f8ef7; }
        .dot-2 { background: #6ee7b7; box-shadow: 0 0 8px #6ee7b7; animation-delay: .1s; }
        .dot-3 { background: #a78bfa; box-shadow: 0 0 8px #a78bfa; animation-delay: .2s; }
        .logo-title { font-size: 20px; font-weight: 900; color: #0f4c81; letter-spacing: .06em; text-transform: uppercase; }
        .logo-sub { font-size: 12px; color: var(--text-sub); margin-top: 4px; font-family: 'DM Mono', monospace; }
        
        
        
.card { 
    background: var(--surface); 
    border: 1px solid var(--border); 
    border-radius: 12px; 
    padding: 32px; 
}
        
        
.card-title { font-size: 16px; font-weight: 500; color:black; margin-bottom: 24px; }

.form-group { margin-bottom: 16px; }
    label { display: block; font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; color: #000000; margin-bottom: 7px; }
        
    input[type=email], input[type=password], input[type=text] {
            width: 100%; padding: 10px 14px;
            background: var(--bg); border: 1px solid var(--border); 
            border-radius: 7px; color: #0f4c81; font-family: inherit; font-size: 14px;
            outline: none; transition: border-color .15s, box-shadow .15s;
        }
        
    input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
        .input-error { border-color: var(--danger) !important; }
        .error-msg { font-size: 12px; color: var(--danger); margin-top: 5px; }
        .row-between { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .check-label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #000000; cursor: pointer; }
        input[type=checkbox] { accent-color: var(--accent); }
        .link { font-size: 14px; color: #0f4c81; text-decoration: none; }
        .link:hover { text-decoration: underline; }
        .btn { width: 100%; padding: 11px; border-radius: 7px; border: none; background: #0f4c81; color: #fff; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; transition: opacity .15s, transform .1s; }
        .btn:hover { opacity: .88; }
        .btn:active { transform: scale(.99); }
        .alert { padding: 11px 14px; border-radius: 7px; font-size: 13px; margin-bottom: 18px; border: 1px solid; }
        .alert-success { background: rgba(110,231,183,.08); border-color: rgba(110,231,183,.25); color: var(--success); }
        .alert-error   { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.25); color: var(--danger); }
    
</style>
</head>






<body>
<div class="wrap">
    <div class="logo">
        <!--<div class="logo-dot-row">
            <div class="dot dot-1"></div>
            <div class="dot dot-2"></div>
            <div class="dot dot-3"></div>
        </div>-->
        <div class="logo-title">SUNY CHAT LOGIN</div>
        <!--<div class="logo-sub">Secure Access Only</div>-->
    </div>

    <div class="card">
        <div class="card-title"><a href="" class="link">MANAGE YOUR SERVER</a> 
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('user.login') }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@address.edu" class="{{ $errors->has('email') ? 'input-error' : '' }}" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="•••••••••••" required>
            </div>
            <div class="row-between">
                <label class="check-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
<br>
<div><a href="{{ route('register') }}" class="btn" style="display:block;text-align:center;text-decoration:none;">REGISTER</a></div>


</div>
</div>
</body>
</html>
