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

<?php require_login(); require_role('admin'); $pdo = db();
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['add'])){
    $expires = strtotime($_POST['expires']);
    $pdo->prepare("INSERT INTO coupons (code,percent,usage_limit,expires_at) VALUES (?,?,?,?)")
        ->execute([strtoupper(trim($_POST['code'])), (int)$_POST['percent'], (int)$_POST['usage_limit'], $expires]);
  }
  if(isset($_POST['del'])){
    $pdo->prepare("DELETE FROM coupons WHERE code=?")->execute([$_POST['code']]);
  }
}
$rows = $pdo->query("SELECT * FROM coupons ORDER BY expires_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Kuponlar</h2>
<form method="post">
  <input name="code" placeholder="KOD" required>
  <input name="percent" type="number" min="1" max="100" placeholder="%">
  <input name="usage_limit" type="number" min="1" placeholder="Limit">
  <input name="expires" type="date" required>
  <button name="add">Ekle</button>
</form>
<table>
<tr><th>Kod</th><th>%</th><th>Kullanım</th><th>Son Kullanma</th><th></th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?=h($r['code'])?></td>
  <td><?=$r['percent']?></td>
  <td><?=$r['used_count']?> / <?=$r['usage_limit']?></td>
  <td><?=date('Y-m-d', (int)$r['expires_at'])?></td>
  <td>
    <form method="post" style="display:inline">
      <input type="hidden" name="code" value="<?=$r['code']?>">
      <button name="del">Sil</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>

</main>
</body>
</html>

