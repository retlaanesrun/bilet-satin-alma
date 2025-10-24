<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_login();

$pdo = db();

$trip_id = (int)($_POST['id'] ?? 0);
$seat_no = (int)($_POST['seat_no'] ?? 0);
$coupon  = trim($_POST['coupon'] ?? '');

$pdo->beginTransaction();
try {
    // 1) Sefer kontrolü
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id=?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$trip) {
        throw new Exception("Sefer bulunamadı.");
    }
    if ($seat_no < 1 || $seat_no > (int)$trip['seat_count']) {
        throw new Exception("Geçersiz koltuk.");
    }

    // 2) Koltuk dolu mu?
    $stmt = $pdo->prepare("SELECT 1 FROM tickets WHERE trip_id=? AND seat_no=? AND status='active'");
    $stmt->execute([$trip_id, $seat_no]);
    if ($stmt->fetch()) {
        throw new Exception("Koltuk dolu.");
    }

    // 3) Fiyat + kupon
    $price = (int)$trip['price'];
    if ($coupon !== '') {
        $c = $pdo->prepare("SELECT * FROM coupons WHERE code=?");
        $c->execute([$coupon]);
        $cp = $c->fetch(PDO::FETCH_ASSOC);
        if ($cp && (int)$cp['expires_at'] > time() && (int)$cp['used_count'] < (int)$cp['usage_limit']) {
            $price = (int)round($price * (100 - (int)$cp['percent']) / 100);
            $pdo->prepare("UPDATE coupons SET used_count=used_count+1 WHERE code=?")->execute([$coupon]);
        }
    }

    // 4) Kullanıcı bakiyesi
    $u = current_user();
    $stmt = $pdo->prepare("SELECT credit FROM users WHERE id=?");
    $stmt->execute([$u['id']]);
    $credit = (int)$stmt->fetchColumn();
    if ($credit < $price) {
        throw new Exception("Yetersiz bakiye.");
    }

    // 5) Tahsilat + bilet oluşturma
    $pdo->prepare("UPDATE users SET credit=credit-? WHERE id=?")->execute([$price, $u['id']]);
    $pdo->prepare("INSERT INTO tickets (user_id, trip_id, seat_no, price_paid) VALUES (?,?,?,?)")
        ->execute([$u['id'], $trip_id, $seat_no, $price]);

    $pdo->commit();
    header("Location: /my_tickets.php?msg=" . urlencode("Satın alındı."));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo "Hata: " . h($e->getMessage()) . " — <a href=\"/trip_details.php?id={$trip_id}\">Geri dön</a>";
}
