<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Quest Settings';
$current_admin_page = 'quest_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 1; $i <= 5; $i++) {
        $title = trim($_POST["quest_{$i}_title"] ?? '');
        $desc = trim($_POST["quest_{$i}_desc"] ?? '');
        $reward = (float)($_POST["quest_{$i}_reward"] ?? 0);
        $target = (int)($_POST["quest_{$i}_target"] ?? 1);
        $type = trim($_POST["quest_{$i}_type"] ?? '');

        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $data = json_encode(['title'=>$title,'description'=>$desc,'reward'=>$reward,'target'=>$target,'type'=>$type]);
        $key = "quest_$i";
        $stmt->bind_param('sss', $key, $data, $data);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Quests saved.';
}

include __DIR__ . '/_admin_layout.php';

$quests = [];
for ($i = 1; $i <= 5; $i++) {
    $r = db()->query("SELECT value FROM settings WHERE setting_key='quest_$i'")->fetch_assoc();
    $quests[$i] = $r ? json_decode($r['value'], true) : ['title'=>'','description'=>'','reward'=>0,'target'=>1,'type'=>''];
}
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Quest Definitions (5 Quests)</h3>
    <form method="post">
        <?php for ($i = 1; $i <= 5; $i++): $q = $quests[$i]; ?>
        <div style="border:1px solid #1a2340;padding:12px;border-radius:8px;margin-bottom:12px;">
            <strong style="color:#00D4FF;">Quest <?= $i ?></strong>
            <div class="form-group"><label>Title</label><input type="text" name="quest_<?= $i ?>_title" value="<?= htmlspecialchars($q['title']) ?>"></div>
            <div class="form-group"><label>Description</label><input type="text" name="quest_<?= $i ?>_desc" value="<?= htmlspecialchars($q['description']) ?>"></div>
            <div class="form-group"><label>Type (e.g. mining, topup, referral)</label><input type="text" name="quest_<?= $i ?>_type" value="<?= htmlspecialchars($q['type']) ?>"></div>
            <div class="form-group"><label>Target Count</label><input type="number" name="quest_<?= $i ?>_target" value="<?= $q['target'] ?>"></div>
            <div class="form-group"><label>Reward (Rp)</label><input type="number" name="quest_<?= $i ?>_reward" value="<?= $q['reward'] ?>"></div>
        </div>
        <?php endfor; ?>
        <button type="submit" class="btn-admin primary">Save Quests</button>
    </form>
</div>

    </div>
</div>
</body>
</html>
