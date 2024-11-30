<?php
session_start(); // Spustí session

// Ověření, zda je uživatel přihlášen
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Získání uživatelského ID ze session
$user_id = $_SESSION['user_id'];

// Připojení k databázi
require 'config.php'; // Zahrnutí souboru s připojením k databázi

try {
    // Dotaz na získání údajů o uživateli
    $sql = "SELECT username, email, send_email_notifications, notify_30_days_before, notify_7_days_before FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Získání údajů o uživateli
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $username = htmlspecialchars($user['username']);
        $email = htmlspecialchars($user['email']);
        $send_email_notifications = $user['send_email_notifications'];
        $notify_30_days_before = $user['notify_30_days_before'];
        $notify_7_days_before = $user['notify_7_days_before'];
    } else {
        $error_message = "Uživatel nebyl nalezen.";
    }
} catch (PDOException $e) {
    $error_message = "Chyba: " . $e->getMessage();
}

// Zpracování odeslaného formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $send_email_notifications = isset($_POST['send_email_notifications']) ? 1 : 0;
    $notify_30_days_before = isset($_POST['notify_30_days_before']) ? 1 : 0;
    $notify_7_days_before = isset($_POST['notify_7_days_before']) ? 1 : 0;

    try {
        $sql = "UPDATE users SET send_email_notifications = :send_email_notifications, notify_30_days_before = :notify_30_days_before, notify_7_days_before = :notify_7_days_before WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':send_email_notifications', $send_email_notifications, PDO::PARAM_INT);
        $stmt->bindParam(':notify_30_days_before', $notify_30_days_before, PDO::PARAM_INT);
        $stmt->bindParam(':notify_7_days_before', $notify_7_days_before, PDO::PARAM_INT);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $success_message = "Nastavení byla úspěšně aktualizována.";
    } catch (PDOException $e) {
        $error_message = "Chyba: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Můj účet</title>
    <?php include 'styles.php'; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content border-radius-lg">
        <?php include 'header.php'; ?>
        <div class="container-fluid py-4">
            <h2 class="mb-3">Můj účet</h2>

            <?php if (isset($user)): ?>
                <div class="card muj-ucet">
                    <div class="card-header">
                        <h4>Osobní údaje</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Uživatelské jméno:</strong> <?php echo $username; ?></p>
                        <p><strong>Email:</strong> <?php echo $email; ?></p>

                        <form method="POST" action="ucet.php">
                            <h4>Nastavení upozornění</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_email_notifications" name="send_email_notifications" <?php echo $send_email_notifications ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="send_email_notifications">
                                    Odesílat upozornění emailem
                                </label>
								<p>Pokud zaškrtnete toto pole budete automaticky upozorněni na svůj email 30 a 7 dní dopředu, že se blíží některá z expirací.</p>
                            </div>
							<!--
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_30_days_before" name="notify_30_days_before" <?php echo $notify_30_days_before ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notify_30_days_before">
                                    Zasílat upozornění 30 dní před expirací
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_7_days_before" name="notify_7_days_before" <?php echo $notify_7_days_before ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notify_7_days_before">
                                    Zasílat upozornění 7 dní před expirací
                                </label>
                            </div>
-->
                            <button type="submit" class="btn btn-primary mt-3">Uložit změny</button>
                        </form>

                        <p class="mt-4"><a href="./logout.php" class="nav-link text-body font-weight-bold px-0" title="Odhlásit"><i class="fa-solid fa-power-off me-sm-1"></i> Odhlásit</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <?php include 'scripts.php'; ?>
</body>
</html>
