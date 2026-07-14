<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT i.*, c.name AS customer_name, c.company_name, c.mobile, c.email, c.address
    FROM invoices i JOIN customers c ON i.customer_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$inv = $stmt->fetch();

if (!$inv) { die('Invoice not found.'); }

$page_title = 'Invoice ' . $inv['invoice_no'];
$active = 'invoices';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <h3>🧾 Invoice <?= e($inv['invoice_no']) ?></h3>
        <button class="btn btn-sm btn-outline" onclick="window.print()">🖨 Print / Download PDF</button>
    </div>

    <div class="grid-2" style="margin-bottom:20px;">
        <div>
            <strong>Bill To:</strong><br>
            <?= e($inv['customer_name']) ?><br>
            <?= e($inv['company_name']) ?><br>
            <?= e($inv['address']) ?><br>
            📞 <?= e($inv['mobile']) ?> <?= $inv['email'] ? ' | ✉ '.e($inv['email']) : '' ?>
        </div>
        <div style="text-align:right;">
            <strong>Invoice Date:</strong> <?= e($inv['invoice_date']) ?><br>
            <strong>Status:</strong> <span class="badge badge-<?= e($inv['status']) ?>"><?= ucfirst(e($inv['status'])) ?></span>
        </div>
    </div>

    <table>
        <tr><th>Description</th><th style="text-align:right;">Amount</th></tr>
        <tr>
            <td><?= e($inv['notes'] ?: 'AMC / Service Charges') ?></td>
            <td style="text-align:right;">₹<?= number_format($inv['amount'],2) ?></td>
        </tr>
        <tr>
            <td style="text-align:right;font-weight:700;">Total</td>
            <td style="text-align:right;font-weight:700;">₹<?= number_format($inv['amount'],2) ?></td>
        </tr>
    </table>

    <p style="margin-top:20px;color:#6b7280;font-size:13px;">Thank you for your business!</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
