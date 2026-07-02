<footer class="site-footer">
    <div class="container">
        <div class="row text-start gx-md-5 gy-4">
            
            <!-- Column 1 - Brand -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-3" style="color:#fff; font-weight:800; font-family:'Outfit', sans-serif;">🍱 FoodSaver</h4>
                <p style="color:#cbd5e1; font-size:0.95rem; line-height:1.7;">
                    Reducing food waste across Sri Lanka
                </p>
                <div class="mt-3">
                    <span style="background:rgba(255,255,255,0.1); padding:0.4em 1em; border-radius:50px; font-size:0.85rem; color:#a8e063; display:inline-flex; align-items:center;">
                        🌱 Aligned with UN SDG 12
                    </span>
                </div>
            </div>

            <!-- Column 2 - Platform -->
            <div class="col-lg-3 col-md-6">
                <h5>Platform</h5>
                <a href="<?= $css_prefix ?? '' ?>browse_listings.php">Browse Listings</a>
                <a href="<?= $css_prefix ?? '' ?>auth/register.php">Register as Business</a>
                <a href="<?= $css_prefix ?? '' ?>auth/login.php">Login</a>
                <a href="<?= $css_prefix ?? '' ?>about.php">About Us</a>
            </div>

            <!-- Column 3 - For Businesses -->
            <div class="col-lg-3 col-md-6">
                <h5>For Businesses</h5>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'business'): ?>
                    <a href="<?= $css_prefix ?? '' ?>business/add_listing.php">Add a Listing</a>
                <?php else: ?>
                    <a href="<?= $css_prefix ?? '' ?>auth/register.php">Add a Listing</a>
                <?php endif; ?>
                <a href="<?= $css_prefix ?? '' ?>about.php#pricing">Business Plans</a>
                <a href="<?= $css_prefix ?? '' ?>about.php#how-it-works">How it Works</a>
            </div>

            <!-- Column 4 - Legal & Info -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3 fw-bold">Legal & Info</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="privacy.php">Privacy Policy</a></li>
                    <li class="mb-2"><a href="terms.php">Terms of Service</a></li>
                    <li class="mb-2"><a href="mailto:contact@foodsaver.lk">contact@foodsaver.lk</a></li>
                    <li class="mb-2"><a href="https://github.com/Ebinesh2709/Foodsaver-platform" target="_blank"><i class="bi bi-github me-1"></i>GitHub</a></li>
                    <li class="mb-2"><a href="https://www.instagram.com/foodsaverapp2026?igsh=MWNlb2E5cjYyN2owNA==" target="_blank"><i class="bi bi-instagram me-1"></i>Instagram</a></li>
                    <li><a href="https://www.facebook.com/share/1EQW3KGDUV/?mibextid=wwXIfr" target="_blank"><i class="bi bi-facebook me-1"></i>Facebook</a></li>
                </ul>
            </div>
            
        </div>
        
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> FoodSaver &mdash; Built by Ebinesh Udayakumar | IIT affiliated with University of Westminster UK | SDG 12
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" aria-label="Back to Top">
    <i class="bi bi-arrow-up"></i>
</button>

<script>
    // Back to Top functionality
    const backToTopBtn = document.getElementById('back-to-top');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
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
        
        <?php
        // Gather dynamic context for AI
        $ai_context = "General FoodSaver platform.";
        if (isset($pdo)) {
            try {
                if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'business') {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM food_listings WHERE business_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $biz_cnt = $stmt->fetchColumn();
                    
                    $stmt2 = $pdo->prepare("SELECT title FROM food_listings WHERE business_id = ? LIMIT 5");
                    $stmt2->execute([$_SESSION['user_id']]);
                    $biz_items = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                    $items_str = $biz_items ? implode(", ", $biz_items) : "none yet";
                    
                    $ai_context = "The user is logged in as a BUSINESS account (Name: " . htmlspecialchars($_SESSION['name']) . "). They have $biz_cnt active listings. Some of their items: $items_str.";
                } else {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM food_listings WHERE quantity > 0");
                    $total_active = $stmt->fetchColumn();
                    
                    $stmt2 = $pdo->query("SELECT title FROM food_listings WHERE quantity > 0 ORDER BY id DESC LIMIT 5");
                    $active_items = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                    $items_str = $active_items ? implode(", ", $active_items) : "none currently";
                    
                    $role_str = isset($_SESSION['user_id']) ? "a CUSTOMER (Name: " . htmlspecialchars($_SESSION['name']) . ")" : "a GUEST";
                    $ai_context = "The user is $role_str. The platform currently has $total_active active food listings available. Recent items: $items_str.";
                }
            } catch (Exception $e) {}
        }
        ?>
        const dynamicContext = <?= json_encode($ai_context) ?>;
        
        const systemPrompt = `You are the official FoodSaver assistant — a helpful chatbot built exclusively for the FoodSaver food waste reduction web platform operated in Sri Lanka.

STRICT RULES YOU MUST FOLLOW:
1. You ONLY answer questions about the FoodSaver website and its features. Nothing else.
2. If someone asks about anything unrelated to FoodSaver — such as general knowledge, other apps, cooking recipes, weather, news, or anything outside this platform — politely decline and redirect them to ask about FoodSaver instead.
3. You are NOT a general AI assistant. You are ONLY the FoodSaver website helper.
4. FoodSaver is a WEBSITE only — there is no mobile app yet. If asked about an app, say "FoodSaver is currently available as a website only at food-saver.infinityfreeapp.com. A mobile app is planned for a future release."

WHAT YOU KNOW ABOUT FOODSAVER:

Platform: FoodSaver is a web-based food waste reduction platform that connects surplus food businesses (restaurants, canteens, bakeries) with local customers in Sri Lanka who can reserve discounted food before it expires.

Live website: food-saver.infinityfreeapp.com

User roles:
- Business users: Can register, post food listings, manage reservations, confirm and collect orders
- Customer users: Can browse listings, search using natural language, reserve food, view and cancel reservations
- Admin users: Can view platform statistics and recent activity

AI features on this platform:
- AI Urgency Scoring: Automatically classifies each listing as High, Medium, or Low urgency based on the food description and time remaining until pickup
- Natural Language Search: Customers can type plain English like "I need 10 rice meals for tonight" and the AI extracts filters and finds matching listings
- Smart Synonym Matching: AI generates alternative food names to broaden search results
- AI Listing Summary: Automatically generates a short appetising description for each food listing
- AI Expiry Alert: Shows a personalised urgent message on high-urgency listings
- AI Dynamic Discount Recommendation: Suggests the optimal discount percentage for a listing based on food type, quantity, and time remaining
- AI Chatbot: That is you — you help users navigate and understand the platform

Food listings: All food listings shown on the platform are REAL listings posted by registered businesses. You do not know the exact listings available right now because they change in real time. If a user asks "what foods do you have" or "what is available", tell them to visit the Browse Listings page at food-saver.infinityfreeapp.com/browse_listings.php to see all current available listings. Do NOT make up or suggest specific food items.

How to reserve food:
1. Register or login as a customer at food-saver.infinityfreeapp.com/auth/register.php
2. Go to Browse Listings
3. Search for what you need using the AI search box
4. Click Reserve Now on any listing you want
5. View your reservation in My Reservations

How to post food as a business:
1. Register as a Business at food-saver.infinityfreeapp.com/auth/register.php
2. Login and go to your Business Dashboard
3. Click Add Listing and fill in the food details
4. The AI will automatically score urgency and generate a summary
5. Use the AI Discount Suggestion button to get a recommended price

Reservation status meanings:
- Pending: Your reservation is waiting for the business to confirm
- Confirmed: The business has confirmed — food is ready for pickup
- Collected: You have collected the food — complete
- Cancelled: The reservation was cancelled and the listing is available again

Technology: The platform is built with PHP, MySQL, and Bootstrap. AI features use the Groq API and Gemini API. It is deployed on InfinityFree hosting.

SDG alignment: FoodSaver is aligned with United Nations Sustainable Development Goal 12 — Responsible Consumption and Production — which aims to reduce food waste globally.

Pricing: FoodSaver is currently free to use for all users during the beta launch period. Business plans are planned for a future release.

WHAT YOU MUST NEVER DO:
- Never make up food items or listings that may not exist
- Never answer questions unrelated to FoodSaver
- Never pretend to be ChatGPT, Gemini, or any other AI
- Never discuss other food apps, competitors, or general food topics
- Never say you can place reservations on behalf of the user — direct them to the website
- Never discuss politics, news, general knowledge, or any topic outside this platform

If you are unsure about something, say: "I can only assist with FoodSaver platform questions. Please visit food-saver.infinityfreeapp.com for the latest information."

Keep all responses short, friendly, and helpful. Use simple English. Always end with a helpful next step or a link to the relevant page.`;
        
        const prompt = systemPrompt + "\n\nCurrent context: " + dynamicContext + "\n\nUser says: " + text;
        
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
