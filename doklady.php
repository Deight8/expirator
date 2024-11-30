<?php
require 'config.php';

session_start();

if ( !isset( $_SESSION[ 'user_id' ] ) ) {
  header( "Location: login.php" );
  exit();
}

$user_id = $_SESSION[ 'user_id' ];

// Definujte název tabulky a název služby
$table_name = 'doklady';  // Pro zaruky použijte 'zaruky'
$service_name = 'Doklady'; // Pro zaruky použijte 'Záruky'

try {
  // Dotaz na aktuální záznamy
  $sql_current = "
    SELECT d.id, d.title, d.description, d.expiration_date, k.title AS category_title, 
    DATEDIFF(d.expiration_date, CURDATE()) AS days_left, d.file_path
    FROM $table_name d
    JOIN kategorie k ON d.category = k.id
    WHERE d.user_id = :user_id AND d.expiration_date >= CURDATE()
    ORDER BY d.expiration_date ASC
";

  $stmt_current = $conn->prepare( $sql_current );
  $stmt_current->bindParam( ':user_id', $user_id, PDO::PARAM_INT );
  $stmt_current->execute();

  $current_records = $stmt_current->fetchAll( PDO::FETCH_ASSOC );

  // Dotaz na archivované záznamy
  $sql_archive = "
    SELECT d.id, d.title, d.description, d.expiration_date, k.title AS category_title, d.file_path
    FROM $table_name d
    JOIN kategorie k ON d.category = k.id
    WHERE d.user_id = :user_id AND d.expiration_date < CURDATE()
    ORDER BY d.expiration_date DESC
";

  $stmt_archive = $conn->prepare( $sql_archive );
  $stmt_archive->bindParam( ':user_id', $user_id, PDO::PARAM_INT );
  $stmt_archive->execute();

  $archived_records = $stmt_archive->fetchAll( PDO::FETCH_ASSOC );

} catch ( PDOException $e ) {
  echo "Chyba při dotazování databáze: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action === 'delete') {
        // Smazání záznamu
        try {
            $sql_delete = "DELETE FROM doklady WHERE id = :id AND user_id = :user_id";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_delete->execute();
            header("Location: doklady.php");
            exit();
        } catch (PDOException $e) {
            echo "Chyba při mazání záznamu: " . $e->getMessage();
        }
    }

    if ($action === 'edit') {
        // Načtení záznamu pro úpravu
        try {
            $sql_edit = "SELECT * FROM doklady WHERE id = :id AND user_id = :user_id";
            $stmt_edit = $conn->prepare($sql_edit);
            $stmt_edit->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_edit->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_edit->execute();
            $edit_doklad = $stmt_edit->fetch(PDO::FETCH_ASSOC);

            if ($edit_doklad) {
                // Zobrazit modální okno pro úpravu (budeme implementovat níže)
            } else {
                echo "Záznam nebyl nalezen.";
            }
        } catch (PDOException $e) {
            echo "Chyba při načítání záznamu: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Aktualizace záznamu
    try {
        $sql_update = "UPDATE doklady SET title = :title, description = :description, expiration_date = :expiration_date WHERE id = :id AND user_id = :user_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':title', $_POST['title'], PDO::PARAM_STR);
        $stmt_update->bindParam(':description', $_POST['description'], PDO::PARAM_STR);
        $stmt_update->bindParam(':expiration_date', $_POST['expiration_date'], PDO::PARAM_STR);
        $stmt_update->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_update->execute();
        header("Location: doklady.php");
        exit();
    } catch (PDOException $e) {
        echo "Chyba při aktualizaci záznamu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($service_name); ?></title>
<?php include 'styles.php'; // Obsahuje odkazy na CSS ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content border-radius-lg ">
  <?php include 'header.php'; ?>
  <div class="container-fluid py-4">
    <h1 class="mb-3 d-inline-flex justify-content-center align-items-center nadpis-zaruky"><div class="icon icon-sm icon-shape bg-gradient-info shadow-info text-center border-radius-md d-inline-flex justify-content-center align-items-center"> 
      <i class="material-icons opacity-10">description</i> </div> <?php echo htmlspecialchars($service_name); ?></h1>
    
    <!-- Seznam dokladů -->
    <div class="row mt-3">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg py-3">
                <h4 class="text-capitalize ps-3">Seznam dokladů</h4>
              </div>
              </div>
              </div>
              </div>
              </div>
    <?php if (!empty($current_records)): ?>
      <div class="row zaruky-cards doklady">
    <?php foreach ($current_records as $doklad): ?>
    <?php
    // Nastavení CSS třídy podle hodnoty days_left
    $class = '';
    if ($doklad['days_left'] < 7) {
        $class = 'dd-red';
    } elseif ($doklad['days_left'] < 30) {
        $class = 'dd-orange';
    }
    ?>
    <div class="col-xl-3 col-sm-6 mb-4 d-flex">
        <!-- Kliknutí na kartu otevře modální okno -->
        <div class="card w-100" data-bs-toggle="modal" data-bs-target="#detailModal-<?php echo $doklad['id']; ?>">
            <div class="card-header px-3 py-2">
                <div class="row">
                    <div class="col-9">
                        <div class="text-start pt-1">
                            <h2 class="mb-0"><?php echo htmlspecialchars($doklad['title']); ?></h2>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n3 d-flex justify-content-center align-items-center">
                            <i class="material-icons opacity-10">description</i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-3 py-1">
                <div class="row">
                    <div class="col-9">
                        <h4 class="mb-0"><small>Expiruje:</small> <?php echo htmlspecialchars($doklad['expiration_date']); ?></h4>
                        <p class="text-truncate"><?php echo htmlspecialchars($doklad['description']); ?></p>
                    </div>
                    <div class="col-3">
                        <div class="my-0 d-flex align-items-center flex-column text-md">Končí za:
                            <span class="text-success font-weight-bolder text-lg <?php echo $class; ?>"><?php echo htmlspecialchars($doklad['days_left']); ?> dní</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modální okno pro detaily dokladu -->
    <div class="modal fade" id="detailModal-<?php echo $doklad['id']; ?>" tabindex="-1" aria-labelledby="detailModalLabel-<?php echo $doklad['id']; ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel-<?php echo $doklad['id']; ?>">Detaily dokladu: <?php echo htmlspecialchars($doklad['title']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p><strong>Název:</strong> <?php echo htmlspecialchars($doklad['title']); ?></p>
            <p><strong>Popis:</strong> <?php echo htmlspecialchars($doklad['description']); ?></p>
            <p><strong>Datum expirace:</strong> <?php echo htmlspecialchars($doklad['expiration_date']); ?></p>
            <p><strong>Počet dní do expirace:</strong> <?php echo htmlspecialchars($doklad['days_left']); ?> dní</p>

            <!-- Odkaz ke stažení souboru, pokud existuje -->
            <?php if (!empty($doklad['file_path'])): ?>
              <p><strong>Soubor:</strong> <a href="download.php?file=<?php echo urlencode($doklad['file_path']); ?>" class="btn btn-sm btn-outline-secondary">Stáhnout soubor</a></p>
            <?php endif; ?>
            
            <!-- Tlačítko pro úpravu, které otevře modální okno pro editaci -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal-<?php echo $doklad['id']; ?>">Upravit</button>
            <a href="doklady.php?action=delete&id=<?php echo $doklad['id']; ?>" onclick="return confirm('Opravdu chcete tento záznam smazat?');" class="btn btn-danger">Smazat</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modální okno pro úpravu -->
    <div class="modal fade" id="editModal-<?php echo $doklad['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel-<?php echo $doklad['id']; ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel-<?php echo $doklad['id']; ?>">Upravit záznam: <?php echo htmlspecialchars($doklad['title']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Formulář pro úpravu -->
            <form method="POST" action="doklady.php">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars($doklad['id']); ?>">
              <div class="mb-3">
                <label for="title-<?php echo $doklad['id']; ?>" class="form-label">Název</label>
                <input type="text" class="form-control" id="title-<?php echo $doklad['id']; ?>" name="title" value="<?php echo htmlspecialchars($doklad['title']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="description-<?php echo $doklad['id']; ?>" class="form-label">Popis</label>
                <textarea class="form-control" id="description-<?php echo $doklad['id']; ?>" name="description" rows="3" required><?php echo htmlspecialchars($doklad['description']); ?></textarea>
              </div>
              <div class="mb-3">
                <label for="expiration_date-<?php echo $doklad['id']; ?>" class="form-label">Datum expirace</label>
                <input type="date" class="form-control" id="expiration_date-<?php echo $doklad['id']; ?>" name="expiration_date" value="<?php echo htmlspecialchars($doklad['expiration_date']); ?>" required>
              </div>
              <button type="submit" name="update" class="btn btn-primary">Uložit změny</button>
            </form>
            <!-- Zpráva o úspěšné aktualizaci -->
            <?php if (isset($_POST['update']) && $_POST['id'] == $doklad['id']): ?>
              <div class="alert alert-success mt-3">Záznam byl úspěšně aktualizován.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php endforeach; ?>
</div>

<?php else: ?>
    <p>Žádné doklady nebyly vytvořeny</p>
<?php endif; ?>

<?php if (!empty($archived_records)): ?>

  <div class="row archive">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg py-3">
                <h4 class="text-capitalize ps-3">Archiv expirovaných</h4>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
  <div class="table-responsive">
    <table class="table align-items-center mb-0">
      <thead>
        <tr>
          <th class="text-uppercase text-secondary font-weight-bolder opacity-7">Název</th>
          <th class="text-uppercase text-secondary font-weight-bolder opacity-7 ps-2">Popis</th>
          <th class="text-uppercase text-secondary font-weight-bolder opacity-7 ps-2">Datum expirace</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($archived_records as $doklad): ?>
        <tr>
          <td>
            <div class="d-flex px-2">
              <div>
                <div class="icon icon-sm icon-shape bg-gradient-info shadow-info text-center border-radius-md d-flex justify-content-center align-items-center">
                  <i class="material-icons opacity-10">description</i>
                </div>
              </div>
              <div class="my-auto">
                <h6 class="mb-0 ms-2 text-lg"><?php echo htmlspecialchars($doklad['title']); ?></h6>
              </div>
            </div>
          </td>
          <td>
            <p class="lead font-weight-normal m-0"><?php echo htmlspecialchars($doklad['description']); ?></p>
          </td>
          <td class="align-middle text-center">
            <div class="d-flex align-items-center">
              <span class="me-2 lead"><?php echo htmlspecialchars($doklad['expiration_date']); ?></span>
            </div>
          </td>
          <td class="align-middle">
            <!-- Tlačítko more_vert pro otevření modálního okna -->
            <button class="btn btn-link text-secondary mb-0" data-bs-toggle="modal" data-bs-target="#archiveDetailModal-<?php echo $doklad['id']; ?>">
              <span class="material-icons">more_vert</span>
            </button>
          </td>
        </tr>

        <!-- Modální okno pro detaily záznamu v archivu -->
        <div class="modal fade" id="archiveDetailModal-<?php echo $doklad['id']; ?>" tabindex="-1" aria-labelledby="archiveDetailModalLabel-<?php echo $doklad['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="archiveDetailModalLabel-<?php echo $doklad['id']; ?>">Detaily dokladu: <?php echo htmlspecialchars($doklad['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p><strong>Název:</strong> <?php echo htmlspecialchars($doklad['title']); ?></p>
                <p><strong>Popis:</strong> <?php echo htmlspecialchars($doklad['description']); ?></p>
                <p><strong>Datum expirace:</strong> <?php echo htmlspecialchars($doklad['expiration_date']); ?></p>

                <!-- Odkaz ke stažení souboru, pokud existuje -->
                <?php if (!empty($doklad['file_path'])): ?>
                  <p><strong>Soubor:</strong> <a href="download.php?file=<?php echo urlencode($doklad['file_path']); ?>" class="btn btn-sm btn-outline-secondary">Stáhnout soubor</a></p>
                <?php endif; ?>

                <!-- Tlačítko pro úpravu, které otevře modální okno pro editaci -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editArchiveModal-<?php echo $doklad['id']; ?>">Upravit</button>
                <a href="doklady.php?action=delete&id=<?php echo $doklad['id']; ?>" onclick="return confirm('Opravdu chcete tento záznam smazat?');" class="btn btn-danger">Smazat</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Modální okno pro úpravu záznamu v archivu -->
        <div class="modal fade" id="editArchiveModal-<?php echo $doklad['id']; ?>" tabindex="-1" aria-labelledby="editArchiveModalLabel-<?php echo $doklad['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editArchiveModalLabel-<?php echo $doklad['id']; ?>">Upravit záznam: <?php echo htmlspecialchars($doklad['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Formulář pro úpravu -->
                <form method="POST" action="doklady.php">
                  <input type="hidden" name="id" value="<?php echo htmlspecialchars($doklad['id']); ?>">
                  <div class="mb-3">
                    <label for="title-<?php echo $doklad['id']; ?>" class="form-label">Název</label>
                    <input type="text" class="form-control" id="title-<?php echo $doklad['id']; ?>" name="title" value="<?php echo htmlspecialchars($doklad['title']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label for="description-<?php echo $doklad['id']; ?>" class="form-label">Popis</label>
                    <textarea class="form-control" id="description-<?php echo $doklad['id']; ?>" name="description" rows="3" required><?php echo htmlspecialchars($doklad['description']); ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="expiration_date-<?php echo $doklad['id']; ?>" class="form-label">Datum expirace</label>
                    <input type="date" class="form-control" id="expiration_date-<?php echo $doklad['id']; ?>" name="expiration_date" value="<?php echo htmlspecialchars($doklad['expiration_date']); ?>" required>
                  </div>
                  <button type="submit" name="update" class="btn btn-primary">Uložit změny</button>
                </form>
                <!-- Zpráva o úspěšné aktualizaci -->
                <?php if (isset($_POST['update']) && $_POST['id'] == $doklad['id']): ?>
                  <div class="alert alert-success mt-3">Záznam byl úspěšně aktualizován.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
            </div>
          </div>
        </div>
      </div>

<?php else: ?>
  <p>Žádné archivované záznamy k zobrazení.</p>
<?php endif; ?>


  </div>
</main>
<?php include 'scripts.php'; ?>
<script>
    // Tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

  
  <!-- Footer -->
  <?php include 'footer.php'; ?>
  </div>
</main>
</div>
<?php include 'scripts.php'; // Obsahuje odkazy na JS ?>

<!-- Modální okno pro úpravu záznamu -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Upravit záznam</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="doklady.php">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_doklad['id']); ?>">
          <div class="mb-3">
            <label for="title" class="form-label">Název</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_doklad['title']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Popis</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($edit_doklad['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label for="expiration_date" class="form-label">Datum expirace</label>
            <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($edit_doklad['expiration_date']); ?>" required>
          </div>
          <button type="submit" name="update" class="btn btn-primary">Uložit změny</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    // Otevře modální okno, pokud je potřeba upravit záznam
    <?php if (isset($edit_doklad)): ?>
        var editModal = new bootstrap.Modal(document.getElementById('editModal'), {});
        editModal.show();
    <?php endif; ?>
</script>
</body>
</html>