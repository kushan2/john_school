<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <!--<link rel="icon" href="{{ asset('logo.png') }}" type="image/x-icon">-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #F1F1F1;
            --surface:  #0f4c81;
            --border:   #1e232b;
            --accent:   #4f8ef7;
            --accent2:  #6ee7b7;
            --muted:    #4a5260;
            --text:     #001F3F;
            --text-sub: #7c8798;
            --danger:   #f87171;
            --success:  #6ee7b7;
            --sidebar-w: 220px;
            --radius:   10px;
        }

        html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 15px; }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed; inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            z-index: 100;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
        }
        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .brand-dot {
            width: 9px; height: 9px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            flex-shrink: 0;
        }
        .brand-name {
            font-size: 14px; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: var(--text); color: white;
        }
        .nav-section { padding: 20px 14px 8px; }
        .nav-label { font-size: 14px; letter-spacing: .12em; text-transform: uppercase; color: black; padding: 0 10px 8px; font-weight: 500; }
        .nav-item {
            display: flex; align-items: center; gap: 11px;
            padding: 9px 10px; border-radius: 7px; margin-bottom: 2px;
            color: white; text-decoration: none; font-size: 14px; font-weight: 900;
            transition: background .15s, color .15s;
        }
        .nav-item:hover { background: #F1F1F1; color: #0f4c81; }
        .nav-item.active { background: #F1F1F1; color: #0f4c81; }
        .nav-item svg { flex-shrink: 0; }
        .sidebar-footer {
            margin-top: auto; padding: 16px 14px;
            border-top: 1px solid var(--border);
        }
        .user-pill {
            display: flex; align-items: center; gap: 10px;
            padding: 10px; border-radius: 8px; background: var(--border);
        }
        .avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; color: #0d0f12; flex-shrink: 0;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--text-sub); }
        .logout-btn {
            background: none; border: none; cursor: pointer;
            color: var(--muted); padding: 4px; border-radius: 5px;
            display: flex; align-items: center;
            transition: color .15s;
        }
        .logout-btn:hover { color: var(--danger); }

        
        


/* ── Top bar (mobile) ── */
        .topbar {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; height: 56px;
            background: var(--surface); border-bottom: 1px solid var(--border);
            align-items: center; padding: 0 18px; z-index: 99; color: white;
            gap: 14px;
        }
        .hamburger {
            background: none; border: none; cursor: pointer;
            color: var(--text); padding: 6px; display: flex; flex-direction: column;
            gap: 5px; border-radius: 6px; color: white;
        }
        .hamburger span {
            display: block; width: 20px; height: 2px;
            background: currentColor; border-radius: 2px;
            transition: transform .25s, opacity .25s;
        }
        .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
        .topbar-brand { font-size: 14px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }

        /* ── Overlay ── */
        .overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.55); z-index: 99;
            backdrop-filter: blur(2px);
        }
        .overlay.visible { display: block; }

        





/* ── Main ── */
.main { margin-left: var(--sidebar-w); min-height: 100vh; padding: 36px 40px; }
.page-header { margin-bottom: 32px; }
.page-title { font-size: 25px; font-weight: 900; letter-spacing: -.01em; }
.page-sub { font-size: 15px; color: var(--text-sub); margin-top: 4px; font-family: 'DM Mono', monospace; }

       
/* ── Forms ── */
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase; color: var(--text-sub); margin-bottom: 7px; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%; padding: 10px 14px;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 7px; color: var(--text); font-family: inherit; font-size: 14px;
            outline: none; transition: border-color .15s, box-shadow .15s;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 7px; border: none;
            font-family: inherit; font-size: 13.5px; font-weight: 500;
            cursor: pointer; text-decoration: none; transition: opacity .15s, transform .1s;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: var(--surface); color: #fff; }
        .btn-primary:hover { opacity: .88; }
        .btn-ghost { background: var(--border); color: white; }
        .btn-ghost:hover { background: #252932; }

        .alert {
            padding: 11px 16px; border-radius: 7px; font-size: 13px; margin-bottom: 20px;
            border: 1px solid;
        }
        .alert-success { background: rgba(110,231,183,.08); border-color: rgba(110,231,183,.25); color: var(--success); }
        .alert-error   { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.25); color: var(--danger); }




/* ── Mobile icon bars (hidden on desktop) ── */
.mobile-nav { display: none; }

/* ── Mobile ── */
@media (max-width: 768px) {
    /* Replace hamburger sidebar with fixed icon bars */
    .sidebar, .topbar, .overlay { display: none !important; }

    .main { margin-left: 0; padding: 78px 16px 88px; }

    .mobile-nav {
        display: flex; align-items: stretch; justify-content: space-around;
        position: fixed; left: 0; right: 0; height: 64px;
        background: var(--surface); z-index: 120;
    }
    .mobile-nav.top    { top: 0;    border-bottom: 1px solid var(--border); }
    .mobile-nav.bottom { bottom: 0; border-top: 1px solid var(--border); }

    .mobile-nav a {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 4px;
        color: #cdd8ea; text-decoration: none;
        font-size: 10px; font-weight: 600; letter-spacing: .03em; text-transform: uppercase;
        padding: 4px 2px; text-align: center;
    }
    .mobile-nav a svg { width: 22px; height: 22px; flex-shrink: 0; }
    .mobile-nav a.active { color: #fff; }
    .mobile-nav a.active svg { stroke: var(--accent2); }
    }


</style>
</head>






<body>

{{-- Mobile icon nav — top 3 icons (edit the 6 links here to change which pages show) --}}
<nav class="mobile-nav top">
    <a href="{{ route('pages.dashboard') }}" class="{{ request()->routeIs('pages.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Chat
    </a>
    <a href="{{ route('pages.messages') }}" class="{{ request()->routeIs('pages.messages') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        Inbox
    </a>
    <a href="{{ route('pages.connections') }}" class="{{ request()->routeIs('pages.connections') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Connect
    </a>
</nav>

{{-- Mobile icon nav — bottom 3 icons --}}
<nav class="mobile-nav bottom">
    <a href="{{ route('pages.events') }}" class="{{ request()->routeIs('pages.events') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Events
    </a>
    <a href="{{ route('pages.groups') }}" class="{{ request()->routeIs('pages.groups') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>
        Groups
    </a>
    <a href="{{ route('pages.profile') }}" class="{{ request()->routeIs('pages.profile') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>
</nav>

{{-- Mobile top bar --}}
<header class="topbar">
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>
    <span class="topbar-brand">Server Panel</span>
</header>

<div class="overlay" id="overlay"></div>




{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">
<div class="sidebar-brand">
<div class="brand-dot"></div>
<span class="brand-name">Server Panel</span>
</div>


<nav class="nav-section">
<div class="nav-label">My Server</div>
<a href="{{ route('pages.dashboard') }}" class="nav-item {{ request()->routeIs('pages.dashboard') ? 'active' : '' }}">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    OPEN CHAT
</a>



<a href="{{ route('pages.messages') }}" class="nav-item {{ request()->routeIs('pages.messages') ? 'active' : '' }}">
INBOX
</a>

<a href="{{ route('pages.media') }}" class="nav-item {{ request()->routeIs('pages.media') ? 'active' : '' }}">
MEDIA & FILES
</a>




<a href="{{ route('pages.profile') }}" class="nav-item {{ request()->routeIs('pages.profile') ? 'active' : '' }}">
SETTINGS
</a>

</nav>

    
    



<nav class="nav-section">
<div class="nav-label">Campus Connect</div>


<a href="{{ route('pages.connections') }}" class="nav-item {{ request()->routeIs('pages.connections') ? 'active' : '' }}">
CONNECTIONS
</a>

<a href="{{ route('pages.events') }}" class="nav-item {{ request()->routeIs('pages.events') ? 'active' : '' }}">
EVENTS
</a>

<a href="{{ route('pages.groups') }}" class="nav-item {{ request()->routeIs('pages.groups') ? 'active' : '' }}">
GROUPS
</a>

<a href="{{ route('pages.classifieds') }}" class="nav-item {{ request()->routeIs('pages.classifieds') ? 'active' : '' }}">
ROOMATES
</a>






<!--<a href="{{ route('pages.change-password') }}" class="nav-item {{ request()->routeIs('pages.change-password') ? 'active' : '' }}">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
Change Password
</a>-->
</nav>






<div class="sidebar-footer">
<div class="user-pill">
<div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
<div class="user-info">
<div class="user-name">{{ Auth::user()->name }}</div>
<div class="user-role">Sign-out</div>
</div>
    <form method="POST" action="{{ route('user.logout') }}">
                @csrf
    <button class="logout-btn" title="Sign out">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </button>
</form>
</div>
</div>
</aside>

{{-- Main content --}}
<main class="main">
    @yield('content')
</main>

<script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');

    function toggleMenu(open) {
        sidebar.classList.toggle('open', open);
        hamburger.classList.toggle('open', open);
        overlay.classList.toggle('visible', open);
    }

    hamburger.addEventListener('click', () => toggleMenu(!sidebar.classList.contains('open')));
    overlay.addEventListener('click',   () => toggleMenu(false));
    document.querySelectorAll('.nav-item').forEach(el => el.addEventListener('click', () => toggleMenu(false)));
</script>
</body>
</html>
