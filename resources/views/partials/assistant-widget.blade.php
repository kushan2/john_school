{{-- AI assistant floating chat widget. Included once in layouts/app.blade.php. --}}
<div id="ai-assistant">
    <button id="ai-launcher" type="button" aria-label="Open assistant">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </button>

    <div id="ai-panel" hidden>
        <div class="ai-head">
            <div class="ai-head-title">
                <span class="ai-dot"></span> Campus Assistant
            </div>
            <button id="ai-close" type="button" aria-label="Close">&times;</button>
        </div>

        <div id="ai-log" class="ai-log">
            <div class="ai-msg ai-bot">
                Hi! Ask me about events, groups, classifieds, news or people at your campus.
            </div>
        </div>

        <form id="ai-form" class="ai-form" autocomplete="off">
            <input id="ai-input" type="text" placeholder="Ask something…" maxlength="2000" required>
            <button type="submit" id="ai-send" aria-label="Send">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </div>
</div>

<style>
    #ai-launcher {
        position: fixed; right: 22px; bottom: 22px; z-index: 200;
        width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer;
        background: #0f4c81; color: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 6px 20px rgba(15,76,129,.4);
        transition: transform .12s, opacity .15s;
    }
    #ai-launcher:hover { transform: scale(1.06); }
    #ai-launcher svg { width: 24px; height: 24px; }

    /* When the `hidden` attribute is set, stay hidden — an id-level
       `display:flex` below would otherwise override the attribute and
       leave the panel permanently open (full-screen on mobile). */
    #ai-panel[hidden] { display: none !important; }

    #ai-panel {
        position: fixed; right: 22px; bottom: 22px; z-index: 201;
        width: 370px; max-width: calc(100vw - 32px); height: 520px; max-height: calc(100vh - 44px);
        background: #fff; border-radius: 14px; overflow: hidden;
        display: flex; flex-direction: column;
        box-shadow: 0 12px 40px rgba(0,31,63,.28);
        font-family: 'DM Sans', sans-serif;
    }
    .ai-head {
        background: #0f4c81; color: #fff; padding: 14px 16px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .ai-head-title { font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .ai-dot { width: 8px; height: 8px; border-radius: 50%; background: #6ee7b7; box-shadow: 0 0 8px #6ee7b7; }
    #ai-close { background: none; border: none; color: #fff; font-size: 22px; line-height: 1; cursor: pointer; opacity: .8; }
    #ai-close:hover { opacity: 1; }

    .ai-log { flex: 1; overflow-y: auto; padding: 16px; background: #F1F1F1; display: flex; flex-direction: column; gap: 10px; }
    .ai-msg { max-width: 85%; padding: 9px 13px; border-radius: 12px; font-size: 14px; line-height: 1.45; white-space: pre-wrap; word-wrap: break-word; }
    .ai-bot  { background: #fff; color: #001F3F; border: 1px solid #e2e6ea; align-self: flex-start; border-bottom-left-radius: 4px; }
    .ai-user { background: #0f4c81; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .ai-typing { color: #7c8798; font-style: italic; }

    .ai-form { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e2e6ea; background: #fff; }
    #ai-input { flex: 1; padding: 10px 13px; border: 1px solid #d5dbe1; border-radius: 9px; font-family: inherit; font-size: 14px; outline: none; color: #001F3F; }
    #ai-input:focus { border-color: #4f8ef7; box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
    #ai-send { border: none; background: #0f4c81; color: #fff; border-radius: 9px; width: 42px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    #ai-send svg { width: 18px; height: 18px; }
    #ai-send:disabled { opacity: .5; cursor: default; }

    @media (max-width: 768px) {
        /* Hide the floating bubble on mobile so it never covers the nav —
           open the assistant from the "Assistant" item in the mobile nav. */
        #ai-launcher { display: none !important; }
        #ai-panel { bottom: 0; right: 0; width: 100vw; max-width: 100vw; height: 100vh; max-height: 100vh; border-radius: 0; }
    }
</style>

<script>
(function () {
    var launcher = document.getElementById('ai-launcher');
    var panel    = document.getElementById('ai-panel');
    var closeBtn = document.getElementById('ai-close');
    var form     = document.getElementById('ai-form');
    var input    = document.getElementById('ai-input');
    var sendBtn  = document.getElementById('ai-send');
    var log      = document.getElementById('ai-log');

    var endpoint = "{{ route('assistant.chat') }}";
    var csrf     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var history  = [];               // {role, content} plain-text turns
    var busy     = false;

    function open()  { panel.hidden = false; launcher.style.display = 'none'; input.focus(); }
    function close() { panel.hidden = true;  launcher.style.display = ''; }

    launcher.addEventListener('click', open);
    closeBtn.addEventListener('click', close);

    // Let other UI (e.g. the mobile nav button) open the assistant.
    window.openAssistant = open;

    function addMsg(text, who) {
        var el = document.createElement('div');
        el.className = 'ai-msg ' + (who === 'user' ? 'ai-user' : 'ai-bot');
        el.textContent = text;
        log.appendChild(el);
        log.scrollTop = log.scrollHeight;
        return el;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text || busy) return;

        busy = true; sendBtn.disabled = true;
        addMsg(text, 'user');
        input.value = '';

        var typing = addMsg('…thinking', 'bot');
        typing.classList.add('ai-typing');

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ message: text, history: history.slice(-18) })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            var reply = (res.body && res.body.reply) ? res.body.reply : 'Sorry, something went wrong.';
            typing.classList.remove('ai-typing');
            typing.textContent = reply;
            log.scrollTop = log.scrollHeight;
            if (res.ok) {
                history.push({ role: 'user', content: text });
                history.push({ role: 'assistant', content: reply });
            }
        })
        .catch(function () {
            typing.classList.remove('ai-typing');
            typing.textContent = 'Network error — please try again.';
        })
        .finally(function () { busy = false; sendBtn.disabled = false; input.focus(); });
    });
})();
</script>
