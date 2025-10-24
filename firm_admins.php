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
    $hash = password_hash($_POST['password'] ?? 'firm123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (name,email,password_hash,role,firm_id) VALUES (?,?,?,?,?)")
        ->execute([$_POST['name'], $_POST['email'], $hash, 'firm_admin', (int)$_POST['firm_id']]);
  }
}
$firms = $pdo->query("SELECT * FROM firms")->fetchAll(PDO::FETCH_ASSOC);
$admins = $pdo->query("SELECT u.*, f.name as firm_name FROM users u LEFT JOIN firms f ON f.id=u.firm_id WHERE role='firm_admin'")->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Firma Adminleri</h2>
<form method="post">
  <input name="name" placeholder="Ad Soyad" required>
  <input name="email" type="email" placeholder="E-posta" required>
  <input name="password" type="password" placeholder="Şifre (opsiyonel)">
  <select name="firm_id">
    <?php foreach($firms as $f): ?><option value="<?=$f['id']?>"><?=h($f['name'])?></option><?php endforeach; ?>
  </select>
  <button name="add">Oluştur</button>
</form>
<table>
<tr><th>ID</th><th>Ad</th><th>E-posta</th><th>Firma</th></tr>
<?php foreach($admins as $a): ?>
<tr><td><?=$a['id']?></td><td><?=h($a['name'])?></td><td><?=h($a['email'])?></td><td><?=h($a['firm_name'])?></td></tr>
<?php endforeach; ?>
</table>

</main>
</body>
</html>

