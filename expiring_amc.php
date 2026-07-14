<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
refresh_amc_statuses($pdo);

$page_title = 'Expiring AMCs';
$active = 'expiring';

$amcs = $pdo->query("
    SELECT a.*, c.name AS customer_name, c.mobile, c.company_name, u.name AS tech_name
    FROM amc_contracts a
    JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON a.technician_id = u.id
    WHERE a.status IN ('expiring','expired')
    ORDER BY a.end_date ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header"><h3>⏰ Expiring / Expired AMC Contracts (<?= count($amcs) ?>)</h3></div>
    <div class="table-wrap">
    <table>
        <tr><th>Customer</th><th>Company</th><th>Mobile</th><th>End Date</th><th>Technician</th><th>Status</th><th>Action</th></tr>
        <?php if (!$amcs): ?>
            <tr><td colspan="7" style="text-align:center;color:#6b7280;">Sab AMC active hain 🎉</td></tr>
        <?php endif; ?>
        <?php foreach ($amcs as $a): ?>
            <tr>
                <td><?= e($a['customer_name']) ?></td>
                <td><?= e($a['company_name']) ?></td>
                <td><a href="https://wa.me/91<?= e($a['mobile']) ?>" class="whatsapp-link" target="_blank">📞 <?= e($a['mobile']) ?></a></td>
                <td><?= e($a['end_date']) ?></td>
                <td><?= e($a['tech_name'] ?? '-') ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= ucfirst(e($a['status'])) ?></span></td>
                <td><a href="amc_form.php?id=<?= $a['id'] ?>" class="btn btn-sm">Renew / Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
