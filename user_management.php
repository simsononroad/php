<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$usersFile = 'users.txt';
$deletedUsersFile = 'deleted_users.txt';

// Felhasználók betöltése
$users = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$deletedUsers = file($deletedUsersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Ha törlésre kerültek felhasználók
if (isset($_POST['delete_users'])) {
    $selectedUsers = $_POST['selected_users'] ?? [];

    foreach ($selectedUsers as $username) {
        foreach ($users as $index => $user) {
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            if ($savedUsername === $username) {
                // Felhasználó eltávolítása a users.txt-ből
                unset($users[$index]);

                // Törölt felhasználó hozzáadása a deleted_users.txt-hez
                file_put_contents($deletedUsersFile, "$user\n", FILE_APPEND);
            }
        }
    }

    // Frissített users.txt fájl mentése
    file_put_contents($usersFile, implode("\n", $users) . "\n");
}

// Ha visszaállítjuk a felhasználót a törölt listából
if (isset($_POST['restore_user'])) {
    $usernameToRestore = $_POST['restore_user'];

    foreach ($deletedUsers as $index => $deletedUser) {
        list($deletedUsername, $deletedPassword, $deletedRoles) = explode('|', $deletedUser);
        if ($deletedUsername === $usernameToRestore) {
            // Felhasználó visszaállítása a users.txt-be
            file_put_contents($usersFile, "$deletedUser\n", FILE_APPEND);

            // Törölt felhasználó eltávolítása a deleted_users.txt-ből
            unset($deletedUsers[$index]);
        }
    }

    // Frissített deleted_users.txt fájl mentése
    file_put_contents($deletedUsersFile, implode("\n", $deletedUsers) . "\n");
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználók kezelése</title>
</head>
<body>

<h2>Felhasználók kezelése</h2>

<!-- Felhasználók listázása -->
<form method="post">
    <h3>Aktív Felhasználók:</h3>
    <ul>
        <?php foreach ($users as $user): ?>
            <?php
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            ?>
            <li>
                <input type="checkbox" name="selected_users[]" value="<?php echo $savedUsername; ?>"> 
                <?php echo $savedUsername . " - " . implode(', ', explode(',', $savedRoles)); ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <button type="submit" name="delete_users">Törlés</button>
</form>

<h3>Törölt Felhasználók:</h3>
<ul>
    <?php foreach ($deletedUsers as $deletedUser): ?>
        <?php
        list($deletedUsername, $deletedPassword, $deletedRoles) = explode('|', $deletedUser);
        ?>
        <li>
            <?php echo $deletedUsername . " - " . implode(', ', explode(',', $deletedRoles)); ?>
            <form method="post" style="display:inline;">
                <button type="submit" name="restore_user" value="<?php echo $deletedUsername; ?>">Visszaállítás</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>
