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
$msg = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $err = register_user($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
  if (!$err) { echo "<p>Kayıt başarılı. <a href='/login.php'>Giriş yap</a></p>"; }
  else { echo "<p>".h($err)."</p>"; }
}
?>
<h2>Kayıt</h2>
<form method="post">
  <label>Ad Soyad</label><input name="name" required>
  <label>E-posta</label><input name="email" type="email" required>
  <label>Şifre</label><input name="password" type="password" required>
  <button type="submit">Kayıt Ol</button>
</form>

</main>
</body>
</html>

