<?php
/**
 * NOXARA - Leaderboard
 */

function get_leaderboard(string $type, int $limit = 10): array {
    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $month_start = date('Y-m-01');
    
    switch ($type) {
        case 'miner_daily':
            $sql = "SELECT u.username, u.vip_level, u.avatar, SUM(ml.profit_amount) as score FROM mining_logs ml JOIN users u ON u.id = ml.user_id WHERE ml.mining_date = ? AND ml.status = 'completed' GROUP BY ml.user_id ORDER BY score DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('si', $today, $limit);
            break;
        case 'miner_weekly':
            $sql = "SELECT u.username, u.vip_level, u.avatar, SUM(ml.profit_amount) as score FROM mining_logs ml JOIN users u ON u.id = ml.user_id WHERE ml.mining_date >= ? AND ml.status = 'completed' GROUP BY ml.user_id ORDER BY score DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('si', $week_start, $limit);
            break;
        case 'miner_monthly':
            $sql = "SELECT u.username, u.vip_level, u.avatar, SUM(ml.profit_amount) as score FROM mining_logs ml JOIN users u ON u.id = ml.user_id WHERE ml.mining_date >= ? AND ml.status = 'completed' GROUP BY ml.user_id ORDER BY score DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('si', $month_start, $limit);
            break;
        case 'referrer_weekly':
            $sql = "SELECT u.username, u.vip_level, u.avatar, SUM(c.commission_amount) as score FROM commissions c JOIN users u ON u.id = c.user_id WHERE c.created_at >= ? GROUP BY c.user_id ORDER BY score DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('si', $week_start, $limit);
            break;
        case 'referrer_monthly':
            $sql = "SELECT u.username, u.vip_level, u.avatar, SUM(c.commission_amount) as score FROM commissions c JOIN users u ON u.id = c.user_id WHERE c.created_at >= ? GROUP BY c.user_id ORDER BY score DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('si', $month_start, $limit);
            break;
        default: // earner_alltime
            $sql = "SELECT u.username, u.vip_level, u.avatar, uw.total_earned as score FROM user_wallets uw JOIN users u ON u.id = uw.user_id ORDER BY uw.total_earned DESC LIMIT ?";
            $stmt = db()->prepare($sql);
            $stmt->bind_param('i', $limit);
            break;
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}
