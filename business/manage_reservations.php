<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header('Location: ../auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';

// Get business_id
$stmt = $pdo->prepare('SELECT id FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$biz_row = $stmt->fetch();

if (!$biz_row) {
    header('Location: dashboard.php');
    exit;
}
$business_id = $biz_row['id'];

// --- POST: handle confirm / collect actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $reservation_id = (int)($_POST['reservation_id'] ?? 0);
    $action         = trim($_POST['action'] ?? '');

    // Verify reservation belongs to this business
    $stmt2 = $pdo->prepare(
        'SELECT r.id, r.status, r.listing_id
         FROM reservations r
         JOIN food_listings fl ON r.listing_id = fl.id
         WHERE r.id = ? AND fl.business_id = ?'
    );
    $stmt2->execute([$reservation_id, $business_id]);
    $reservation = $stmt2->fetch();

    if ($reservation) {
        if ($action === 'confirm' && $reservation['status'] === 'pending') {
            $stmt3 = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
            $stmt3->execute([$reservation_id]);

        } elseif ($action === 'collect' && $reservation['status'] === 'confirmed') {
            try {
                $pdo->beginTransaction();

                $stmt3 = $pdo->prepare("UPDATE reservations SET status = 'collected' WHERE id = ?");
                $stmt3->execute([$reservation_id]);

                $stmt4 = $pdo->prepare("UPDATE food_listings SET status = 'collected' WHERE id = ?");
                $stmt4->execute([$reservation['listing_id']]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
    }

    header('Location: manage_reservations.php');
    exit;
}

// --- GET: display reservations ---
$stmt5 = $pdo->prepare(
    'SELECT r.id, r.status, r.created_at, r.quantity,
            fl.title, fl.id AS listing_id, fl.discounted_price,
            u.name AS customer_name, u.phone AS customer_phone
     FROM reservations r
     JOIN food_listings fl ON r.listing_id = fl.id
     JOIN users u ON r.user_id = u.id
     WHERE fl.business_id = ?
     ORDER BY r.created_at DESC'
);
$stmt5->execute([$business_id]);
$reservations = $stmt5->fetchAll();

$page_title  = 'Manage Reservations';
$active_page = 'manage_reservations';
$css_prefix  = '../';
require_once '../includes/header.php';
?>

<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-calendar-check me-2"></i>Manage Reservations</h1>
        <p>Review, confirm and mark collections for customer reservations</p>
    </div>
</div>

<div class="container premium-section">

    <?php if (empty($reservations)): ?>
        <div class="fs-empty">
            <span class="empty-icon">📅</span>
            <h2>No reservations yet</h2>
            <p>Reservations on your listings will appear here once customers start reserving.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive fs-table">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Food Item</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Reserved At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $res): ?>
                    <?php
                    switch($res['status']) {
                        case 'pending':   $status_badge = '<span class="status-badge status-pending">Pending</span>'; break;
                        case 'confirmed': $status_badge = '<span class="status-badge status-confirmed">Confirmed</span>'; break;
                        case 'collected': $status_badge = '<span class="status-badge status-collected">Collected</span>'; break;
                        case 'cancelled': $status_badge = '<span class="status-badge status-cancelled">Cancelled</span>'; break;
                        default:          $status_badge = '<span class="status-badge status-collected">' . htmlspecialchars($res['status'], ENT_QUOTES, 'UTF-8') . '</span>'; break;
                    }
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($res['title'], ENT_QUOTES, 'UTF-8') ?> 
                            <br>
                            <small class="text-muted" style="font-weight:normal;">Qty: <?= (int)$res['quantity'] ?></small>
                            <br>
                            <small class="text-success" style="font-weight:700;">Total: LKR <?= number_format((float)($res['discounted_price'] * $res['quantity']), 2) ?></small>
                        </td>
                        <td><?= htmlspecialchars($res['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($res['customer_phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small text-nowrap"><?= htmlspecialchars(date('d M Y, H:i', strtotime($res['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $status_badge ?></td>
                        <td>
                            <?php if ($res['status'] === 'pending'): ?>
                                <form method="post" action="manage_reservations.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-fs-primary" style="padding:0.3rem 0.85rem; font-size:0.8rem;">
                                        <i class="bi bi-check-circle me-1"></i>Confirm
                                    </button>
                                </form>
                            <?php elseif ($res['status'] === 'confirmed'): ?>
                                <form method="post" action="manage_reservations.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                                    <input type="hidden" name="action" value="collect">
                                    <button type="submit" class="btn btn-fs-outline" style="padding:0.3rem 0.85rem; font-size:0.8rem;">
                                        <i class="bi bi-bag-check me-1"></i>Mark Collected
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
