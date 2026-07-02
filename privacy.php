<?php
session_start();
$page_title = 'Privacy Policy';
$page_description = 'FoodSaver Privacy Policy. Learn how we collect, use, and protect your data.';
$active_page = '';
require_once 'includes/header.php';
?>

<section style="background: #0f172a; padding: 100px 0 60px;">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-4">Privacy Policy</h1>
        <p class="lead text-light opacity-75">Last updated: <?= date('F d, Y') ?></p>
    </div>
</section>

<section class="py-5" style="background: var(--fs-white);">
    <div class="container" style="max-width: 800px;">
        <div class="fs-card p-5 shadow-sm">
            <h3 class="fw-bold mb-3">1. Information We Collect</h3>
            <p class="text-muted mb-4">We collect information you provide directly to us, such as when you create or modify your account, request on-demand services, contact customer support, or otherwise communicate with us. This information may include: name, email, phone number, postal address, profile picture, payment method, items requested (for delivery services), delivery notes, and other information you choose to provide.</p>

            <h3 class="fw-bold mb-3">2. How We Use Your Information</h3>
            <p class="text-muted mb-4">We may use the information we collect about you to: Provide, maintain, and improve our Services, including, for example, to facilitate payments, send receipts, provide products and services you request (and send related information), develop new features, provide customer support to Users and Drivers, develop safety features, authenticate users, and send product updates and administrative messages.</p>

            <h3 class="fw-bold mb-3">3. Sharing of Information</h3>
            <p class="text-muted mb-4">We may share the information we collect about you as described in this Statement or as described at the time of collection or sharing, including as follows: With third parties to provide you a service you requested through a partnership or promotional offering made by a third party or us; With the general public if you submit content in a public forum, such as blog comments, social media posts, or other features of our Services that are viewable by the general public.</p>

            <h3 class="fw-bold mb-3">4. Security</h3>
            <p class="text-muted mb-4">We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access, disclosure, alteration and destruction.</p>

            <h3 class="fw-bold mb-3">5. Contact Us</h3>
            <p class="text-muted mb-0">If you have any questions about this Privacy Statement, please contact us at: <a href="mailto:contact@foodsaver.lk" class="text-decoration-none" style="color: var(--fs-green-dark); font-weight: bold;">contact@foodsaver.lk</a>.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
