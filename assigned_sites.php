<?php
require_once __DIR__ . '/../includes/auth.php';
require_technician();
refresh_amc_statuses($pdo);

$page_title = 'Assigned Sites';
$active = 'sites';
$tech_id = $_SESSION['user_id'];

$sites = $pdo->prepare("
    SELECT a.*, c.name AS customer_name, c.company_name, c.mobile, c.address, c.total_cameras
    FROM amc_contracts a JOIN customers c ON a.customer_id = c.id
    WHERE a.technician_id = ?
    ORDER BY a.end_date ASC
");
$sites->execute([$tech_id]);
$sites = $sites->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg']) && $_GET['msg']==='visit_added'): ?>
    <div class="alert alert-success">Visit report save ho gaya ✅</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>📍 All My Assigned Sites (<?= count($sites) ?>)</h3></div>
    <div class="table-wrap">
    <table>
        <tr><th>Customer</th><th>Company</th><th>Address</th><th>Cameras</th><th>Mobile</th><th>Visits</th><th>Status</th><th>Action</th></tr>
        <?php if (!$sites): ?>
            <tr><td colspan="8" style="text-align:center;color:#6b7280;">Koi site assign nahi hui hai</td></tr>
        <?php endif; ?>
        <?php foreach ($sites as $s): ?>
            <tr>
                <td><?= e($s['customer_name']) ?></td>
                <td><?= e($s['company_name']) ?></td>
                <td><?= e(mb_strimwidth($s['address'] ?? '', 0, 30, '...')) ?></td>
                <td><?= e($s['total_cameras']) ?></td>
                <td><a href="https://wa.me/91<?= e($s['mobile']) ?>" class="whatsapp-link" target="_blank">📞 <?= e($s['mobile']) ?></a></td>
                <td><?= e($s['used_visits']) ?> / <?= e($s['total_visits']) ?></td>
                <td><span class="badge badge-<?= e($s['status']) ?>"><?= ucfirst(e($s['status'])) ?></span></td>
                <td><a href="visit_report.php?amc_id=<?= $s['id'] ?>" class="btn btn-sm">Add Visit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
