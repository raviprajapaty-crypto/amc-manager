<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
refresh_amc_statuses($pdo);

$page_title = 'Reports';
$active = 'reports';

$status_counts = $pdo->query("SELECT status, COUNT(*) c FROM amc_contracts GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$revenue = $pdo->query("SELECT SUM(amount) t FROM invoices WHERE status='paid'")->fetch()['t'] ?? 0;
$pending_revenue = $pdo->query("SELECT SUM(amount) t FROM invoices WHERE status='unpaid'")->fetch()['t'] ?? 0;

$tech_performance = $pdo->query("
    SELECT u.name, COUNT(v.id) AS visit_count,
           SUM(CASE WHEN v.issue_resolved=1 THEN 1 ELSE 0 END) AS resolved_count
    FROM users u
    LEFT JOIN visits v ON v.technician_id = u.id
    WHERE u.role='technician'
    GROUP BY u.id
    ORDER BY visit_count DESC
")->fetchAll();

$all_visits = $pdo->query("
    SELECT v.*, c.name AS customer_name, u.name AS tech_name
    FROM visits v
    JOIN amc_contracts a ON v.amc_id = a.id
    JOIN customers c ON a.customer_id = c.id
    JOIN users u ON v.technician_id = u.id
    ORDER BY v.visit_date DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card success"><div class="num">₹<?= number_format($revenue,2) ?></div><div class="label">Revenue Collected</div></div>
    <div class="stat-card danger"><div class="num">₹<?= number_format($pending_revenue,2) ?></div><div class="label">Pending Payments</div></div>
    <div class="stat-card"><div class="num"><?= $status_counts['active'] ?? 0 ?></div><div class="label">Active AMCs</div></div>
    <div class="stat-card warning"><div class="num"><?= $status_counts['expiring'] ?? 0 ?></div><div class="label">Expiring AMCs</div></div>
    <div class="stat-card danger"><div class="num"><?= $status_counts['expired'] ?? 0 ?></div><div class="label">Expired AMCs</div></div>
</div>

<div class="card">
    <div class="card-header"><h3>🧑‍🔧 Technician Performance</h3></div>
    <div class="table-wrap">
    <table>
        <tr><th>Technician</th><th>Total Visits</th><th>Issues Resolved</th></tr>
        <?php foreach ($tech_performance as $t): ?>
            <tr>
                <td><?= e($t['name']) ?></td>
                <td><?= e($t['visit_count']) ?></td>
                <td><?= e($t['resolved_count']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>📋 All Visit Reports (<?= count($all_visits) ?>)</h3>
        <button class="btn btn-sm btn-outline" onclick="window.print()">🖨 Print Report</button>
    </div>
    <div class="table-wrap">
    <table>
        <tr><th>Date</th><th>Customer</th><th>Technician</th><th>Report</th><th>Status</th></tr>
        <?php foreach ($all_visits as $v): ?>
            <tr>
                <td><?= e($v['visit_date']) ?></td>
                <td><?= e($v['customer_name']) ?></td>
                <td><?= e($v['tech_name']) ?></td>
                <td><?= e(mb_strimwidth($v['report_text'] ?? '', 0, 50, '...')) ?></td>
                <td><?= $v['issue_resolved'] ? '<span class="badge badge-active">Resolved</span>' : '<span class="badge badge-expiring">Pending</span>' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
