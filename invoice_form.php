<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$page_title = 'New Invoice';
$active = 'invoices';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int) $_POST['customer_id'];
    $amc_id = $_POST['amc_id'] !== '' ? (int) $_POST['amc_id'] : null;
    $amount = (float) $_POST['amount'];
    $invoice_date = $_POST['invoice_date'];
    $notes = trim($_POST['notes']);
    $invoice_no = 'INV-' . date('Ymd') . '-' . rand(100,999);

    if (!$customer_id || !$amount || !$invoice_date) {
        $error = 'Customer, amount aur date required hai.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_no, customer_id, amc_id, amount, invoice_date, notes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$invoice_no, $customer_id, $amc_id, $amount, $invoice_date, $notes]);
        header('Location: invoices.php?msg=saved');
        exit;
    }
}

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$amcs = $pdo->query("SELECT id, customer_id, start_date, end_date FROM amc_contracts ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:600px;">
    <div class="card-header"><h3>➕ New Invoice</h3></div>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Customer *</label>
            <select name="customer_id" id="customer_id" required>
                <option value="">-- Select Customer --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Related AMC Contract (optional)</label>
            <select name="amc_id">
                <option value="">-- None --</option>
                <?php foreach ($amcs as $a): ?>
                    <option value="<?= $a['id'] ?>" data-customer="<?= $a['customer_id'] ?>">
                        AMC #<?= $a['id'] ?> (<?= e($a['start_date']) ?> to <?= e($a['end_date']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grid-2">
            <div class="form-group"><label>Amount (₹) *</label><input type="number" step="0.01" name="amount" required></div>
            <div class="form-group"><label>Invoice Date *</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="form-group"><label>Notes</label><textarea name="notes" rows="3" placeholder="e.g. AMC renewal charges, extra visit charges..."></textarea></div>

        <button type="submit" class="btn">Save Invoice</button>
        <a href="invoices.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
