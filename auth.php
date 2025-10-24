<?php
declare(strict_types=1);

// Herhangi bir çıktıdan ÖNCE oturum başlasın
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}

require_once __DIR__ . '/db.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        $next = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: /login.php?next=' . urlencode($next));
        exit;
    }
}

function login(string $email, string $password): bool {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    session_destroy();
}

function register_user(string $name, string $email, string $password): ?string {
    $pdo = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, credit) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, trim($email), $hash, 'user', 200000]);
        return null;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' || stripos($e->getMessage(), 'UNIQUE') !== false) {
            return "Bu e-posta zaten kayıtlı. Lütfen giriş yapın veya farklı bir e-posta deneyin.";
        }
        return "Kayıt başarısız: " . $e->getMessage();
    }
}

function require_role(string $role): void {
    $u = current_user();
    if (!$u || $u['role'] !== $role) {
        http_response_code(403);
        echo "Yetkisiz erişim.";
        exit;
    }
}
