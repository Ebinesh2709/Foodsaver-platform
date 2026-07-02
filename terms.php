<?php
session_start();
$page_title = 'Terms of Service';
$page_description = 'FoodSaver Terms of Service. Read our rules and guidelines for using the platform.';
$active_page = '';
require_once 'includes/header.php';
?>

<section style="background: #0f172a; padding: 100px 0 60px;">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-4">Terms of Service</h1>
        <p class="lead text-light opacity-75">Last updated: <?= date('F d, Y') ?></p>
    </div>
</section>

<section class="py-5" style="background: var(--fs-white);">
    <div class="container" style="max-width: 800px;">
        <div class="fs-card p-5 shadow-sm">
            <h3 class="fw-bold mb-3">1. Agreement to Terms</h3>
            <p class="text-muted mb-4">By accessing our platform, you agree to be bound by these Terms of Service and to use the Site in accordance with these Terms of Service, our Privacy Policy and any additional terms and conditions that may apply to specific sections of the Site or to products and services available through the Site.</p>

            <h3 class="fw-bold mb-3">2. User Responsibilities</h3>
            <p class="text-muted mb-4">You are responsible for your use of the Services, for any Content you provide, and for any consequences thereof, including the use of your Content by other users and our third party partners. You should only provide Content that you are comfortable sharing with others under these Terms.</p>

            <h3 class="fw-bold mb-3">3. Food Quality & Safety</h3>
            <p class="text-muted mb-4">Businesses listing food on FoodSaver are solely responsible for ensuring the food is safe for consumption and accurately described. FoodSaver acts only as a platform connecting businesses with consumers and does not guarantee the quality, safety, or legality of the items listed.</p>

            <h3 class="fw-bold mb-3">4. Limitation of Liability</h3>
            <p class="text-muted mb-4">In no event will FoodSaver, or its suppliers or licensors, be liable with respect to any subject matter of this agreement under any contract, negligence, strict liability or other legal or equitable theory for: (i) any special, incidental or consequential damages; (ii) the cost of procurement for substitute products or services; (iii) for interruption of use or loss or corruption of data.</p>

            <h3 class="fw-bold mb-3">5. Changes</h3>
            <p class="text-muted mb-0">We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
