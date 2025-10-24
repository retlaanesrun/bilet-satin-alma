<?php require_once __DIR__ . '/../../src/auth.php'; require_once __DIR__ . '/../../src/helpers.php'; ?>
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

<?php require_login(); $u=current_user(); if($u['role']!=='firm_admin'){ http_response_code(403); echo "Yetkisiz."; exit; } $pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ 
  if(isset($_POST['add'])){ 
    $pdo->prepare("INSERT INTO trips (firm_id, origin, destination, departure_ts, price, seat_count) VALUES (?,?,?,?,?,?)")
        ->execute([$u['firm_id'], $_POST['origin'], $_POST['destination'], strtotime($_POST['departure']), (int)($_POST['price']*100), (int)$_POST['seat_count']]);
  }
  if(isset($_POST['del'])){ $pdo->prepare("DELETE FROM trips WHERE id=? AND firm_id=?")->execute([(int)$_POST['id'], $u['firm_id']]); }
}
$stmt = $pdo->prepare("SELECT * FROM trips WHERE firm_id=? ORDER BY departure_ts DESC");
$stmt->execute([$u['firm_id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Sefer Yönetimi</h2>
<form method="post">
  <input name="origin" placeholder="Kalkış" required>
  <input name="destination" placeholder="Varış" required>
  <input name="departure" type="datetime-local" required>
  <input name="price" type="number" step="0.01" placeholder="Fiyat (TL)" required>
  <input name="seat_count" type="number" value="40" min="1" max="60" required>
  <button name="add">Ekle</button>
</form>
<table>
<tr><th>ID</th><th>Rota</th><th>Kalkış</th><th>Fiyat</th><th>Koltuk</th><th></th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?=$r['id']?></td>
  <td><?=h($r['origin'])?> → <?=h($r['destination'])?></td>
  <td><?=h(ts_to_local((int)$r['departure_ts']))?></td>
  <td><?=money_fmt((int)$r['price'])?></td>
  <td><?=$r['seat_count']?></td>
  <td>
    <form method="post" style="display:inline">
      <input type="hidden" name="id" value="<?=$r['id']?>">
      <button name="del">Sil</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>

</main>
</body>
</html>

