<?php
session_start();
$page_title = 'About Us & Premium Plans';
$page_description = 'Learn how FoodSaver uses AI to connect surplus food businesses with communities across Sri Lanka to reduce food waste. View our mission, AI features, and premium business plans.';
$active_page = 'about';
require_once 'includes/header.php';
?>

<!-- HERO SECTION -->
<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); min-height: 100vh; display: flex; align-items: center; position: relative; padding: 120px 0 60px;">
    <div class="container text-center text-white fade-in-up visible">
        <h1 class="display-3 fw-bold mb-4 gradient-text">Fighting Food Waste,<br>One Meal at a Time.</h1>
        <p class="lead mb-5 mx-auto" style="max-width: 800px; color: #cbd5e1;">
            FoodSaver connects surplus food businesses with local communities across Sri Lanka &mdash; giving perfectly good food a second chance before it's wasted.
        </p>
        <div class="d-flex justify-content-center gap-3 mb-5">
            <a href="browse_listings.php" class="btn btn-fs-primary btn-lg px-5 shadow-sm">Browse Listings</a>
            <a href="auth/register.php" class="btn btn-nav-outline btn-lg px-5" style="border-color: rgba(255,255,255,0.3); color: white;">Join as a Business</a>
        </div>
        
        <!-- Stats Counters -->
        <div class="row justify-content-center mt-5 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="col-md-3 mb-3">
                <div class="display-4 fw-bold text-white mb-2"><span class="counter" data-target="500">0</span>+</div>
                <div class="text-uppercase small fw-bold" style="letter-spacing: 1px; color: #a8e063;">Meals Saved</div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="display-4 fw-bold text-white mb-2"><span class="counter" data-target="50">0</span>+</div>
                <div class="text-uppercase small fw-bold" style="letter-spacing: 1px; color: #a8e063;">Partner Businesses</div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="display-4 fw-bold text-white mb-2"><span class="counter" data-target="1000">0</span>+</div>
                <div class="text-uppercase small fw-bold" style="letter-spacing: 1px; color: #a8e063;">Community Members</div>
            </div>
        </div>
    </div>
</section>

<!-- MISSION SECTION -->
<section style="background: #fdfdfc; padding: 100px 0;">
    <div class="container fade-in-up">
        <div class="row align-items-center gx-5">
            <div class="col-lg-5 mb-5 mb-lg-0">
                <div class="p-5 rounded-4 shadow-sm" style="background: var(--fs-green-light); border-left: 5px solid var(--fs-green-dark);">
                    <h2 class="fw-bold mb-4" style="color: var(--fs-green-dark);">Our Mission</h2>
                    <blockquote class="fs-4 fst-italic text-dark mb-0" style="line-height: 1.6;">
                        "To build a sustainable digital ecosystem that bridges the gap between food surplus and food scarcity across the nation, supporting UN SDG 12 for responsible consumption and production."
                    </blockquote>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="fs-card p-4 h-100 text-center">
                            <div class="display-5 mb-3" style="color: var(--fs-orange);">🗑️</div>
                            <h4 class="h5 fw-bold">The Problem</h4>
                            <p class="text-muted small mb-0">Tons of edible surplus food are discarded daily by bakeries, restaurants, and supermarkets in Sri Lanka.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fs-card p-4 h-100 text-center">
                            <div class="display-5 mb-3" style="color: var(--fs-green);">🤝</div>
                            <h4 class="h5 fw-bold">The Solution</h4>
                            <p class="text-muted small mb-0">A real-time platform allowing businesses to list surplus food at a discount, instantly notifying the community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works" style="padding: 100px 0; background: var(--fs-white);">
    <div class="container text-center fade-in-up">
        <h2 class="fw-bold mb-5">How FoodSaver Works</h2>
        <div class="row position-relative z-1">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="p-4 bg-light rounded-4 h-100 border transition-hover shadow-sm">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">🏪</div>
                    <h4 class="h5 fw-bold">1. Businesses List Food</h4>
                    <p class="text-muted small">A restaurant has surplus food. They create a quick listing. Our AI instantly scores the urgency and recommends a dynamic discount.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="p-4 bg-light rounded-4 h-100 border transition-hover shadow-sm">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">🔍</div>
                    <h4 class="h5 fw-bold">2. Customers Discover</h4>
                    <p class="text-muted small">Locals use natural language search ("I want 2 portions of rice") to find exact matches, completely powered by AI.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-light rounded-4 h-100 border transition-hover shadow-sm">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">🛍️</div>
                    <h4 class="h5 fw-bold">3. Reserve & Save</h4>
                    <p class="text-muted small">Customers reserve the food online, then pick it up directly from the store. Food is saved, money is saved, waste is prevented.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI FEATURES SECTION -->
<section id="ai-features" style="background: #0f172a; padding: 100px 0;">
    <div class="container fade-in-up">
        <div class="text-center mb-5">
            <span class="badge text-uppercase mb-2" style="background: rgba(148, 216, 45, 0.2); color: #a8e063; letter-spacing: 1px;">Powered by Groq & Gemini</span>
            <h2 class="fw-bold text-white">Smart Artificial Intelligence</h2>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">FoodSaver is not just a marketplace. It uses cutting-edge LLMs to optimize every step of the food rescue process.</p>
        </div>
        
        <div class="row g-4">
            <!-- AI Card 1 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-speedometer2 display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">AI Urgency Scoring</h4>
                    <p class="text-light small mb-0 opacity-75">Automatically calculates food urgency (High/Medium/Low) based on category and expiry time.</p>
                </div>
            </div>
            <!-- AI Card 2 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-search display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">Natural Language Search</h4>
                    <p class="text-light small mb-0 opacity-75">Search in plain English (e.g. "I need vegetarian dinner for two") and AI finds the best matching listings.</p>
                </div>
            </div>
            <!-- AI Card 3 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-diagram-3 display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">Smart Synonym Matching</h4>
                    <p class="text-light small mb-0 opacity-75">Understands semantic relationships (e.g. searching "bread" will also find "bun" or "loaf").</p>
                </div>
            </div>
            <!-- AI Card 4 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-card-text display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">Listing Summary Generator</h4>
                    <p class="text-light small mb-0 opacity-75">Generates catchy, personalized one-line descriptions for every food listing automatically.</p>
                </div>
            </div>
            <!-- AI Card 5 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-graph-down display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">Dynamic Discount Recommendation</h4>
                    <p class="text-light small mb-0 opacity-75">Suggests the optimal discount percentage to businesses based on time remaining until expiry.</p>
                </div>
            </div>
            <!-- AI Card 6 -->
            <div class="col-md-4">
                <div class="fs-card ai-feature-card p-4 h-100 text-center" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi bi-robot display-5 mb-3 d-block" style="color: #a8e063;"></i>
                    <h4 class="h5 fw-bold text-white">AI Chatbot Assistant</h4>
                    <p class="text-light small mb-0 opacity-75">A 24/7 floating assistant that helps users navigate the platform and answers questions instantly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PREMIUM PLANS SECTION -->
<section id="pricing" style="padding: 100px 0; background: var(--fs-bg); background-image: url('assets/img/background.png'); background-size: cover;">
    <div class="container fade-in-up">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Business Plans</h2>
            <p class="text-muted">Currently in Beta &mdash; All features are available free during the launch period.</p>
        </div>

        <div class="row g-4 align-items-center justify-content-center">
            
            <!-- Card 1 — Free Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="fs-card p-4 h-100 shadow-sm">
                    <h4 class="fw-bold">Free Plan</h4>
                    <p class="text-muted small">For Customers</p>
                    <div class="my-4">
                        <span class="display-4 fw-bold">LKR 0</span><span class="text-muted">/forever</span>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Browse all food listings</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>AI-powered natural language search</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Reserve food listings</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>View and cancel reservations</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>No credit card required</li>
                    </ul>
                    <a href="auth/register.php" class="btn btn-nav-outline w-100">Get Started</a>
                </div>
            </div>

            <!-- Card 2 — Business Free -->
            <div class="col-lg-3 col-md-6">
                <div class="fs-card p-4 h-100 shadow-sm">
                    <h4 class="fw-bold">Business Free</h4>
                    <p class="text-muted small">For New Businesses</p>
                    <div class="my-4">
                        <span class="display-4 fw-bold">LKR 0</span><span class="text-muted">/month</span>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Post up to 2 listings per month</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Basic listing management</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Manual urgency classification</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>No AI features included</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Perfect for trying the platform</li>
                    </ul>
                    <a href="auth/register.php" class="btn btn-fs-primary w-100">Register Free</a>
                </div>
            </div>

            <!-- Card 3 — Business Starter -->
            <div class="col-lg-3 col-md-6">
                <div class="fs-card pro-card p-1 shadow-lg" style="background: linear-gradient(135deg, var(--fs-green), var(--fs-green-dark)); z-index: 10;">
                    <div class="bg-white rounded p-4 h-100 position-relative">
                        <div class="position-absolute top-0 end-0 bg-warning text-dark fw-bold px-3 py-1 rounded-bl" style="border-bottom-left-radius: 12px; border-top-right-radius: 12px; font-size: 0.8rem; box-shadow: -2px 2px 10px rgba(0,0,0,0.1);">
                            Most Popular
                        </div>
                        <h4 class="fw-bold">Business Starter</h4>
                        <p class="text-muted small">For Small Shops</p>
                        <div class="my-4">
                            <span class="display-4 fw-bold">LKR 990</span><span class="text-muted">/month</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Up to 10 listings per month</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>AI urgency scoring on every listing</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>AI-generated listing summaries</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>AI dynamic discount recommendation</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Reservation management dashboard</li>
                        </ul>
                        <a href="checkout.php?plan=starter" class="btn w-100 text-white fw-bold" style="background: var(--fs-green-dark);">Get Started</a>
                    </div>
                </div>
            </div>

            <!-- Card 4 — Business Pro -->
            <div class="col-lg-3 col-md-6">
                <div class="fs-card p-4 h-100 shadow-sm">
                    <h4 class="fw-bold">Business Pro</h4>
                    <p class="text-muted small">For Restaurants and Supermarkets</p>
                    <div class="my-4">
                        <span class="display-4 fw-bold">LKR 2,490</span><span class="text-muted">/month</span>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited listings per month</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>All AI features included</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Priority placement in browse listings</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Verified Business badge on all listings</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Early access to new features</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Dedicated support</li>
                    </ul>
                    <a href="checkout.php?plan=pro" class="btn btn-fs-primary w-100">Get Started</a>
                </div>
            </div>

        </div>
        <p class="text-center text-muted small mt-4 fst-italic">All features are currently unlocked for all users during the FoodSaver beta launch period.</p>
    </div>
</section>

<!-- SDG 12 ALIGNMENT -->
<section id="sdg12" style="background: #fffdf5; padding: 100px 0;">
    <div class="container fade-in-up">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 text-center">
                <div style="background: #e62b38; width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 20px; color: white; font-weight: bold; font-size: 1.5rem; text-align: center; padding: 20px;">
                    SDG 12<br><br>Responsible Consumption & Production
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Aligned with Global Goals</h2>
                <p class="mb-4 text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                    FoodSaver directly contributes to the United Nations Sustainable Development Goal 12. By providing a technological bridge between businesses with surplus food and communities in need, we actively reduce food waste, lower carbon footprints, and promote sustainable consumption habits in Sri Lanka.
                </p>
                <blockquote class="fst-italic fs-5 text-dark" style="border-left: 4px solid #e62b38; padding-left: 20px;">
                    "Approximately one-third of all food produced globally for human consumption is lost or wasted each year."<br>
                    <small class="text-muted fw-bold">&mdash; FAO, 2011</small>
                </blockquote>
            </div>
        </div>
    </div>
</section>

<!-- TEAM / DEVELOPER -->
<section style="padding: 100px 0; background: var(--fs-white);">
    <div class="container text-center fade-in-up">
        <h2 class="fw-bold mb-5">The Developer</h2>
        <div class="fs-card p-5 mx-auto" style="max-width: 600px;">
            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-4 border shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem; color: var(--fs-green-dark); font-weight: 800;">
                EU
            </div>
            <h3 class="fw-bold h4">Ebinesh Udayakumar</h3>
            <p class="text-muted fw-bold mb-1">Full-Stack Developer & Founder</p>
            <p class="small text-muted mb-4">Informatics Institute of Technology (IIT)<br>affiliated with University of Westminster UK<br>Software Development Group Project &mdash; 5COSC021C</p>
            <p class="text-dark fst-italic mb-4" style="line-height: 1.6;">
                "Built FoodSaver as an individual project to solve the food waste crisis in local Sri Lankan communities using modern web technologies and artificial intelligence."
            </p>
            <a href="https://github.com/Ebinesh2709/Foodsaver-platform" target="_blank" rel="noopener" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-github me-2"></i>View on GitHub</a>
        </div>
    </div>
</section>

<!-- CONTACT CTA -->
<section style="background: #0f172a; padding: 100px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="container text-center fade-in-up text-white">
        <h2 class="display-5 fw-bold mb-4">Ready to reduce food waste?</h2>
        <p class="lead mb-5 text-light opacity-75" style="max-width: 600px; margin: 0 auto;">Join thousands of others in making a positive environmental impact while saving money on delicious food.</p>
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
            <a href="auth/register.php" class="btn btn-fs-primary btn-lg px-5">Join as Business</a>
            <a href="browse_listings.php" class="btn btn-light btn-lg px-5 text-dark fw-bold">Browse as Customer</a>
        </div>
        <p class="text-muted small"><i class="bi bi-envelope me-2"></i>contact@foodsaver.lk</p>
    </div>
</section>

<!-- Intersection Observer & Counter Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fade in on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Trigger counter if it has counters
                const counters = entry.target.querySelectorAll('.counter');
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const duration = 2000; 
                    const increment = target / (duration / 16);
                    let current = 0;
                    
                    if (counter.innerText === '0' || counter.innerText === '') {
                        const updateCounter = () => {
                            current += increment;
                            if (current < target) {
                                counter.innerText = Math.ceil(current);
                                requestAnimationFrame(updateCounter);
                            } else {
                                counter.innerText = target;
                            }
                        };
                        updateCounter();
                    }
                });
                
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
});
</script>

<?php require_once 'includes/footer.php'; ?>
