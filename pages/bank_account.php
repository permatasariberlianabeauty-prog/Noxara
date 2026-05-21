<?php
/**
 * NOXARA - Bank Accounts
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Rekening Bank';
$show_back = true;

// Handle add bank account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_bank') {
    verify_csrf();
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_holder = trim($_POST['account_holder'] ?? '');

    if ($bank_name && $account_number && $account_holder) {
        $stmt = db()->prepare("INSERT INTO bank_accounts (user_id, bank_name, account_number, account_holder) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $user['id'], $bank_name, $account_number, $account_holder);
        $stmt->execute();
        $stmt->close();
        redirect(BASE_URL . '/pages/bank_account.php?success=1');
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_bank') {
    verify_csrf();
    $bank_id = (int)($_POST['bank_id'] ?? 0);
    $stmt = db()->prepare("DELETE FROM bank_accounts WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $bank_id, $user['id']);
    $stmt->execute();
    $stmt->close();
    redirect(BASE_URL . '/pages/bank_account.php?deleted=1');
}

// Get bank accounts
$stmt = db()->prepare("SELECT * FROM bank_accounts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$banks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Rekening Bank</h2>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Rekening berhasil ditambahkan.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Rekening berhasil dihapus.</div>
    <?php endif; ?>


    <!-- Bank List -->
    <?php if (!empty($banks)): ?>
    <div class="bank-list">
        <?php foreach ($banks as $bank): ?>
        <div class="bank-item glassmorphism">
            <div class="bank-info">
                <span class="bank-name"><?= sanitize($bank['bank_name']) ?></span>
                <span class="bank-number"><?= sanitize($bank['account_number']) ?></span>
                <span class="bank-holder"><?= sanitize($bank['account_holder']) ?></span>
            </div>
            <form method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_bank">
                <input type="hidden" name="bank_id" value="<?= $bank['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state"><p>Belum ada rekening bank.</p></div>
    <?php endif; ?>

    <!-- Add Form -->
    <div class="glassmorphism form-card">
        <h4>Tambah Rekening Baru</h4>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_bank">
            <div class="form-group">
                <label class="form-label">Nama Bank</label>
                <select name="bank_name" class="form-input" required>
                    <option value="">Pilih Bank</option>
                    <option value="BCA">BCA</option>
                    <option value="BNI">BNI</option>
                    <option value="BRI">BRI</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="CIMB">CIMB Niaga</option>
                    <option value="Permata">Permata</option>
                    <option value="DANA">DANA</option>
                    <option value="OVO">OVO</option>
                    <option value="GoPay">GoPay</option>
                    <option value="ShopeePay">ShopeePay</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" name="account_number" class="form-input" placeholder="Nomor rekening" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Pemilik</label>
                <input type="text" name="account_holder" class="form-input" placeholder="Nama sesuai rekening" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Rekening</button>
        </form>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>
