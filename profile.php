<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$dataFile = 'users.txt';
$username = $_SESSION['username'];
$currentUser = null;

// Felhasználó adatainak lekérése
if (file_exists($dataFile)) {
    $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
        if ($savedUsername === $username) {
            $currentUser = [
                'username' => $savedUsername,
                'password' => $savedPassword,
                'roles' => $savedRoles
            ];
            break;
        }
    }
}

// Ha a felhasználó nem található
if (!$currentUser) {
    echo "Hiba: A felhasználó nem található!";
    exit;
}

// Profilkép elérési útja
$profileImageDir = 'profile_pics/';
$profileImagePath = $profileImageDir . $username . '.jpg';

// Profilkép feltöltése
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($profileImageDir)) {
        mkdir($profileImageDir);
    }

    $imagePath = $profileImageDir . $username . '.jpg';
    move_uploaded_file($_FILES['profile_image']['tmp_name'], $imagePath);
    echo "<p>Profilkép frissítve!</p>";
}

// Adatmódosítás kezelése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);

    // Adatok frissítése a users.txt-ben
    foreach ($users as $index => $user) {
        list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
        if ($savedUsername === $username) {
            $hashedPassword = !empty($newPassword) ? password_hash($newPassword, PASSWORD_DEFAULT) : $savedPassword;
            $users[$index] = $newUsername . '|' . $hashedPassword . '|' . $savedRoles;
            $_SESSION['username'] = $newUsername; // Frissítjük a munkamenetben a felhasználónevet
            break;
        }
    }

    // Frissített users.txt fájl mentése
    file_put_contents($dataFile, implode("\n", $users) . "\n");
    echo "<p>Profil sikeresen frissítve!</p>";
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .profile-container { max-width: 400px; margin: 0 auto; }
        input, button { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .profile-image { text-align: center; margin-bottom: 20px; }
        .profile-image img { max-width: 150px; border-radius: 50%; border: 2px solid #ccc; }
    </style>
</head>
<body>
<div class="profile-container">
    <h2>Profil</h2>

    <div class="profile-image">
        <h3>Profilkép</h3>
        <?php if (file_exists($profileImagePath)): ?>
            <img src="<?php echo $profileImagePath; ?>" alt="Profilkép">
        <?php else: ?>
            <img src="default-profile.jpg" alt="Nincs profilkép">
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="profile_image" accept="image/*" required>
            <button type="submit">Profilkép feltöltése</button>
        </form>
    </div>

    <form method="post">
        <h3>Adatok</h3>
        <label for="username">Felhasználónév:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>

        <label for="password">Új jelszó (opcionális):</label>
        <input type="password" id="password" name="password" placeholder="Új jelszó">

        <h3>Rang:</h3>
        <p><?php echo htmlspecialchars($currentUser['roles']); ?></p>

        <button type="submit" name="update_profile">Profil frissítése</button>
    </form>
</div>
</body>
</html>
