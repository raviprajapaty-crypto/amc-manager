<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$customer = ['name'=>'','company_name'=>'','mobile'=>'','email'=>'','address'=>'','total_cameras'=>0];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $customer = $found;
}

$page_title = $id ? 'Edit Customer' : 'Add Customer';
$active = 'customers';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['company_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $total_cameras = (int) $_POST['total_cameras'];

    if ($name === '' || $mobile === '') {
        $error = 'Naam aur Mobile number required hai.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE customers SET name=?, company_name=?, mobile=?, email=?, address=?, total_cameras=? WHERE id=?");
            $stmt->execute([$name, $company_name, $mobile, $email, $address, $total_cameras, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (name, company_name, mobile, email, address, total_cameras) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $company_name, $mobile, $email, $address, $total_cameras]);
        }
        header('Location: customers.php?msg=saved');
        exit;
    }
    $customer = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <div class="card-header"><h3><?= $id ? '✏️ Edit Customer' : '➕ Add New Customer' ?></h3></div>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="name" value="<?= e($customer['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= e($customer['company_name']) ?>">
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Mobile Number *</label>
                <input type="text" name="mobile" value="<?= e($customer['mobile']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($customer['email']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3"><?= e($customer['address']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Total Installed Cameras</label>
            <input type="number" name="total_cameras" min="0" value="<?= e($customer['total_cameras']) ?>">
        </div>

        <button type="submit" class="btn">Save Customer</button>
        <a href="customers.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
