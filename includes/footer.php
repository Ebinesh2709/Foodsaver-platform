<footer class="fs-footer mt-auto pt-5 pb-4">
    <div class="container">
        <div class="row text-start gx-md-5 gy-4 mb-5">
            <div class="col-lg-4">
                <h4 class="mb-3" style="color:#fff; font-weight:800; font-family:'Outfit', sans-serif;">🍱 FoodSaver</h4>
                <p style="color:rgba(255,255,255,0.7); font-size:0.95rem; line-height:1.7;">
                    Empowering Sri Lanka to reduce food waste by connecting surplus food from local businesses directly to the community.
                </p>
                <div class="mt-4 d-flex gap-3 social-links">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-3" style="color:#fff; font-weight:700;">Our Vision</h5>
                <p style="color:rgba(255,255,255,0.7); font-size:0.95rem; line-height:1.7;">
                    A future where zero perfectly good food goes to waste, and every community has access to affordable, quality meals.
                </p>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-3" style="color:#fff; font-weight:700;">Our Mission</h5>
                <p style="color:rgba(255,255,255,0.7); font-size:0.95rem; line-height:1.7;">
                    To build a sustainable digital ecosystem that bridges the gap between food surplus and food scarcity across the nation.
                </p>
            </div>
        </div>
        <div class="pt-4" style="border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; flex-wrap:wrap; align-items:center; gap:1rem;">
            <div style="color:rgba(255,255,255,0.6); font-size:0.9rem;">
                &copy; 2025 FoodSaver Sri Lanka. All rights reserved.
            </div>
            <div>
                <span class="sdg-pill" style="background:rgba(255,255,255,0.1); padding:0.4em 1em; border-radius:50px; font-size:0.85rem; color:#d1fae5; display:inline-flex; align-items:center; gap:0.4rem;">
                    🌍 <span>Supporting UN SDG 12</span>
                </span>
            </div>
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
    const API_URL = <?= json_encode(($css_prefix ?? '') . 'get_info.php') ?>;


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

        // Bypass InfinityFree's outbound firewall by connecting directly to Groq from the browser
        const GROQ_API_KEY = "<?= GROQ_API_KEY ?>";
        const prompt = "You are FoodSaver AI, a helpful assistant for a Sri Lankan food redistribution platform. Keep answers short (1-2 sentences), friendly, and focused on food waste reduction. User says: " + text;
        
        const payload = {
            model: "llama-3.1-8b-instant",
            messages: [{ role: "user", content: prompt }],
            temperature: 0.7,
            max_tokens: 150
        };

        fetch('https://api.groq.com/openai/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + GROQ_API_KEY
            },
            body: JSON.stringify(payload)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            removeTyping(typingEl);
            if (data.choices && data.choices[0] && data.choices[0].message) {
                const reply = data.choices[0].message.content;
                appendBotMessage(reply);
                history.push({ role: 'model', text: reply });
                if (history.length > 20) history = history.slice(-20);
            } else if (data.error) {
                appendErrorMessage(data.error.message || 'Groq API error');
            } else {
                appendErrorMessage('Something went wrong. Please try again.');
            }
        })
        .catch(function (err) {
            removeTyping(typingEl);
            appendErrorMessage('Network Error: ' + err.message);
            console.error('Chatbot error:', err);
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
