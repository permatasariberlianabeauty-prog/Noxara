<?php
/**
 * NOXARA - Database Backup
 */

function create_backup(): array {
    $filename = 'noxara_' . date('Y-m-d_His') . '.sql';
    $filepath = BACKUPS_PATH . '/' . $filename;
    
    $cmd = sprintf(
        'mysqldump -h %s -P %d -u %s -p%s %s > %s 2>&1',
        escapeshellarg(DB_HOST),
        DB_PORT,
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_NAME),
        escapeshellarg($filepath)
    );
    
    exec($cmd, $output, $code);
    
    if ($code !== 0 || !file_exists($filepath)) {
        return ['success' => false, 'message' => 'Backup gagal: ' . implode("\n", $output)];
    }
    
    $size = filesize($filepath);
    $stmt = db()->prepare("INSERT INTO backup_logs (filename, filesize) VALUES (?, ?)");
    $stmt->bind_param('si', $filename, $size);
    $stmt->execute();
    $stmt->close();
    
    return ['success' => true, 'filename' => $filename, 'size' => $size];
}
