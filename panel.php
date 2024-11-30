<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Zobrazení úspěšné zprávy z registrace, pokud je nastavena
if ( isset( $_SESSION[ 'success_message' ] ) ) {
  $success_message = $_SESSION[ 'success_message' ];
  unset( $_SESSION[ 'success_message' ] ); // Vymazání zprávy po zobrazení
}

// Pokud je uživatel přihlášen, pokračujeme s vykreslením stránky
?>

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel</title>
<?php include 'styles.php'; // Obsahuje odkazy na CSS ?>
</head>
<body>
	
<?php include 'sidebar.php'; ?>
	
<main class="main-content border-radius-lg "> 
	
<?php include 'header.php'; ?>
  
  <div class="container-fluid py-4">
	<?php include 'content.php'; // Hlavní obsah specifický pro tuto stránku ?>
	  
    <!-- Footer -->
    <?php include 'footer.php'; ?>
	  
  </div>
</main>
</div>
<?php include 'scripts.php'; // Obsahuje odkazy na JS ?>
</body>
</html>
