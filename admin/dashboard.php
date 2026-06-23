<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';

// Summary counts
$total_users        = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_businesses   = (int)$pdo->query('SELECT COUNT(*) FROM businesses')->fetchColumn();
$total_listings     = (int)$pdo->query('SELECT COUNT(*) FROM food_listings')->fetchColumn();
$total_reservations = (int)$pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();

// Recent 10 users
$users = $pdo->query(
    'SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10'
)->fetchAll();

// Recent 10 listings
$listings = $pdo->query(
    'SELECT fl.id, fl.title, b.business_name, fl.urgency_score, fl.status, fl.created_at
     FROM food_listings fl
     JOIN businesses b ON fl.business_id = b.id
     ORDER BY fl.created_at DESC LIMIT 10'
)->fetchAll();

$page_title  = 'Admin Dashboard';
$active_page = 'dashboard';
require_once '../includes/header.php';
?>

<main>
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4">Admin Dashboard</h1>

    <!-- Stats -->
    <div class="row g-3 mb-5">
        <?php
        $stats = [
            ['label' => 'Total Users',        'value' => $total_users,        'icon' => '👥', 'color' => 'primary'],
            ['label' => 'Total Businesses',   'value' => $total_businesses,   'icon' => '🏪', 'color' => 'success'],
            ['label' => 'Total Listings',     'value' => $total_listings,     'icon' => '📋', 'color' => 'info'],
            ['label' => 'Total Reservations', 'value' => $total_reservations, 'icon' => '📅', 'color' => 'warning'],
        ];
        foreach ($stats as $s):
        ?>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-<?= $s['color'] ?> shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-1"><?= $s['icon'] ?></div>
                    <div>
                        <div class="display-6 fw-bold text-<?= $s['color'] ?>"><?= $s['value'] ?></div>
                        <div class="text-muted small"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Users -->
    <h2 class="h5 fw-bold mb-3">Recent Users</h2>
    <div class="table-responsive mb-5">
        <table class="table table-hover align-middle table-sm">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="small text-nowrap"><?= htmlspecialchars(date('d M Y', strtotime($u['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Listings -->
    <h2 class="h5 fw-bold mb-3">Recent Listings</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle table-sm">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Business</th>
                    <th>Urgency</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($listings as $l): ?>
                <tr>
                    <td><?= (int)$l['id'] ?></td>
                    <td><?= htmlspecialchars($l['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($l['business_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= get_urgency_badge_html($l['urgency_score']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$l['status'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="small text-nowrap"><?= htmlspecialchars(date('d M Y', strtotime($l['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>
