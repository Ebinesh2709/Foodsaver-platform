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
$css_prefix  = '../';
require_once '../includes/header.php';
?>

<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-shield-lock me-2"></i>Admin Dashboard</h1>
        <p>Platform overview and recent activity</p>
    </div>
</div>

<div class="container pb-5">

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <?php
        $stats = [
            ['label' => 'Total Users',        'value' => $total_users,        'icon' => '👥', 'color' => 'blue'],
            ['label' => 'Total Businesses',   'value' => $total_businesses,   'icon' => '🏪', 'color' => 'green'],
            ['label' => 'Total Listings',     'value' => $total_listings,     'icon' => '📋', 'color' => 'amber'],
            ['label' => 'Total Reservations', 'value' => $total_reservations, 'icon' => '📅', 'color' => 'red'],
        ];
        foreach ($stats as $s):
        ?>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon <?= $s['color'] ?>"><?= $s['icon'] ?></div>
                <div>
                    <div class="stat-value"><?= $s['value'] ?></div>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Users -->
    <h2 class="h6 fw-bold mb-3" style="font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--fs-text-muted);">RECENT USERS</h2>
    <div class="table-responsive fs-table mb-5">
        <table class="table table-hover align-middle mb-0">
            <thead>
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
                    <td style="font-size:0.8rem; color:var(--fs-text-muted);"><?= (int)$u['id'] ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="font-size:0.84rem;"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="filter-chip"><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td style="font-size:0.8rem; white-space:nowrap;"><?= htmlspecialchars(date('d M Y', strtotime($u['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Listings -->
    <h2 class="h6 fw-bold mb-3" style="font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--fs-text-muted);">RECENT LISTINGS</h2>
    <div class="table-responsive fs-table">
        <table class="table table-hover align-middle mb-0">
            <thead>
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
                    <td style="font-size:0.8rem; color:var(--fs-text-muted);"><?= (int)$l['id'] ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($l['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="font-size:0.84rem;"><?= htmlspecialchars($l['business_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= get_urgency_badge_html($l['urgency_score']) ?></td>
                    <td><span class="filter-chip"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$l['status'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td style="font-size:0.8rem; white-space:nowrap;"><?= htmlspecialchars(date('d M Y', strtotime($l['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
