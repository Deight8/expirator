<?php
require 'config.php';

session_start();

// Debug: Zkontrolujeme, zda jsou v session nějaké hodnoty
error_log( print_r( $_SESSION, true ) ); // Uložení session do logu pro diagnostiku

// Zkontrolujte, zda je uživatel již přihlášen
if ( isset( $_SESSION[ 'user_id' ] ) ) {
  // Pokud je uživatel přihlášen, přesměrujte ho na panel.php
  header( "Location: panel" );
  exit(); // Je důležité vždy ukončit skript po header
}

// Inicializace proměnné pro zprávy
$error_message = '';
$success_message = '';

// Zobrazení úspěšné zprávy z registrace, pokud je nastavena
if ( isset( $_SESSION[ 'success_message' ] ) ) {
  $success_message = $_SESSION[ 'success_message' ];
  unset( $_SESSION[ 'success_message' ] ); // Vymazání zprávy po zobrazení
}

if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
  $email = trim( $_POST[ 'email' ] );
  $password = trim( $_POST[ 'password' ] );
  $remember_me = isset( $_POST[ 'remember_me' ] ); // Zkontrolujte, zda je zaškrtávací pole zaškrtnuto

  if ( !empty( $email ) && !empty( $password ) ) {
    try {
      $sql = "SELECT id, email, password FROM users WHERE email = :email";
      $stmt = $conn->prepare( $sql );
      $stmt->bindParam( ':email', $email );
      $stmt->execute();

      $user = $stmt->fetch( PDO::FETCH_ASSOC );

      if ( $user ) {
        if ( password_verify( $password, $user[ 'password' ] ) ) {
          $_SESSION[ 'user_id' ] = $user[ 'id' ];

          // Nastavení cookies pro zapamatování přihlášení, pokud je zaškrtávací pole zaškrtnuto
          if ( $remember_me ) {
            $cookie_name = 'remember_me';
            $cookie_value = $user[ 'id' ];
            $cookie_expiry = time() + ( 86400 * 30 ); // 30 dní
            setcookie( $cookie_name, $cookie_value, $cookie_expiry, "/" );
          }

          // Uložení do session
          session_start();

          // Po úspěšné registraci
          $_SESSION[ 'success_message' ] = "Přihlášení proběhlo úspěšně, vítejte.";

          header( "Location: panel" );
          exit(); // Ujistěte se, že skript končí po přesměrování
        } else {
          $error_message = "<div class='alert alert-danger'>Špatné heslo, zkuste to prosím znovu.</div>";
        }
      } else {
        $error_message = "<div class='alert alert-danger'>Uživatel s tímto emailem neexistuje. Zkontrolujte svůj přihlašovací email nebo <a class='alert-link' href='./register.php'>se registrujte</a>.</div>";
      }
    } catch ( PDOException $e ) {
      $error_message = "<div class='alert alert-danger'>Chyba připojení, prosím kontaktujte nás.</div>";
    }
  } else {
    $error_message = "<div class='alert alert-danger'>Email a heslo jsou povinné.</div>";
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
  <div class="page-header align-items-start min-vh-100" style="background: url(assets/img/bg.webp);">
	  <span class="mask bg-gradient-black opacity-6"></span>
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
                <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Přihlášení</h4>
              </div>
            </div>
            <div class="card-body">
              <form method="post" action="" class="text-start">
                <div class="input-group input-group-outline my-3">
                  <label for="email" class="form-label">E-mail:</label>
                  <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="input-group input-group-outline mb-3">
                  <label for="password" class="form-label">Heslo:</label>
                  <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-check form-switch d-flex align-items-center mb-3">
                  <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" checked>
                  <label class="form-check-label mb-0 ms-3" for="rememberMe">Zapamatovat</label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Přihlásit se</button>
                </div>
              </form>
              <p class="mt-4 text-md text-center"> Ještě nemáte účet, <a href="./register.php">registrujte se</a>. </p>
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