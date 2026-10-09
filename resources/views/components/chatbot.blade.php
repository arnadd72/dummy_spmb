{{-- Chatbot Widget --}}
<div id="chatbot-widget" aria-live="polite">
    {{-- Toggle Button --}}
    <button class="chatbot-btn" id="chatbot-toggle"
            aria-label="Buka asisten chatbot PPDB"
            aria-expanded="false"
            aria-controls="chatbot-panel">
        <svg id="chatbot-icon-open" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
        </svg>
        <svg id="chatbot-icon-close" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chatbot Panel --}}
    <div class="chatbot-panel" id="chatbot-panel" role="dialog" aria-label="Asisten Chatbot PPDB" aria-hidden="true">
        <div class="chatbot-header">
            <div class="chatbot-avatar" aria-hidden="true">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.798-1.319 2.798H4.117c-1.35 0-2.32-1.798-1.319-2.798L4.2 15.3" />
                </svg>
            </div>
            <div>
                <div style="font-weight:700; font-size:0.9rem;">Asisten PPDB</div>
                <div style="font-size:0.75rem; opacity:0.8;">Tanya apapun seputar pendaftaran</div>
            </div>
            <button id="chatbot-close-btn"
                    style="margin-left:auto; background:rgba(255,255,255,0.15); border:none; color:white; width:2rem; height:2rem; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;"
                    aria-label="Tutup chatbot">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="chatbot-body" id="chatbot-body">
            <div class="chat-bubble bot">
                Halo! 👋 Saya asisten PPDB SMK Ma'arif NU 1 Wangon.<br>
                Ada yang bisa saya bantu? Tanyakan seputar persyaratan, jadwal, atau cara pendaftaran.
            </div>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <button class="chatbot-quick-btn" data-query="persyaratan dokumen apa saja">Persyaratan</button>
                <button class="chatbot-quick-btn" data-query="jadwal pendaftaran kapan">Jadwal</button>
                <button class="chatbot-quick-btn" data-query="cara upload dokumen">Cara Upload</button>
                <button class="chatbot-quick-btn" data-query="jurusan apa saja yang tersedia">Jurusan</button>
            </div>
        </div>

        <div class="chatbot-footer">
            <label for="chatbot-input" class="sr-only">Ketik pertanyaan Anda</label>
            <input type="text" id="chatbot-input" class="chatbot-input"
                   placeholder="Ketik pertanyaan..."
                   maxlength="300"
                   autocomplete="off">
            <button class="chatbot-send" id="chatbot-send" aria-label="Kirim pertanyaan">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.269 20.876L5.999 12zm0 0h7.5" />
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
.chatbot-quick-btn {
    padding: 0.3125rem 0.75rem;
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    font-size: 0.8125rem;
    font-family: var(--font-sans);
    cursor: pointer;
    color: var(--color-primary);
    font-weight: 600;
    transition: all 150ms;
}
.chatbot-quick-btn:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}
.sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
</style>

<script>
(function() {
    const toggle    = document.getElementById('chatbot-toggle');
    const panel     = document.getElementById('chatbot-panel');
    const closeBtn  = document.getElementById('chatbot-close-btn');
    const body      = document.getElementById('chatbot-body');
    const input     = document.getElementById('chatbot-input');
    const sendBtn   = document.getElementById('chatbot-send');
    const iconOpen  = document.getElementById('chatbot-icon-open');
    const iconClose = document.getElementById('chatbot-icon-close');
    let isOpen = false;

    function openChat() {
        isOpen = true;
        panel.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        panel.setAttribute('aria-hidden', 'false');
        iconOpen.style.display = 'none';
        iconClose.style.display = 'block';
        toggle.setAttribute('aria-label', 'Tutup chatbot PPDB');
        input.focus();
    }

    function closeChat() {
        isOpen = false;
        panel.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
        iconOpen.style.display = 'block';
        iconClose.style.display = 'none';
        toggle.setAttribute('aria-label', 'Buka asisten chatbot PPDB');
    }

    toggle.addEventListener('click', () => isOpen ? closeChat() : openChat());
    closeBtn.addEventListener('click', closeChat);

    function addBubble(text, type) {
        const div = document.createElement('div');
        div.className = `chat-bubble ${type}`;
        div.textContent = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        return div;
    }

    async function sendMessage(q) {
        if (!q.trim()) return;
        addBubble(q, 'user');
        input.value = '';

        const typing = addBubble('...', 'bot');
        typing.style.opacity = '0.5';

        try {
            const res = await fetch('{{ route('ppdb.chatbot') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ query: q }),
            });
            const data = await res.json();
            typing.textContent = data.message;
            typing.style.opacity = '1';
        } catch (e) {
            typing.textContent = 'Maaf, terjadi kesalahan. Silakan coba lagi.';
            typing.style.opacity = '1';
        }
        body.scrollTop = body.scrollHeight;
    }

    sendBtn.addEventListener('click', () => sendMessage(input.value));
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendMessage(input.value); });

    document.querySelectorAll('.chatbot-quick-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openChat();
            sendMessage(btn.dataset.query);
        });
    });
})();
</script>
