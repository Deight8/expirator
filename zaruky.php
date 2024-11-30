<?php
// zaruky.php

require 'config.php';
session_start();

// Zkontrolujeme, zda je uživatel přihlášen
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Předpokládáme, že user_id je uloženo v session po přihlášení

// Načítání kategorií
try {
    $sql = "SELECT id, title FROM kategorie";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Chyba: " . $e->getMessage();
}

// Načítání záruk uživatele
try {
    $sql = "SELECT z.id, z.title, z.description, z.expiration_date, k.title AS category_title
            FROM zaruky z
            JOIN kategorie k ON z.category = k.id
            WHERE z.user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $zaruky = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Chyba: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Záruky</title>
<?php include 'styles.php'; // Obsahuje odkazy na CSS ?>
</head>
<body>
	
<?php include 'sidebar.php'; ?>
	
<main class="main-content border-radius-lg "> 
	
<?php include 'header.php'; ?>
  
  <div class="container-fluid py-4">

    <h2>Záruky</h2>

    <!-- Seznam -->
    <h3 class="mt-5">Seznam záruk</h3>
    <?php if (!empty($zaruky)): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Název</th>
                <th>Popis</th>
                <th>Datum expirace</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zaruky as $zaruka): ?>
            <tr>
                <td><?php echo htmlspecialchars($zaruka['title']); ?></td>
                <td><?php echo htmlspecialchars($zaruka['description']); ?></td>
                <td><?php echo htmlspecialchars($zaruka['expiration_date']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Žádné záruky ještě nebyly přidány.</p>
    <?php endif; ?>
</div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
	  
  </div>
</main>
</div>
<?php include 'scripts.php'; // Obsahuje odkazy na JS ?>
</body>
</html>