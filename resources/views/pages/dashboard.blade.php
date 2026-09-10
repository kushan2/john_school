@extends('layouts.app')
@section('title', 'Open Chat')

@section('content')
<style>
    .chat-shell { display: flex; flex-direction: column;
        height: calc(100vh - 200px); min-height: 380px; max-width: 860px; }

    .chat-stream { flex: 1; overflow-y: auto; padding: 6px 4px 10px;
        display: flex; flex-direction: column; gap: 14px; }

    .chat-loading { text-align: center; color: var(--text-sub);
        font-family: 'DM Mono', monospace; font-size: 13px; padding: 24px; }

    /* Message row */
    .msg { display: flex; gap: 10px; align-items: flex-start; max-width: 78%; }
    .msg.mine { align-self: flex-end; flex-direction: row-reverse; }

    .msg-av { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: #0d0f12; }

    .msg-main { min-width: 0; }
    .msg-head { display: flex; gap: 7px; align-items: baseline; margin-bottom: 3px; flex-wrap: wrap; }
    .msg.mine .msg-head { justify-content: flex-end; }
    .msg-name { font-size: 12.5px; font-weight: 700; color: var(--text); }
    .msg-campus { font-size: 10.5px; font-weight: 600; letter-spacing: .03em;
        text-transform: uppercase; color: var(--surface);
        background: rgba(15,76,129,.09); padding: 1px 7px; border-radius: 20px; }
    .msg-time { font-size: 11px; color: var(--text-sub); font-family: 'DM Mono', monospace; }

    .msg-bubble { display: inline-block; padding: 9px 13px; border-radius: 12px;
        font-size: 14px; line-height: 1.5; white-space: pre-wrap; word-break: break-word;
        background: #fff; border: 1px solid #e2e6ec; color: #2a3340; }
    .msg.mine .msg-bubble { background: var(--surface); color: #fff; border-color: var(--surface); }

    /* Reactions */
    .msg-reactions { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; align-items: center; }
    .msg.mine .msg-reactions { justify-content: flex-end; }
    .react-chip { font-size: 12.5px; padding: 2px 8px; border-radius: 20px; cursor: pointer;
        border: 1px solid #d5dbe3; background: #fff; font-family: inherit; line-height: 1.4;
        transition: background .12s, border-color .12s; }
    .react-chip.active { background: rgba(79,142,247,.14); border-color: var(--accent); font-weight: 600; }
    .react-chip:hover { background: rgba(79,142,247,.08); }

    .react-add { position: relative; }
    .react-add-btn { width: 24px; height: 24px; border-radius: 50%; cursor: pointer;
        border: 1px solid #d5dbe3; background: #fff; color: var(--text-sub);
        font-size: 13px; line-height: 1; display: flex; align-items: center; justify-content: center; }
    .react-add-btn:hover { border-color: var(--accent); color: var(--accent); }
    .react-palette { position: absolute; bottom: 30px; left: 0; z-index: 10;
        background: #fff; border: 1px solid #e2e6ec; border-radius: 22px;
        padding: 5px 8px; display: flex; gap: 4px; box-shadow: 0 6px 20px rgba(0,0,0,.12); }
    .msg.mine .react-palette { left: auto; right: 0; }
    .react-palette button { font-size: 17px; background: none; border: none; cursor: pointer;
        padding: 2px 4px; border-radius: 8px; line-height: 1; }
    .react-palette button:hover { background: var(--bg); transform: scale(1.15); }
    .chat-hidden { display: none; }

    /* Composer */
    .chat-composer { display: flex; gap: 10px; padding-top: 14px; margin-top: 6px;
        border-top: 1px solid #e2e6ec; }
    .chat-composer input { flex: 1; padding: 11px 15px; background: #fff;
        border: 1px solid var(--border); border-radius: 24px; color: var(--text);
        font-family: inherit; font-size: 14px; outline: none; }
    .chat-composer input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
    .chat-send { border: none; border-radius: 24px; padding: 0 22px; cursor: pointer;
        background: var(--surface); color: #fff; font-family: inherit; font-size: 14px; font-weight: 600; }
    .chat-send:disabled { opacity: .5; cursor: default; }

    @media (max-width: 768px) {
        .chat-shell { height: calc(100vh - 250px); }
        .msg { max-width: 88%; }
    }
</style>

<div class="page-header">
    <div class="page-title">OPEN CHAT</div>
    <div class="page-sub">one room · every campus · say hi 👋</div>
</div>

<div class="chat-shell">
    <div class="chat-stream" id="chat-stream">
        <div class="chat-loading" id="chat-loading">Loading the conversation…</div>
    </div>

    <form class="chat-composer" id="chat-form" autocomplete="off">
        <input type="text" id="chat-input" placeholder="Message all campuses…" maxlength="1000" required>
        <button type="submit" class="chat-send" id="chat-send">Send</button>
    </form>
</div>

<script>
(function () {
    const EMOJIS    = @json($emojis);
    const FETCH_URL = @json(route('chat.fetch'));
    const STORE_URL = @json(route('chat.store'));
    const REACT_BASE = @json(url('chat/messages'));
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    const stream  = document.getElementById('chat-stream');
    const loading = document.getElementById('chat-loading');
    const form    = document.getElementById('chat-form');
    const input   = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send');

    const nodes = {};   // id -> message row element
    let lastId = 0;
    let polling = false;

    function nearBottom() {
        return stream.scrollHeight - stream.scrollTop - stream.clientHeight < 60;
    }
    function scrollToBottom() { stream.scrollTop = stream.scrollHeight; }

    async function api(url, method, body) {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            body: body ? JSON.stringify(body) : undefined,
        });
        if (!res.ok) throw new Error('Request failed: ' + res.status);
        return res.json();
    }

    function renderReactions(container, m) {
        container.innerHTML = '';

        Object.keys(m.reactions).forEach(function (emoji) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'react-chip' + (m.reacted.indexOf(emoji) !== -1 ? ' active' : '');
            chip.textContent = emoji + ' ' + m.reactions[emoji];
            chip.addEventListener('click', function () { react(m.id, emoji); });
            container.appendChild(chip);
        });

        const add = document.createElement('div');
        add.className = 'react-add';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'react-add-btn';
        btn.textContent = '☺';
        const palette = document.createElement('div');
        palette.className = 'react-palette chat-hidden';
        EMOJIS.forEach(function (emoji) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = emoji;
            b.addEventListener('click', function () {
                palette.classList.add('chat-hidden');
                react(m.id, emoji);
            });
            palette.appendChild(b);
        });
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            document.querySelectorAll('.react-palette').forEach(function (p) {
                if (p !== palette) p.classList.add('chat-hidden');
            });
            palette.classList.toggle('chat-hidden');
        });
        add.appendChild(btn);
        add.appendChild(palette);
        container.appendChild(add);
    }

    function buildNode(m) {
        const row = document.createElement('div');
        row.className = 'msg' + (m.mine ? ' mine' : '');

        const av = document.createElement('div');
        av.className = 'msg-av';
        av.style.overflow = 'hidden';
        if (m.avatar) {
            const img = document.createElement('img');
            img.src = m.avatar;
            img.alt = '';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
            av.appendChild(img);
        } else {
            av.textContent = m.initial;
        }

        const main = document.createElement('div');
        main.className = 'msg-main';

        const head = document.createElement('div');
        head.className = 'msg-head';
        const name = document.createElement('span');
        name.className = 'msg-name';
        name.textContent = m.name;
        head.appendChild(name);
        if (m.campus) {
            const campus = document.createElement('span');
            campus.className = 'msg-campus';
            campus.textContent = m.campus;
            head.appendChild(campus);
        }
        const time = document.createElement('span');
        time.className = 'msg-time';
        time.textContent = m.time;
        head.appendChild(time);

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.textContent = m.body;

        const reactions = document.createElement('div');
        reactions.className = 'msg-reactions';

        main.appendChild(head);
        main.appendChild(bubble);
        main.appendChild(reactions);
        row.appendChild(av);
        row.appendChild(main);
        renderReactions(reactions, m);
        return row;
    }

    function upsert(m) {
        if (nodes[m.id]) {
            renderReactions(nodes[m.id].querySelector('.msg-reactions'), m);
        } else {
            const node = buildNode(m);
            nodes[m.id] = node;
            stream.appendChild(node);
            if (m.id > lastId) lastId = m.id;
        }
    }

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const data = await api(FETCH_URL + '?after=' + lastId, 'GET');
            if (data.messages.length) {
                const stick = nearBottom();
                data.messages.forEach(upsert);
                if (loading) loading.remove();
                if (stick) scrollToBottom();
            } else if (loading) {
                loading.textContent = 'No messages yet — start the conversation!';
            }
        } catch (e) { /* keep the last good state on transient errors */ }
        finally { polling = false; }
    }

    async function react(id, emoji) {
        try {
            const m = await api(REACT_BASE + '/' + id + '/react', 'POST', { emoji: emoji });
            upsert(m);
        } catch (e) { /* ignore */ }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const body = input.value.trim();
        if (!body) return;
        sendBtn.disabled = true;
        try {
            const m = await api(STORE_URL, 'POST', { body: body });
            upsert(m);
            input.value = '';
            scrollToBottom();
        } catch (err) { /* leave text in the box so nothing is lost */ }
        finally { sendBtn.disabled = false; input.focus(); }
    });

    // Close any open reaction palette when clicking elsewhere.
    document.addEventListener('click', function () {
        document.querySelectorAll('.react-palette').forEach(function (p) { p.classList.add('chat-hidden'); });
    });

    poll();
    setInterval(poll, 3500);
})();
</script>
@endsection
