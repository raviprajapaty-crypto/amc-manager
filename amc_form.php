<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$amc = [
    'customer_id'=>'', 'start_date'=>date('Y-m-d'), 'end_date'=>date('Y-m-d', strtotime('+1 year')),
    'total_visits'=>4, 'used_visits'=>0, 'technician_id'=>'', 'amc_amount'=>0
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM amc_contracts WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $amc = $found;
}

$page_title = $id ? 'Edit AMC Contract' : 'New AMC Contract';
$active = 'amc';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int) $_POST['customer_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_visits = (int) $_POST['total_visits'];
    $used_visits = (int) $_POST['used_visits'];
    $technician_id = $_POST['technician_id'] !== '' ? (int) $_POST['technician_id'] : null;
    $amc_amount = (float) $_POST['amc_amount'];

    if (!$customer_id || !$start_date || !$end_date) {
        $error = 'Customer, start date aur end date required hai.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE amc_contracts SET customer_id=?, start_date=?, end_date=?, total_visits=?, used_visits=?, technician_id=?, amc_amount=? WHERE id=?");
            $stmt->execute([$customer_id, $start_date, $end_date, $total_visits, $used_visits, $technician_id, $amc_amount, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO amc_contracts (customer_id, start_date, end_date, total_visits, used_visits, technician_id, amc_amount) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$customer_id, $start_date, $end_date, $total_visits, $used_visits, $technician_id, $amc_amount]);
        }
        header('Location: amc.php?msg=saved');
        exit;
    }
    $amc = $_POST;
}

$customers = $pdo->query("SELECT id, name, company_name FROM customers ORDER BY name")->fetchAll();
$technicians = $pdo->query("SELECT id, name FROM users WHERE role='technician' ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <div class="card-header"><h3><?= $id ? '✏️ Edit AMC Contract' : '➕ New AMC Contract' ?></h3></div>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Customer *</label>
            <select name="customer_id" required>
                <option value="">-- Select Customer --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $amc['customer_id']==$c['id']?'selected':'' ?>>
                        <?= e($c['name']) ?><?= $c['company_name'] ? ' - '.e($c['company_name']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group"><label>AMC Start Date *</label><input type="date" name="start_date" value="<?= e($amc['start_date']) ?>" required></div>
            <div class="form-group"><label>AMC End Date *</label><input type="date" name="end_date" value="<?= e($amc['end_date']) ?>" required></div>
        </div>

        <div class="grid-2">
            <div class="form-group"><label>Total Visits</label><input type="number" name="total_visits" min="0" value="<?= e($amc['total_visits']) ?>"></div>
            <div class="form-group"><label>Used Visits</label><input type="number" name="used_visits" min="0" value="<?= e($amc['used_visits']) ?>"></div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Assign Technician</label>
                <select name="technician_id">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($technicians as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $amc['technician_id']==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>AMC Amount (₹)</label><input type="number" step="0.01" name="amc_amount" value="<?= e($amc['amc_amount']) ?>"></div>
        </div>

        <button type="submit" class="btn">Save AMC</button>
        <a href="amc.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
