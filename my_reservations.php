<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';

$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT r.id, r.status, r.created_at, r.quantity,
            fl.title, fl.category, fl.discounted_price,
            fl.pickup_end, fl.urgency_score, fl.ai_summary,
            b.business_name, b.area
     FROM reservations r
     JOIN food_listings fl ON r.listing_id = fl.id
     JOIN businesses b ON fl.business_id = b.id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC'
);
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();

$page_title  = 'My Reservations';
$active_page = 'reservations';
$css_prefix  = '';
require_once 'includes/header.php';
?>

<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-bag-check me-2"></i>My Reservations</h1>
        <p>Track and manage your current food reservations</p>
    </div>
</div>

<div class="container premium-section">

    <?php if (isset($_GET['reserved']) && $_GET['reserved'] == '1'): ?>
        <div class="fs-alert-success mb-4">
            <i class="bi bi-check-circle me-2"></i>Reservation created! The business will confirm it shortly.
        </div>
    <?php endif; ?>

    <?php if (empty($reservations)): ?>
        <div class="fs-empty">
            <span class="empty-icon">📅</span>
            <h2>No reservations yet</h2>
            <p>Browse available food listings and reserve something delicious before it expires!</p>
            <a href="browse_listings.php" class="btn btn-fs-primary px-4">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($reservations as $res): ?>
            <?php
            $status = $res['status'];
            switch($status) {
                case 'pending':   $badge_html = '<span class="status-badge status-pending">Pending</span>'; break;
                case 'confirmed': $badge_html = '<span class="status-badge status-confirmed">Confirmed</span>'; break;
                case 'collected': $badge_html = '<span class="status-badge status-collected">Collected</span>'; break;
                case 'cancelled': $badge_html = '<span class="status-badge status-cancelled">Cancelled</span>'; break;
                default:          $badge_html = '<span class="status-badge status-collected">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>'; break;
            }
            ?>
            <div class="col-md-6">
                <div class="fs-card" style="padding:1.4rem 1.5rem;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h2 class="h6 fw-bold mb-0" style="font-size:1rem; max-width:70%;">
                            <?= htmlspecialchars($res['title'], ENT_QUOTES, 'UTF-8') ?> 
                            <span class="badge" style="background:#e8f5ee; color:var(--fs-green); font-size:0.75rem; border:1px solid var(--fs-green-light); vertical-align:middle; margin-left:0.5rem;">Qty: <?= (int)$res['quantity'] ?></span>
                        </h2>
                        <?= $badge_html ?>
                    </div>

                    <?php if ($status === 'pending'): ?>
                        <div class="mb-3 p-2" style="background:#fff3cd; border-left:4px solid #ffc107; font-size:0.85rem; color:#664d03; border-radius:4px;">
                            <i class="bi bi-hourglass-split me-1"></i><strong>Thank you for your reservation!</strong> The business will accept it soon.
                        </div>
                    <?php elseif ($status === 'confirmed'): ?>
                        <div class="mb-3 p-2" style="background:#d1e7dd; border-left:4px solid #198754; font-size:0.85rem; color:#0f5132; border-radius:4px;">
                            <i class="bi bi-bag-check me-1"></i><strong>Confirmed!</strong> Your food is ready. Please collect it before the pickup time.
                        </div>
                    <?php elseif ($status === 'collected'): ?>
                        <div class="mb-3 p-2" style="background:#e8f5ee; border-left:4px solid var(--fs-green); font-size:0.85rem; color:var(--fs-green-dark); border-radius:4px;">
                            <i class="bi bi-heart-fill text-danger me-1"></i><strong>Thank you for your purchase!</strong> We hope you enjoyed the food and helping the community.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($res['ai_summary'])): ?>
                        <p style="font-size:0.78rem; font-style:italic; color:var(--fs-green-mid); border-left:2px solid var(--fs-green-light); padding-left:0.6rem; margin-bottom:0.5rem;"><?= htmlspecialchars($res['ai_summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <p style="font-size:0.8rem; color:var(--fs-text-muted); margin-bottom:0.3rem;">
                        <i class="bi bi-shop me-1"></i><?= htmlspecialchars($res['business_name'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($res['area']): ?> &middot; <?= htmlspecialchars($res['area'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                    </p>
                    <p style="font-size:0.8rem; color:var(--fs-text-muted); margin-bottom:0.3rem;">
                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars(ucfirst($res['category']), ENT_QUOTES, 'UTF-8') ?>
                        &nbsp;&middot;&nbsp;
                        <?= get_urgency_badge_html($res['urgency_score']) ?>
                    </p>
                    <p style="font-size:0.9rem; font-weight:800; color:var(--fs-green); margin-bottom:0.3rem;">
                        Total: LKR <?= number_format((float)($res['discounted_price'] * $res['quantity']), 2) ?>
                        <span style="font-size:0.75rem; color:var(--fs-text-muted); font-weight:normal;">(LKR <?= number_format((float)$res['discounted_price'], 2) ?> each)</span>
                    </p>
                    <p style="font-size:0.78rem; color:var(--fs-text-muted); margin-bottom:0.3rem;">
                        <i class="bi bi-clock me-1"></i>Pickup by: <strong><?= htmlspecialchars(date('D d M, g:i A', strtotime($res['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                    <p style="font-size:0.75rem; color:var(--fs-text-muted); margin-bottom:0.75rem;">
                        Reserved: <?= htmlspecialchars(date('d M Y, H:i', strtotime($res['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <?php if ($status === 'pending'): ?>
                        <form method="post" action="cancel_reservation.php"
                              onsubmit="return confirm('Cancel this reservation? The item will become available again.');">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                            <button type="submit" id="btn-cancel-<?= (int)$res['id'] ?>" class="btn btn-fs-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Cancel Reservation
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
