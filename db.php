<?php
declare(strict_types=1);

const DB_PATH = __DIR__ . '/../data/app.db';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $isNew = !file_exists(DB_PATH);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    if ($isNew) {
        $schema = file_get_contents(__DIR__ . '/../schema.sql');
        // Replace placeholder hashes then execute
        $schema = str_replace('{ADMIN_HASH}', password_hash('admin123', PASSWORD_DEFAULT), $schema);
        $schema = str_replace('{FIRM_HASH}', password_hash('firm123', PASSWORD_DEFAULT), $schema);
        $schema = str_replace('{USER_HASH}', password_hash('user123', PASSWORD_DEFAULT), $schema);
        $pdo->exec($schema);
        // Seed some trips in near future
        $now = time();
        $stmt = $pdo->prepare("INSERT INTO trips (firm_id, origin, destination, departure_ts, price, seat_count) VALUES (?,?,?,?,?,?)");
        for ($i=1;$i<=5;$i++) {
            $stmt->execute([1, 'İstanbul', 'Ankara', $now + 86400*$i + 3600, 300000, 40]); // 3000.00 TL
            $stmt->execute([2, 'Ankara', 'İzmir', $now + 86400*$i + 7200, 280000, 40]);
        }
        // Seed one coupon
        $stmt = $pdo->prepare("INSERT INTO coupons (code, percent, usage_limit, expires_at) VALUES (?,?,?,?)");
        $stmt->execute(['INDIRIM10', 10, 100, $now + 86400*30]);
    }
    return $pdo;
}
