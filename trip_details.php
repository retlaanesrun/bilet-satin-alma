<?php require_once __DIR__ . '/../src/auth.php'; require_once __DIR__ . '/../src/helpers.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bilet Platformu</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
<header>
  <h1><a href="/">🚌 Bilet Satın Alma</a></h1>
  <nav>
    <?php if ($u = current_user()): ?>
      Merhaba, <?=h($u['name'])?> (<?=h($u['role'])?>) — Kredi: <strong><?=money_fmt((int)$u['credit'])?></strong>
      | <a href="/my_tickets.php">Biletlerim</a>
      <?php if($u['role']==='firm_admin'): ?> | <a href="/company/index.php">Firma Paneli</a><?php endif; ?>
      <?php if($u['role']==='admin'): ?> | <a href="/admin/index.php">Admin Paneli</a><?php endif; ?>
      | <a href="/logout.php">Çıkış</a>
    <?php else: ?>
      <a href="/login.php">Giriş</a> | <a href="/register.php">Kayıt</a>
    <?php endif; ?>
  </nav><hr>
</header>
<main>

<?php
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT trips.*, firms.name AS firm_name FROM trips JOIN firms ON firms.id=trips.firm_id WHERE trips.id=?");
$stmt->execute([$id]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$trip) { echo "<p>Sefer bulunamadı.</p>"; require __DIR__ . '/_end.php'; exit; }

$seatStmt = $pdo->prepare("SELECT seat_no FROM tickets WHERE trip_id=? AND status='active'");
$seatStmt->execute([$id]);
$taken = array_map('intval', array_column($seatStmt->fetchAll(PDO::FETCH_ASSOC), 'seat_no'));

?>
<h2><?=h($trip['firm_name'])?> — <?=h($trip['origin'])?> → <?=h($trip['destination'])?></h2>
<p>Kalkış: <strong><?=h(ts_to_local((int)$trip['departure_ts']))?></strong> | Fiyat: <strong><?=money_fmt((int)$trip['price'])?></strong> | Koltuk: <?= (int)$trip['seat_count'] ?></p>

<?php if (!current_user()): ?>
  <p><a href="/login.php?next=<?=urlencode('/buy_ticket.php?id='.(int)$trip['id'])?>">Satın almak için giriş yapın</a></p>
<?php else: ?>
  <form method="post" action="/buy_ticket.php">
    <input type="hidden" name="id" value="<?= (int)$trip['id'] ?>">
    <label>Koltuk Seçin</label><br>
    <?php for($i=1;$i<=(int)$trip['seat_count'];$i++): $disabled=in_array($i,$taken); ?>
      <label style="display:inline-block; width:50px; margin:4px;">
        <input type="radio" name="seat_no" value="<?=$i?>" <?= $disabled? 'disabled':'' ?> required>
        <?=$i?>
      </label>
    <?php endfor; ?>
    <div style="margin-top:10px;">
      <label>Kupon Kodu (opsiyonel)</label>
      <input name="coupon" placeholder="INDIRIM10">
    </div>
    <button type="submit">Bileti Satın Al</button>
  </form>
<?php endif; ?>


</main>
</body>
</html>

