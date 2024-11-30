<?php
require 'config.php';
session_start();

// Zkontrolujeme, zda je uživatel přihlášen
if ( !isset( $_SESSION[ 'user_id' ] ) ) {
  header( "Location: login.php" );
  exit();
}

// Získání ID uživatele ze session
$user_id = $_SESSION[ 'user_id' ];

$success_message = '';
$error_message = '';

// Získání dnešního data ve formátu YYYY-MM-DD
$today_date = date( 'Y-m-d' );

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $category = (int)$_POST['category'];
  $expiration_date = $_POST['expiration_date'];
  $user_id = $_SESSION['user_id'];
  $file = $_FILES['file'];
  
  if (!empty($title) && !empty($description) && !empty($expiration_date) && !empty($category) && $file['error'] == 0) {
      // Zabezpečené nahrání souboru
      $allowed_types = ['application/pdf']; // Povolený typ souboru
      $upload_dir = 'userdata/' . $user_id . '/'; // Složka uživatele v userdata/

      // Ověření typu souboru
      if (in_array($file['type'], $allowed_types)) {
          // Vytvoření složky uživatele, pokud neexistuje
          if (!is_dir($upload_dir)) {
              mkdir($upload_dir, 0755, true);
          }

          // Vygenerování unikátního názvu souboru
          $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
          $file_name = uniqid() . '.' . $file_ext;
          $file_path = $upload_dir . $file_name;

          // Přesun souboru na server
          if (move_uploaded_file($file['tmp_name'], $file_path)) {
              // Záznam do databáze (včetně cesty k souboru)
              try {
                  $stmt = $conn->prepare("INSERT INTO doklady (title, description, expiration_date, user_id, category, file_path) VALUES (:title, :description, :expiration_date, :user_id, :category, :file_path)");
                  $stmt->bindParam(':title', $title);
                  $stmt->bindParam(':description', $description);
                  $stmt->bindParam(':expiration_date', $expiration_date);
                  $stmt->bindParam(':user_id', $user_id);
                  $stmt->bindParam(':category', $category);
                  $stmt->bindParam(':file_path', $file_name);
                  $stmt->execute();
                  $success_message = "Záznam byl úspěšně přidán spolu se souborem.";
              } catch (PDOException $e) {
                  $error_message = "Chyba při ukládání záznamu: " . $e->getMessage();
              }
          } else {
              $error_message = "Nepodařilo se nahrát soubor.";
          }
      } else {
          $error_message = "Nepodporovaný typ souboru. Nahrávejte pouze PDF soubory.";
      }
  } else {
      $error_message = "Prosím, vyplňte všechna pole a nahrajte platný soubor.";
  }
}

?>

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Přidat záznam</title>
<?php include 'styles.php'; // Obsahuje odkazy na CSS ?>
</head>
<body style="background: transparent;">
<div class="container m-0">
  <?php if ($success_message): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert"> <?php echo htmlspecialchars($success_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php elseif ($error_message): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert"> <?php echo htmlspecialchars($error_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>
  <form method="post" action="" enctype="multipart/form-data">
    <div class="input-group input-group-outline my-2">
      <label for="title" class="form-label">Název:</label>
      <input type="text" id="title" name="title" class="form-control" required>
    </div>
    
    <div class="input-group input-group-outline my-2">
      <label for="description" class="form-label">Popis:</label>
      <textarea id="description" name="description" class="form-control" rows="2" required></textarea>
    </div>
    
    <div class="my-2">
      <label for="category" class="form-label">Kategorie:</label>
      <select id="category" name="category" class="form-select" required>
        <option value="1">Doklady</option>
      </select>
    </div>
    
    <div class="mt-2 mb-4">
      <label for="expiration_date" class="form-label">Datum expirace:</label>
      <input type="date" id="expiration_date" name="expiration_date" class="form-control" value="<?php echo $today_date; ?>" required>
    </div>
    
    <!-- Nový prvek pro nahrání souboru -->
    <div class="mb-4">
      <label for="file" class="form-label">Přidat soubor (PDF):</label>
      <input type="file" id="file" name="file" class="form-control" accept=".pdf" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Uložit</button>
</form>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
