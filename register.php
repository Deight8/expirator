<?php
// register.php

require 'config.php'; // Ujistěte se, že obsahuje připojení k databázi

session_start(); // Spuštění session

// Debug: Zkontrolujeme, zda jsou v session nějaké hodnoty
error_log( print_r( $_SESSION, true ) ); // Uložení session do logu pro diagnostiku

// Zkontrolujte, zda je uživatel již přihlášen
if ( isset( $_SESSION[ 'user_id' ] ) ) {
  // Pokud je uživatel přihlášen, přesměrujte ho na panel.php
  header( "Location: panel.php" );
  exit(); // Je důležité vždy ukončit skript po header
}

// Inicializace proměnné pro zprávy
$error_message = '';
$success_message = '';

// Zpracování formuláře po odeslání
if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
  // Získání a čištění vstupů
  $username = trim( $_POST[ 'username' ] );
  $email = trim( $_POST[ 'email' ] ); // Přidáno pole pro e-mail
  $password = trim( $_POST[ 'password' ] );
  $confirm_password = trim( $_POST[ 'confirm_password' ] );

  // Kontrola, zda uživatelské jméno, e-mail a heslo nejsou prázdné
  if ( !empty( $username ) && !empty( $email ) && !empty( $password ) && !empty( $confirm_password ) ) {
    // Kontrola, zda hesla odpovídají
    if ( $password === $confirm_password ) {
      try {
        // Hashování hesla
        $hashed_password = password_hash( $password, PASSWORD_BCRYPT );

        // Příprava SQL dotazu pro vložení nového uživatele
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare( $sql );
        $stmt->bindParam( ':username', $username );
        $stmt->bindParam( ':email', $email );
        $stmt->bindParam( ':password', $hashed_password );
        $stmt->execute();

        // Získání ID nového uživatele
        $user_id = $conn->lastInsertId();

        // Uložení uživatelského ID do session
        session_start();

        // Po úspěšné registraci
        $_SESSION[ 'success_message' ] = "Registrace byla úspěšná. Můžete se přihlásit.";

        // Přesměrování na přihlašovací stránku
        header( "Location: login.php" );
        exit();


      } catch ( PDOException $e ) {
        $error_message = "Chyba: " . $e->getMessage();
      }
    } else {
      $error_message = "Hesla se neshodují.";
    }
  } else {
    $error_message = "Vyplňte všechna pole.";
  }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Přihlášení</title>
<?php include 'styles.php'; // CSS styly ?>
</head>
<body class="bg-gray-200">
<?php include 'index_head.php'; ?>
<main class="main-content mt-0">
  <div class="page-header align-items-start min-vh-100" style="background: url(assets/img/bg.webp);"> <span class="mask bg-gradient-black opacity-6"></span>
    <div class="container my-auto">
      <?php if (isset($error_message)): ?>
      <?php echo $error_message; ?>
      <?php endif; ?>
      <?php if ($success_message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert"> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-lg-4 col-md-8 col-12 mx-auto">
          <div class="card z-index-0 fadeIn3 fadeInBottom">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Registrace</h4>
              </div>
            </div>
            <div class="card-body">
              <form method="post" action="">
                <div class="input-group input-group-outline my-3">
                  <label for="username" class="form-label">Uživatelské jméno:</label>
                  <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="input-group input-group-outline my-3">
                  <label for="email" class="form-label">E-mail:</label>
                  <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="input-group input-group-outline my-3">
                  <label for="password" class="form-label">Heslo:</label>
                  <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="input-group input-group-outline my-3">
                  <label for="confirm_password" class="form-label">Potvrdit heslo:</label>
                  <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Registrovat</button>
              </form>
              <p>Máte již účet, <a href="./login.php">přihlašte se</a>.</p>
            </div>
          </div>
        </div>
      </div>
      <?php include 'footer.php'; ?>
    </div>
  </div>
  </div>
</main>
<?php include 'scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('.input-group .form-control');

    inputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.closest('.input-group').classList.add('is-focused');
        });

        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.closest('.input-group').classList.remove('is-focused');
            }
        });
    });
});

</script>
</body>
</html>
