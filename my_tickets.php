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
require_login();
$pdo = db();
$u = current_user();
$stmt = $pdo->prepare("SELECT t.*, trips.origin, trips.destination, trips.departure_ts FROM tickets t JOIN trips ON trips.id=t.trip_id WHERE t.user_id=? ORDER BY t.purchased_at DESC");
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Biletlerim</h2>
<?php if(isset($_GET['msg'])): ?><p><strong><?=h($_GET['msg'])?></strong></p><?php endif; ?>
<table>
<tr><th>Sefer</th><th>Koltuk</th><th>Durum</th><th>Ödenen</th><th>Kalkış</th><th>Aksiyon</th></tr>
<?php foreach($rows as $r): $depart=(int)$r['departure_ts']; $canCancel = (time() <= $depart - 3600) && $r['status']==='active'; ?>
<tr>
  <td><?=h($r['origin'])?> → <?=h($r['destination'])?></td>
  <td>#<?= (int)$r['seat_no'] ?></td>
  <td><?=h($r['status'])?></td>
  <td><?=money_fmt((int)$r['price_paid'])?></td>
  <td><?=h(ts_to_local($depart))?></td>
  <td>
    <a href="/ticket_pdf.php?id=<?= (int)$r['id'] ?>">PDF</a>
    <?php if($canCancel): ?> | <a href="/cancel_ticket.php?id=<?= (int)$r['id'] ?>">İptal</a><?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</table>

</main>
</body>
</html>

