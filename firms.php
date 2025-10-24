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
  if(isset($_POST['add'])){ $pdo->prepare("INSERT INTO firms (name) VALUES (?)")->execute([$_POST['name']]); }
  if(isset($_POST['del'])){ $pdo->prepare("DELETE FROM firms WHERE id=?")->execute([(int)$_POST['id']]); }
}
$rows = $pdo->query("SELECT * FROM firms ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Firmalar</h2>
<form method="post">
  <input name="name" placeholder="Firma adı" required>
  <button name="add">Ekle</button>
</form>
<table>
<tr><th>ID</th><th>Ad</th><th>Aksiyon</th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?=$r['id']?></td><td><?=h($r['name'])?></td>
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

