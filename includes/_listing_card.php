<?php
/**
 * Listing card partial — included by browse_listings.php.
 * Expects $listing array in scope with all food_listings + business columns.
 * Expects session_start() already called by the including file.
 */
$expiry_alert = '';
if ($listing['urgency_score'] === 'high') {
    $expiry_alert = get_expiry_alert($listing['pickup_end'], $listing['urgency_score']);
}
?>
<div class="col">
    <div class="card h-100 shadow-sm border-0 urgency-<?= htmlspecialchars($listing['urgency_score'], ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($listing['image']): ?>
            <img src="uploads/<?= htmlspecialchars($listing['image'], ENT_QUOTES, 'UTF-8') ?>"
                 class="card-img-top" alt="<?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?>"
                 style="height: 180px; object-fit: cover;">
        <?php else: ?>
            <div class="text-center bg-light d-flex align-items-center justify-content-center" style="height:180px; font-size:4rem;">🍱</div>
        <?php endif; ?>

        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <?= get_urgency_badge_html($listing['urgency_score']) ?>
                <span class="badge bg-info text-dark"><?= htmlspecialchars(ucfirst($listing['category']), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if ($expiry_alert): ?>
                <p class="text-danger small mb-1"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($expiry_alert, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (!empty($listing['ai_summary'])): ?>
                <p class="small fst-italic text-muted mb-1"><?= htmlspecialchars($listing['ai_summary'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <h2 class="h6 fw-bold mb-1"><?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="small text-muted mb-1">
                <i class="bi bi-shop me-1"></i><?= htmlspecialchars($listing['business_name'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($listing['area']): ?>
                    · <?= htmlspecialchars($listing['area'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </p>
            <p class="small text-muted mb-2">
                <?= htmlspecialchars(mb_strimwidth($listing['description'] ?? '', 0, 100, '…'), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="fw-bold text-success fs-5">LKR <?= htmlspecialchars(number_format((float)$listing['discounted_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-muted text-decoration-line-through ms-1 small">LKR <?= htmlspecialchars(number_format((float)$listing['original_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <span class="badge bg-light text-dark border">Qty: <?= (int)$listing['quantity'] ?></span>
            </div>

            <p class="small text-muted mb-3">
                <i class="bi bi-clock me-1"></i>Pickup by:
                <strong><?= htmlspecialchars(date('D d M, g:i A', strtotime($listing['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></strong>
            </p>

            <div class="mt-auto">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                    <form method="post" action="reserve_listing.php" id="reserve-form-<?= (int)$listing['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                        <button type="submit" id="btn-reserve-<?= (int)$listing['id'] ?>" class="btn btn-success w-100">
                            <i class="bi bi-bag-plus me-1"></i>Reserve Now
                        </button>
                    </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <a href="auth/login.php" class="btn btn-outline-success w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login to Reserve
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>Reserve</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
