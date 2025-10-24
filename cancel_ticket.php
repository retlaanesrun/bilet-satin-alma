<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_login();
$pdo = db();
$u = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT t.*, trips.departure_ts FROM tickets t JOIN trips ON trips.id=t.trip_id WHERE t.id=? AND t.user_id=?");
$stmt->execute([$id, $u['id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) { die("Bilet bulunamadı."); }

if (time() > (int)$ticket['departure_ts'] - 3600) {
    die("Kalkışa 1 saatten az kaldı, iptal edilemez.");
}

if ($ticket['status'] !== 'active') {
    die("Bilet zaten iptal.");
}

// refund
$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE tickets SET status='cancelled' WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE users SET credit=credit+? WHERE id=?")->execute([(int)$ticket['price_paid'], (int)$u['id']]);
    $pdo->commit();
    header("Location: /my_tickets.php?msg=" . urlencode("Bilet iptal edildi ve ücret iade edildi."));
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Hata: " . h($e->getMessage());
}
