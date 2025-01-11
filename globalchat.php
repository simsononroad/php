<?php
session_start();

// Ellenőrizzük, hogy be vagyunk-e jelentkezve
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: login.php'); // Ha nem vagy bejelentkezve, irányítson a login oldalra
    exit();
}

// A bejelentkezett felhasználó adatai
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Az üzenetküldés kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = strip_tags(trim($_POST['message'])); // Az üzenet tisztítása

    if (!empty($message)) {
        // Üzenet előkészítése a mentéshez
        $timestamp = date('Y-m-d H:i:s'); // Az üzenet küldésének ideje
        $entry = "$timestamp - $username ($role): $message\n";

        // Az üzenet mentése a fájlba
        file_put_contents('messages.txt', $entry, FILE_APPEND);

        // Átirányítjuk a felhasználót ugyanarra az oldalra, hogy ne küldjön újra üzenetet
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Üzenetek betöltése a messages.txt fájlból
$messages = file_exists('messages.txt') ? file_get_contents('messages.txt') : '';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Chat</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .chat-box { width: 80%; margin: 20px auto; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; }
        .chat-box h2 { text-align: center; }
        .messages { border: 1px solid #ddd; padding: 10px; height: 300px; overflow-y: scroll; background-color: #fff; margin-bottom: 10px; }
        .message { padding: 5px; margin-bottom: 10px; border-bottom: 1px solid #ddd; }
        textarea { width: 100%; padding: 10px; border-radius: 5px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
    <script>
        // Automatikus üzenet frissítés
        function refreshChat() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "load_messages.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("chat-messages").innerHTML = xhr.responseText;
                    var chatMessages = document.getElementById("chat-messages");
                    chatMessages.scrollTop = chatMessages.scrollHeight; // Görgetés az új üzenethez
                }
            };
            xhr.send();
        }

        setInterval(refreshChat, 0000);
    </script>
</head>
<body>
    <div class="chat-box">
        <h2>Global Chat</h2>

        <!-- Üzenetek megjelenítése -->
        <div class="messages" id="chat-messages">
            <?php
            if (!empty($messages)) {
                echo nl2br(htmlspecialchars($messages)); // A korábbi üzenetek megjelenítése
            }
            ?>
        </div>

        <!-- Üzenetküldés form -->
        <form method="post">
            <textarea name="message" rows="5" placeholder="Írd ide az üzeneted..."></textarea><br><br>
            <button type="submit">Üzenet küldése</button>
        </form>
        <br>
        <a href="logout.php">Kijelentkezés</a>
        
        <a href="login.php" class="button">Vissza a főoldalra</a>
        </div>
</body>
</html>
