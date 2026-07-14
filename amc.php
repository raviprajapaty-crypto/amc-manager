<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
refresh_amc_statuses($pdo);

$page_title = 'AMC Contracts';
$active = 'amc';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM amc_contracts WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: amc.php?msg=deleted');
    exit;
}

// Quick technician assign from list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_amc_id'])) {
    $amc_id = (int) $_POST['assign_amc_id'];
    $tech_id = $_POST['technician_id'] !== '' ? (int) $_POST['technician_id'] : null;
    $pdo->prepare("UPDATE amc_contracts SET technician_id = ? WHERE id = ?")->execute([$tech_id, $amc_id]);
    header('Location: amc.php?msg=assigned');
    exit;
}

$amcs = $pdo->query("
    SELECT a.*, c.name AS customer_name, c.mobile, u.name AS tech_name
    FROM amc_contracts a
    JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON a.technician_id = u.id
    ORDER BY a.id DESC
")->fetchAll();

$technicians = $pdo->query("SELECT id, name FROM users WHERE role='technician' ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
    <?php
        $m = ['deleted'=>'AMC delete ho gaya.','saved'=>'AMC save ho gaya.','assigned'=>'Technician assign ho gaya.'];
        echo $m[$_GET['msg']] ?? '';
    ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>🛠 All AMC Contracts (<?= count($amcs) ?>)</h3>
        <a href="amc_form.php" class="btn">+ New AMC Contract</a>
    </div>
    <div class="table-wrap">
    <table>
        <tr>
            <th>Customer</th><th>Mobile</th><th>Start</th><th>End</th>
            <th>Visits (Used/Total)</th><th>Technician</th><th>Status</th><th>Actions</th>
        </tr>
        <?php if (!$amcs): ?>
            <tr><td colspan="8" style="text-align:center;color:#6b7280;">Koi AMC contract nahi mila</td></tr>
        <?php endif; ?>
        <?php foreach ($amcs as $a): ?>
            <tr>
                <td><?= e($a['customer_name']) ?></td>
                <td><a href="https://wa.me/91<?= e($a['mobile']) ?>" class="whatsapp-link" target="_blank">📞 <?= e($a['mobile']) ?></a></td>
                <td><?= e($a['start_date']) ?></td>
                <td><?= e($a['end_date']) ?></td>
                <td><?= e($a['used_visits']) ?> / <?= e($a['total_visits']) ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:4px;">
                        <input type="hidden" name="assign_amc_id" value="<?= $a['id'] ?>">
                        <select name="technician_id" onchange="this.form.submit()" style="padding:4px;border-radius:6px;border:1px solid #e5e7eb;font-size:12px;">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($technicians as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $a['technician_id']==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= ucfirst(e($a['status'])) ?></span></td>
                <td class="actions">
                    <a href="amc_form.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    <a href="amc.php?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
