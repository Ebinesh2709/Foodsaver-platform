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
    'SELECT r.id, r.status, r.created_at,
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
require_once 'includes/header.php';
?>

<main>
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4">My Reservations</h1>

    <?php if (isset($_GET['reserved']) && $_GET['reserved'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>Reservation created! The business will confirm it shortly.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($reservations)): ?>
        <div class="text-center py-5">
            <div class="display-1">📅</div>
            <h2 class="h5 mt-3">No reservations yet</h2>
            <p class="text-muted">Browse available food listings and reserve something delicious!</p>
            <a href="browse_listings.php" class="btn btn-success px-4">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($reservations as $res): ?>
            <?php
            $status = $res['status'];
            [$badge_class, $badge_text, $extra_text] = match($status) {
                'pending'   => ['bg-warning text-dark', 'Pending',   ''],
                'confirmed' => ['bg-success',           'Confirmed', '<div class="text-success fw-semibold small mt-1"><i class="bi bi-bag-check me-1"></i>Ready for pickup!</div>'],
                'collected' => ['bg-secondary',         'Collected', ''],
                'cancelled' => ['bg-danger',            'Cancelled', ''],
                default     => ['bg-secondary',          $status,    ''],
            };
            ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="h6 fw-bold mb-0"><?= htmlspecialchars($res['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                        </div>
                        <?= $extra_text ?>

                        <p class="small text-muted mb-1">
                            <i class="bi bi-shop me-1"></i><?= htmlspecialchars($res['business_name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($res['area']): ?> · <?= htmlspecialchars($res['area'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                        </p>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars(ucfirst($res['category']), ENT_QUOTES, 'UTF-8') ?>
                            &nbsp;|&nbsp;
                            <?= get_urgency_badge_html($res['urgency_score']) ?>
                        </p>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-currency-exchange me-1"></i>
                            <span class="fw-bold text-success">LKR <?= htmlspecialchars(number_format((float)$res['discounted_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                        </p>
                        <p class="small text-muted mb-2">
                            <i class="bi bi-clock me-1"></i>Pickup by: <strong><?= htmlspecialchars(date('d M Y, H:i', strtotime($res['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></strong>
                        </p>
                        <p class="small text-muted mb-0">
                            Reserved: <?= htmlspecialchars(date('d M Y, H:i', strtotime($res['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <?php if ($status === 'pending'): ?>
                            <div class="mt-3">
                                <form method="post" action="cancel_reservation.php"
                                      onsubmit="return confirm('Cancel this reservation? The item will become available again.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                                    <button type="submit" id="btn-cancel-<?= (int)$res['id'] ?>" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-circle me-1"></i>Cancel Reservation
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</main>

<?php require_once 'includes/footer.php'; ?>
