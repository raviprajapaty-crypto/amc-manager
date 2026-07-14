<?php
require_once __DIR__ . '/../includes/auth.php';
require_technician();
refresh_amc_statuses($pdo);

$page_title = 'My Dashboard';
$active = 'dashboard';
$tech_id = $_SESSION['user_id'];

$assigned_count = $pdo->prepare("SELECT COUNT(*) c FROM amc_contracts WHERE technician_id = ?");
$assigned_count->execute([$tech_id]);
$assigned_count = $assigned_count->fetch()['c'];

$pending_visits = $pdo->prepare("SELECT COUNT(*) c FROM amc_contracts WHERE technician_id = ? AND used_visits < total_visits");
$pending_visits->execute([$tech_id]);
$pending_visits = $pending_visits->fetch()['c'];

$my_visits = $pdo->prepare("SELECT COUNT(*) c FROM visits WHERE technician_id = ?");
$my_visits->execute([$tech_id]);
$my_visits = $my_visits->fetch()['c'];

$sites = $pdo->prepare("
    SELECT a.*, c.name AS customer_name, c.mobile
    FROM amc_contracts a JOIN customers c ON a.customer_id = c.id
    WHERE a.technician_id = ?
    ORDER BY a.end_date ASC LIMIT 6
");
$sites->execute([$tech_id]);
$sites = $sites->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card"><div class="num"><?= $assigned_count ?></div><div class="label">Assigned Sites</div></div>
    <div class="stat-card warning"><div class="num"><?= $pending_visits ?></div><div class="label">Sites With Visits Remaining</div></div>
    <div class="stat-card success"><div class="num"><?= $my_visits ?></div><div class="label">Total Visits Done</div></div>
</div>

<div class="card">
    <div class="card-header"><h3>📍 My Assigned Sites</h3><a href="assigned_sites.php" class="btn btn-sm btn-outline">View All</a></div>
    <div class="table-wrap">
    <table>
        <tr><th>Customer</th><th>Mobile</th><th>AMC End Date</th><th>Remaining Visits</th><th>Status</th><th>Action</th></tr>
        <?php if (!$sites): ?>
            <tr><td colspan="6" style="text-align:center;color:#6b7280;">Abhi tak koi site assign nahi hui</td></tr>
        <?php endif; ?>
        <?php foreach ($sites as $s): ?>
            <tr>
                <td><?= e($s['customer_name']) ?></td>
                <td><a href="https://wa.me/91<?= e($s['mobile']) ?>" class="whatsapp-link" target="_blank">📞 <?= e($s['mobile']) ?></a></td>
                <td><?= e($s['end_date']) ?></td>
                <td><?= max(0, $s['total_visits'] - $s['used_visits']) ?></td>
                <td><span class="badge badge-<?= e($s['status']) ?>"><?= ucfirst(e($s['status'])) ?></span></td>
                <td><a href="visit_report.php?amc_id=<?= $s['id'] ?>" class="btn btn-sm">Add Visit Report</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
