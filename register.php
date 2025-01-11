<?php
session_start();

// Regisztrációs űrlap kezelés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ellenőrizni, hogy a felhasználónév már létezik-e
    $users = file('users.txt', FILE_IGNORE_NEW_LINES);
    foreach ($users as $user) {
        list($storedUsername, $storedPassword, $storedRole) = explode('|', $user);
        if ($storedUsername === $username) {
            $errorMessage = "A felhasználónév már létezik!";
            break;
        }
    }

    // Ha nem létezik a felhasználónév, regisztráljuk
    if (!isset($errorMessage)) {
        // Automatikusan a "user" rangot adjuk a felhasználónak
        $role = 'felhasználó';

        // A jelszót hasheljük
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


        // Új felhasználó hozzáadása a users.txt fájlhoz
        $newUser = $username . '|' . $hashedPassword . '|' . $role . PHP_EOL;
        file_put_contents('users.txt', $newUser, FILE_APPEND);

        $successMessage = "Sikeresen regisztráltál! Most bejelentkezhetsz.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .register-form { width: 300px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .error { color: red; font-size: 14px; text-align: center; }
        .success { color: green; font-size: 14px; text-align: center; }
        .login-link { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="register-form">
        <h2>Regisztráció</h2>

        <!-- Ha van hibaüzenet, megjelenítjük -->
        <?php if (isset($errorMessage)): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <!-- Ha sikeres volt a regisztráció, megjelenítjük -->
        <?php if (isset($successMessage)): ?>
            <p class="success"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="username" placeholder="Felhasználónév" required><br>
            <input type="password" name="password" placeholder="Jelszó" required><br>
            <button type="submit">Regisztrálok</button>
        </form>

        <!-- Bejelentkezéshez link -->
        <div class="login-link">
            <a href="login.php">Már van fiókom</a>
        </div>
    </div>
</body>
</html>
