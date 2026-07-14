<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$page_title = 'Technicians';
$active = 'technicians';
$error = '';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role='technician'")->execute([(int)$_GET['delete']]);
    header('Location: technicians.php?msg=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Sab fields bharna zaroori hai.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Ye email pehle se registered hai.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role) VALUES (?,?,?,?,'technician')");
            $stmt->execute([$name, $email, $mobile, $hash]);
            header('Location: technicians.php?msg=saved');
            exit;
        }
    }
}

$technicians = $pdo->query("SELECT * FROM users WHERE role='technician' ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= $_GET['msg']==='deleted' ? 'Technician remove ho gaya.' : 'Technician add ho gaya.' ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>➕ Add Technician</h3></div>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email (login id)</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Mobile</label><input type="text" name="mobile"></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn btn-block" type="submit">Add Technician</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h3>🧑‍🔧 All Technicians (<?= count($technicians) ?>)</h3></div>
        <div class="table-wrap">
        <table>
            <tr><th>Name</th><th>Email</th><th>Mobile</th><th>Actions</th></tr>
            <?php foreach ($technicians as $t): ?>
                <tr>
                    <td><?= e($t['name']) ?></td>
                    <td><?= e($t['email']) ?></td>
                    <td><?= e($t['mobile']) ?></td>
                    <td class="actions">
                        <a href="technicians.php?delete=<?= $t['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
