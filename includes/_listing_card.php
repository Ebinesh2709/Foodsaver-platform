<?php
/**
 * Listing card partial — included by browse_listings.php.
 * Expects $listing array with all food_listings + business columns.
 */
$expiry_alert = '';
if ($listing['urgency_score'] === 'high') {
    $expiry_alert = get_expiry_alert($listing['pickup_end'], $listing['urgency_score']);
}
?>
<div class="col">
    <div class="listing-card urgency-<?= htmlspecialchars($listing['urgency_score'], ENT_QUOTES, 'UTF-8') ?>">

        <!-- Image -->
        <div class="card-img-wrap">
            <?php if ($listing['image']): ?>
                <img src="uploads/<?= htmlspecialchars($listing['image'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div class="img-placeholder">🍱</div>
            <?php endif; ?>
            <div class="urgency-ribbon">
                <?= get_urgency_badge_html($listing['urgency_score']) ?>
                <span class="badge-category"><?= htmlspecialchars(ucfirst($listing['category']), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">
            <?php if ($expiry_alert): ?>
                <p class="expiry-alert"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($expiry_alert, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (!empty($listing['ai_summary'])): ?>
                <p class="ai-blurb"><?= htmlspecialchars($listing['ai_summary'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <h2 class="listing-title"><?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?></h2>

            <p class="listing-business">
                <i class="bi bi-shop"></i>
                <?= htmlspecialchars($listing['business_name'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($listing['area']): ?>
                    &middot; <?= htmlspecialchars($listing['area'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </p>

            <p class="meta-row" style="margin-bottom:0.5rem; color:#555; font-size:0.8rem;">
                <?= htmlspecialchars(mb_strimwidth($listing['description'] ?? '', 0, 90, '…'), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="price-row">
                <span class="price-now">LKR <?= htmlspecialchars(number_format((float)$listing['discounted_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="price-was">LKR <?= htmlspecialchars(number_format((float)$listing['original_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="ms-auto" style="font-size:0.75rem; background:#f0f4f2; padding:0.2em 0.6em; border-radius:50px; font-weight:600; color:#555;">
                    Qty: <?= (int)$listing['quantity'] ?>
                </span>
            </div>

            <p class="meta-row">
                <i class="bi bi-clock"></i>
                Pickup by <strong><?= htmlspecialchars(date('D d M, g:i A', strtotime($listing['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></strong>
            </p>

            <div class="card-footer-area">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                    <form method="post" action="reserve_listing.php" id="reserve-form-<?= (int)$listing['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                        <button type="submit" id="btn-reserve-<?= (int)$listing['id'] ?>" class="btn btn-fs-primary w-100">
                            <i class="bi bi-bag-plus me-1"></i>Reserve Now
                        </button>
                    </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <a href="auth/login.php" class="btn btn-fs-outline w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login to Reserve
                    </a>
                <?php else: ?>
                    <button class="btn w-100" style="background:#f0f0f0; color:#999; cursor:not-allowed;" disabled>Reserve</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
