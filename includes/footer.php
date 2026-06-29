<footer class="fs-footer mt-auto">
    <div class="container">
        <div class="mb-1">
            <strong>🍱 FoodSaver</strong> &mdash; © 2025 Reducing food waste in Sri Lanka
        </div>
        <div>
            <span class="sdg-pill">🌍 Supporting UN SDG 12 — Responsible Consumption</span>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmkfh6kvJBiA5fE2RkMFeAx1yAX"
        crossorigin="anonymous"></script>

<!-- ═══════════════════════════════════════════════════
     FoodSaver AI Chatbot Widget
═══════════════════════════════════════════════════ -->

<!-- Floating toggle button -->
<button id="fs-chat-toggle" aria-label="Open FoodSaver AI Assistant" title="Ask FoodSaver AI">
    <span id="fs-chat-badge" title="Chat with AI">AI</span>
    <!-- Chat icon -->
    <svg class="icon-chat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
    </svg>
    <!-- Close icon -->
    <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>
</button>

<!-- Chat window -->
<div id="fs-chat-window" role="dialog" aria-label="FoodSaver AI Assistant" aria-modal="true">

    <!-- Header -->
    <div id="fs-chat-header">
        <div class="fs-chat-avatar">🤖</div>
        <div class="fs-chat-header-info">
            <h6>FoodSaver AI</h6>
            <span><span class="fs-chat-status-dot"></span>Online — here to help</span>
        </div>
    </div>

    <!-- Messages -->
    <div id="fs-chat-messages" role="log" aria-live="polite" aria-label="Chat messages">
        <!-- Welcome message injected by JS -->
    </div>

    <!-- Quick suggestion chips -->
    <div id="fs-chat-suggestions">
        <button class="fs-chip" data-msg="How do I reserve a food listing?">How to reserve?</button>
        <button class="fs-chip" data-msg="What food categories are available?">Food categories</button>
        <button class="fs-chip" data-msg="What does 'High Urgency' mean?">Urgency levels</button>
        <button class="fs-chip" data-msg="How do I cancel my reservation?">Cancel reservation</button>
        <button class="fs-chip" data-msg="How does FoodSaver work?">How it works</button>
    </div>

    <!-- Input area -->
    <div id="fs-chat-input-area">
        <textarea
            id="fs-chat-input"
            placeholder="Ask about food listings, reservations…"
            rows="1"
            maxlength="500"
            aria-label="Type your message"
        ></textarea>
        <button id="fs-chat-send" aria-label="Send message" title="Send">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
        </button>
    </div>

    <!-- Disclaimer -->
    <div id="fs-chat-disclaimer">
        🤖 AI can make mistakes. Restricted to FoodSaver topics only.
    </div>
</div>

<script>
(function () {
    'use strict';

    /* ── Elements ── */
    const toggle      = document.getElementById('fs-chat-toggle');
    const window_el   = document.getElementById('fs-chat-window');
    const messages    = document.getElementById('fs-chat-messages');
    const input       = document.getElementById('fs-chat-input');
    const sendBtn     = document.getElementById('fs-chat-send');
    const badge       = document.getElementById('fs-chat-badge');
    const chips       = document.querySelectorAll('.fs-chip');

    /* ── State ── */
    let isOpen    = false;
    let isLoading = false;
    let history   = [];  // [{role: 'user'|'model', text: '...'}]
    let badgeHidden = false;

    /* ── API base URL resolved by PHP ── */
    const API_URL = <?= json_encode(($css_prefix ?? '') . 'api/chatbot.php') ?>;


    /* ── Welcome message ── */
    function init() {
        appendBotMessage(
            "👋 Hi! I'm FoodSaver AI Assistant. I can help you with:\n\n" +
            "• Browsing & reserving food listings\n" +
            "• Understanding urgency levels\n" +
            "• Reservation status & cancellations\n" +
            "• How the FoodSaver platform works\n\n" +
            "What would you like to know?"
        );
    }

    /* ── Toggle chat window ── */
    toggle.addEventListener('click', function () {
        isOpen = !isOpen;
        toggle.classList.toggle('open', isOpen);
        window_el.classList.toggle('open', isOpen);

        if (isOpen) {
            // Hide badge when opened
            if (!badgeHidden) {
                badge.style.display = 'none';
                badgeHidden = true;
            }
            setTimeout(() => input.focus(), 320);
        }
    });

    /* ── Close on Escape ── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            isOpen = false;
            toggle.classList.remove('open');
            window_el.classList.remove('open');
        }
    });

    /* ── Quick chips ── */
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const msg = chip.getAttribute('data-msg');
            if (msg && !isLoading) {
                sendMessage(msg);
                // Hide chips after first use
                document.getElementById('fs-chat-suggestions').style.display = 'none';
            }
        });
    });

    /* ── Send on button click ── */
    sendBtn.addEventListener('click', function () {
        const text = input.value.trim();
        if (text && !isLoading) sendMessage(text);
    });

    /* ── Send on Enter (Shift+Enter = newline) ── */
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const text = input.value.trim();
            if (text && !isLoading) sendMessage(text);
        }
    });

    /* ── Auto-resize textarea ── */
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    /* ── Send message logic ── */
    function sendMessage(text) {
        if (isLoading) return;

        // Hide chips permanently
        document.getElementById('fs-chat-suggestions').style.display = 'none';

        // Add user bubble
        appendUserMessage(text);
        history.push({ role: 'user', text: text });

        // Clear input
        input.value = '';
        input.style.height = 'auto';

        // Show typing indicator
        const typingEl = showTyping();
        isLoading = true;
        sendBtn.disabled = true;
        input.disabled = true;

        // POST to backend
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: text,
                history: history.slice(-14) // send last 7 exchanges
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            removeTyping(typingEl);
            if (data.reply) {
                appendBotMessage(data.reply);
                history.push({ role: 'model', text: data.reply });
                // Keep history lean
                if (history.length > 20) history = history.slice(-20);
            } else if (data.error) {
                appendErrorMessage(data.error);
            } else {
                appendErrorMessage('Something went wrong. Please try again.');
            }
        })
        .catch(function () {
            removeTyping(typingEl);
            appendErrorMessage('Connection error. Please check your internet and try again.');
        })
        .finally(function () {
            isLoading = false;
            sendBtn.disabled = false;
            input.disabled = false;
            input.focus();
        });
    }

    /* ── DOM helpers ── */
    function appendUserMessage(text) {
        const el = document.createElement('div');
        el.className = 'fs-msg user';
        el.innerHTML =
            '<div class="fs-msg-icon">👤</div>' +
            '<div class="fs-msg-bubble">' + escapeHtml(text) + '</div>';
        messages.appendChild(el);
        scrollToBottom();
    }

    function appendBotMessage(text) {
        const el = document.createElement('div');
        el.className = 'fs-msg bot';
        el.innerHTML =
            '<div class="fs-msg-icon">🤖</div>' +
            '<div class="fs-msg-bubble">' + formatBotText(text) + '</div>';
        messages.appendChild(el);
        scrollToBottom();
    }

    function appendErrorMessage(text) {
        const el = document.createElement('div');
        el.className = 'fs-msg bot fs-msg-error';
        el.innerHTML =
            '<div class="fs-msg-icon">⚠️</div>' +
            '<div class="fs-msg-bubble">' + escapeHtml(text) + '</div>';
        messages.appendChild(el);
        scrollToBottom();
    }

    function showTyping() {
        const el = document.createElement('div');
        el.className = 'fs-msg bot fs-typing-indicator';
        el.innerHTML =
            '<div class="fs-msg-icon">🤖</div>' +
            '<div class="fs-msg-bubble">' +
                '<span class="fs-typing-dot"></span>' +
                '<span class="fs-typing-dot"></span>' +
                '<span class="fs-typing-dot"></span>' +
            '</div>';
        messages.appendChild(el);
        scrollToBottom();
        return el;
    }

    function removeTyping(el) {
        if (el && el.parentNode) el.parentNode.removeChild(el);
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    /* ── Text helpers ── */
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatBotText(text) {
        // Escape first, then format
        let safe = escapeHtml(text);
        // Convert **bold** to <strong>
        safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Convert *italic* to <em>
        safe = safe.replace(/\*(.+?)\*/g, '<em>$1</em>');
        // Convert bullet lines starting with • or - to proper items
        // Replace newlines with <br>
        safe = safe.replace(/\n/g, '<br>');
        return safe;
    }

    /* ── Init ── */
    init();

})();
</script>
</body>
</html>
