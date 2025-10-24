<?php
// ÇOK ÖNEMLİ: İlk karakter bu satırdaki '<' olmalı. Öncesinde boşluk/BOM yok!
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';

// (İsteğe bağlı) Güvenli olmak için çıktı tamponu aç
if (ob_get_level() === 0) { ob_start(); }

$login_error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        $next = $_GET['next'] ?? '/';
        header('Location: ' . $next);
        exit; // Yönlendirmeden sonra çık
    } else {
        $login_error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bilet Platformu - Giriş</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
<header>
  <h1><a href="/">🚌 Bilet Satın Alma</a></h1>
  <nav>
    <a href="/login.php">Giriş</a> | <a href="/register.php">Kayıt</a>
  </nav><hr>
</header>
<main>

<h2>Giriş</h2>
<?php if ($login_error): ?>
  <p>Giriş başarısız.</p>
<?php endif; ?>
<form method="post">
  <label>E-posta</label><input name="email" type="email" required>
  <label>Şifre</label><input name="password" type="password" required>
  <button type="submit">Giriş Yap</button>
</form>

</main>
</body>
</html>
