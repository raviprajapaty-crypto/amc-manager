<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$page_title = 'Customers';
$active = 'customers';

// Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    header('Location: customers.php?msg=deleted');
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE name LIKE ? OR company_name LIKE ? OR mobile LIKE ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
}
$customers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?= $_GET['msg'] === 'deleted' ? 'Customer delete ho gaya.' : 'Customer save ho gaya.' ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>👥 All Customers (<?= count($customers) ?>)</h3>
        <a href="customer_form.php" class="btn">+ Add Customer</a>
    </div>

    <form method="GET" style="margin-bottom:16px;">
        <input type="text" name="search" placeholder="Naam, company ya mobile se search karein..." value="<?= e($search) ?>"
               style="padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;width:300px;max-width:100%;">
        <button class="btn btn-sm" type="submit">Search</button>
    </form>

    <div class="table-wrap">
    <table>
        <tr>
            <th>Name</th><th>Company</th><th>Mobile</th><th>Email</th><th>Cameras</th><th>Address</th><th>Actions</th>
        </tr>
        <?php if (!$customers): ?>
            <tr><td colspan="7" style="text-align:center;color:#6b7280;">Koi customer nahi mila</td></tr>
        <?php endif; ?>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td><?= e($c['company_name']) ?></td>
                <td><a href="https://wa.me/91<?= e($c['mobile']) ?>" class="whatsapp-link" target="_blank">📞 <?= e($c['mobile']) ?></a></td>
                <td><?= e($c['email']) ?></td>
                <td><?= e($c['total_cameras']) ?></td>
                <td><?= e(mb_strimwidth($c['address'] ?? '', 0, 30, '...')) ?></td>
                <td class="actions">
                    <a href="customer_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    <a href="customers.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
