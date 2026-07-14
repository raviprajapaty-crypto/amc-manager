<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$page_title = 'Invoices';
$active = 'invoices';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: invoices.php?msg=deleted');
    exit;
}

if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("SELECT status FROM invoices WHERE id = ?");
    $stmt->execute([(int)$_GET['toggle']]);
    $row = $stmt->fetch();
    if ($row) {
        $new = $row['status'] === 'paid' ? 'unpaid' : 'paid';
        $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$new, (int)$_GET['toggle']]);
    }
    header('Location: invoices.php');
    exit;
}

$invoices = $pdo->query("
    SELECT i.*, c.name AS customer_name, c.mobile
    FROM invoices i JOIN customers c ON i.customer_id = c.id
    ORDER BY i.id DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= $_GET['msg']==='deleted' ? 'Invoice delete ho gaya.' : 'Invoice save ho gaya.' ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>🧾 All Invoices (<?= count($invoices) ?>)</h3>
        <a href="invoice_form.php" class="btn">+ New Invoice</a>
    </div>
    <div class="table-wrap">
    <table>
        <tr><th>Invoice #</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
        <?php if (!$invoices): ?>
            <tr><td colspan="6" style="text-align:center;color:#6b7280;">Koi invoice nahi mila</td></tr>
        <?php endif; ?>
        <?php foreach ($invoices as $inv): ?>
            <tr>
                <td><?= e($inv['invoice_no']) ?></td>
                <td><?= e($inv['customer_name']) ?></td>
                <td><?= e($inv['invoice_date']) ?></td>
                <td>₹<?= number_format($inv['amount'],2) ?></td>
                <td><span class="badge badge-<?= e($inv['status']) ?>"><?= ucfirst(e($inv['status'])) ?></span></td>
                <td class="actions">
                    <a href="invoice_view.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline">View</a>
                    <a href="invoices.php?toggle=<?= $inv['id'] ?>" class="btn btn-sm btn-success">Mark <?= $inv['status']==='paid'?'Unpaid':'Paid' ?></a>
                    <a href="invoices.php?delete=<?= $inv['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
