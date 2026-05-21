<?php
/**
 * NOXARA Cron - Generate fake activity feed entries
 * Schedule: 0 * * * * (setiap jam)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/activity_feed.php';

$start = microtime(true);

$names = ['A***N','B***I','C***A','D***O','E***A','F***I','G***N','H***A','I***S','J***Y','K***A','L***I','M***N','N***A','O***R','P***A','R***Y','S***N','T***A','U***I','V***A','W***N','X***A','Y***I','Z***A'];
$types = [
    ['type' => 'mining', 'templates' => ['baru klaim Rp %s', 'profit mining Rp %s cair']],
    ['type' => 'topup', 'templates' => ['baru topup Rp %s', 'isi saldo Rp %s']],
    ['type' => 'vip', 'templates' => ['naik VIP %d', 'upgrade ke VIP %d']],
    ['type' => 'purchase', 'templates' => ['beli paket %s', 'aktivasi paket %s']],
    ['type' => 'withdraw', 'templates' => ['withdraw Rp %s berhasil']],
];

$products = ['STONE I','STONE II','STONE III','IRON I','IRON II'];
$amounts_small = [50000, 100000, 150000, 250000, 500000, 850000];
$amounts_big = [500000, 1000000, 2000000, 5000000];

$count = random_int(5, 10);
for ($i = 0; $i < $count; $i++) {
    $name = $names[array_rand($names)];
    $type_data = $types[array_rand($types)];
    $type = $type_data['type'];
    $template = $type_data['templates'][array_rand($type_data['templates'])];
    
    switch ($type) {
        case 'mining':
        case 'withdraw':
            $amt = $amounts_small[array_rand($amounts_small)];
            $msg = $name . ' ' . sprintf($template, format_number($amt));
            break;
        case 'topup':
            $amt = $amounts_big[array_rand($amounts_big)];
            $msg = $name . ' ' . sprintf($template, format_number($amt));
            break;
        case 'vip':
            $vip = random_int(1, 3);
            $msg = $name . ' ' . sprintf($template, $vip);
            break;
        case 'purchase':
            $prod = $products[array_rand($products)];
            $msg = $name . ' ' . sprintf($template, $prod);
            break;
    }
    
    add_activity(null, $msg, $type, true);
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('activity_feed_seed', 'success', ?, ?, ?)");
$msg_log = "Generated {$count} fake entries";
$stmt->bind_param('sid', $msg_log, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] activity_feed_seed: {$count} entries ({$elapsed}s)\n";
