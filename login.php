<?php
session_start();
$logFile = 'login_log.txt';

// Ha a felhasználó már be van jelentkezve, irány a megfelelő oldalra
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } elseif ($_SESSION['role'] === 'premium') {
        header('Location: premium.php');
    } else {
        header('Location: user.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $dataFile = 'users.txt';

    if (empty($username) || empty($password)) {
        echo "A felhasználónév és a jelszó megadása kötelező!";
        exit;
    }

    // Logolás
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "$time - IP: $ip - Próba: $username\n", FILE_APPEND);

    // Felhasználó ellenőrzése
    if (file_exists($dataFile)) {
        $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($users as $user) {
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            if ($savedUsername === $username && password_verify($password, $savedPassword)) {
                $_SESSION['username'] = $username;
                $roles = explode(',', $savedRoles); // Több rang kezelése
                
                // Rangok prioritása
                $priority = ['admin', 'premium', 'felhasználó']; // Hierarchia
                $highestRole = null;

                // A legmagasabb rang kiválasztása
                foreach ($priority as $role) {
                    if (in_array($role, $roles)) {
                        $highestRole = $role;
                        break; // Kilépünk, ha megtaláltuk a legmagasabbat
                    }
                }

                $_SESSION['role'] = $highestRole;

                // Átirányítás rang alapján
                if ($highestRole === 'admin') {
                    header('Location: admin.php');
                } elseif ($highestRole === 'premium') {
                    header('Location: premium.php');
                } else {
                    header('Location: user.php');
                }
                exit;
            }
        }
    }

    echo "Hibás felhasználónév vagy jelszó!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .login-form { width: 300px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .error { color: red; font-size: 14px; text-align: center; }
        .register-link { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="login-form">
        <h2>Bejelentkezés</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Felhasználónév" required><br>
            <input type="password" name="password" placeholder="Jelszó" required><br>
            <button type="submit">Bejelentkezés</button>
        </form>

        <div class="register-link">
            <a href="register.php">Regisztrálok</a>
        </div>
    </div>

</body>
</html>
