<?php
session_start();
$page_title = 'Premium Business Plans';
$page_description = 'Explore FoodSaver Business Plans. Our Premium plans include unlimited listings, priority placement, and full access to our AI features.';
$active_page = 'about';
require_once 'includes/header.php';
?>

<!-- PRICING HERO SECTION -->
<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); padding: 120px 0 60px;">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-4">Choose Your Plan</h1>
        <p class="lead mb-0 mx-auto" style="max-width: 600px; color: #cbd5e1;">
            Whether you're a small local bakery or a large supermarket chain, we have a plan designed to help you reduce waste and recover costs.
        </p>
    </div>
</section>

<!-- PRICING CARDS -->
<section style="padding: 80px 0; background: var(--fs-bg); background-image: url('assets/img/background.png'); background-size: cover;">
    <div class="container fade-in-up visible">
        <div class="row g-4 align-items-center justify-content-center mb-5">
            
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

        <div class="text-center mt-5">
            <span class="badge text-dark bg-white border px-3 py-2 fw-bold" style="font-size: 0.9rem;">
                <i class="bi bi-shield-check text-success me-1"></i>
                All plans include: PHP/MySQL backend, AI features, CSRF security, mobile responsive design.
            </span>
        </div>
    </div>
</section>

<!-- FAQ SECTION -->
<section style="padding: 100px 0; background: var(--fs-white);">
    <div class="container fade-in-up">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Everything you need to know about the product and billing.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="faq-accordion">
                    
                    <!-- FAQ 1 -->
                    <div class="faq-item mb-3">
                        <div class="faq-question">
                            Is FoodSaver free?
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! FoodSaver is completely free for customers to browse and reserve food. For businesses, we offer a free trial during our beta launch period. After that, businesses can choose from our affordable Starter or Pro plans based on their needs.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="faq-item mb-3">
                        <div class="faq-question">
                            How does AI urgency scoring work?
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Our platform integrates with Groq's fast LLMs to instantly analyze the type of food and its expiry time. If a cooked meal expires in 2 hours, the AI automatically tags it as "High Urgency" to prioritize its visibility and ensure it gets saved in time.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="faq-item mb-3">
                        <div class="faq-question">
                            Can I list food daily?
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Absolutely. With our Business Pro plan, you can post an unlimited number of listings every single day. This is perfect for bakeries or supermarkets that have varying amounts of surplus stock at the end of each shift.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="faq-item mb-3">
                        <div class="faq-question">
                            How do I cancel my plan?
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            You can cancel your subscription at any time directly from your Business Dashboard. There are no long-term contracts or hidden cancellation fees.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vanilla JS Accordion Script & Intersection Observer -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accordion functionality
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            // Close other open items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            // Toggle current item
            item.classList.toggle('active');
        });
    });

    // Fade in on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
});
</script>

<?php require_once 'includes/footer.php'; ?>
