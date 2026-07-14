<?php
require_once __DIR__ . '/../includes/auth.php';
require_technician();

$tech_id = $_SESSION['user_id'];
$amc_id = (int) ($_GET['amc_id'] ?? $_POST['amc_id'] ?? 0);

// Make sure this AMC is actually assigned to this technician
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS customer_name, c.company_name, c.mobile, c.address
    FROM amc_contracts a JOIN customers c ON a.customer_id = c.id
    WHERE a.id = ? AND a.technician_id = ?
");
$stmt->execute([$amc_id, $tech_id]);
$amc = $stmt->fetch();

if (!$amc) {
    die('Ye site aapko assign nahi hai ya nahi milti.');
}

$page_title = 'Visit Report';
$active = 'sites';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_visit'])) {
    $visit_date = $_POST['visit_date'];
    $report_text = trim($_POST['report_text']);
    $issue_resolved = isset($_POST['issue_resolved']) ? 1 : 0;

    if (!$visit_date || $report_text === '') {
        $error = 'Visit date aur report likhna zaroori hai.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO visits (amc_id, technician_id, visit_date, report_text, issue_resolved) VALUES (?,?,?,?,?)");
            $stmt->execute([$amc_id, $tech_id, $visit_date, $report_text, $issue_resolved]);
            $visit_id = $pdo->lastInsertId();

            // Handle multiple photo uploads
            if (!empty($_FILES['photos']['name'][0])) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                $allowed = ['jpg','jpeg','png','webp'];
                foreach ($_FILES['photos']['tmp_name'] as $i => $tmpName) {
                    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed)) continue;
                    $filename = 'visit_' . $visit_id . '_' . time() . '_' . $i . '.' . $ext;
                    $dest = UPLOAD_DIR . $filename;
                    if (move_uploaded_file($tmpName, $dest)) {
                        $pdo->prepare("INSERT INTO visit_photos (visit_id, photo_path) VALUES (?,?)")
                            ->execute([$visit_id, $filename]);
                    }
                }
            }

            // Increment used_visits (capped at total_visits)
            $pdo->prepare("UPDATE amc_contracts SET used_visits = LEAST(used_visits + 1, total_visits) WHERE id = ?")
                ->execute([$amc_id]);

            $pdo->commit();
            header('Location: assigned_sites.php?msg=visit_added');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Kuch galat ho gaya, dobara try karein.';
        }
    }
}

// Past visits for this AMC
$past = $pdo->prepare("
    SELECT v.*, GROUP_CONCAT(p.photo_path) AS photos
    FROM visits v LEFT JOIN visit_photos p ON p.visit_id = v.id
    WHERE v.amc_id = ?
    GROUP BY v.id
    ORDER BY v.visit_date DESC
");
$past->execute([$amc_id]);
$past = $past->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:700px;">
    <div class="card-header"><h3>📍 <?= e($amc['customer_name']) ?> <?= $amc['company_name'] ? '- '.e($amc['company_name']) : '' ?></h3></div>
    <p style="color:#6b7280;font-size:13px;margin-bottom:10px;">
        <?= e($amc['address']) ?> | 📞 <a href="https://wa.me/91<?= e($amc['mobile']) ?>" class="whatsapp-link" target="_blank"><?= e($amc['mobile']) ?></a><br>
        Visits Remaining: <strong><?= max(0, $amc['total_visits'] - $amc['used_visits']) ?></strong> of <?= e($amc['total_visits']) ?>
    </p>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="amc_id" value="<?= $amc_id ?>">
        <div class="form-group"><label>Visit Date *</label><input type="date" name="visit_date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="form-group"><label>Visit Report *</label><textarea name="report_text" rows="4" placeholder="Kya kaam hua, kya issue mila..." required></textarea></div>
        <div class="form-group"><label>Upload Photos (multiple)</label><input type="file" name="photos[]" accept="image/*" multiple></div>
        <div class="form-group">
            <label><input type="checkbox" name="issue_resolved" value="1" style="width:auto;"> Issue Resolved / Fully OK</label>
        </div>
        <button type="submit" name="submit_visit" class="btn">Submit Visit Report</button>
        <a href="assigned_sites.php" class="btn btn-outline">Back</a>
    </form>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-header"><h3>📋 Past Visits</h3></div>
    <?php if (!$past): ?>
        <p style="color:#6b7280;">Koi past visit nahi hai.</p>
    <?php endif; ?>
    <?php foreach ($past as $p): ?>
        <div style="border-bottom:1px solid #e5e7eb;padding:12px 0;">
            <strong><?= e($p['visit_date']) ?></strong>
            <?= $p['issue_resolved'] ? '<span class="badge badge-active">Resolved</span>' : '<span class="badge badge-expiring">Pending</span>' ?>
            <p style="margin:6px 0;font-size:13.5px;"><?= nl2br(e($p['report_text'])) ?></p>
            <?php if ($p['photos']): ?>
                <?php foreach (explode(',', $p['photos']) as $photo): ?>
                    <img src="<?= UPLOAD_URL . e($photo) ?>" class="photo-thumb" alt="visit photo">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
