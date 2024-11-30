<!-- content.php -->
<h2>Přehled</h2>
<p></p>
<?php
session_start();
require 'config.php'; // Ujistěte se, že tato cesta je správná a že soubor obsahuje připojení k databázi.

if ( !isset( $conn ) ) {
  die( "Database connection not established." );
}

// Získání dnešního data ve formátu YYYY-MM-DD
$today_date = date( 'Y-m-d' );

try {
  // SQL dotaz pro výběr 5 nejbližších záznamů z obou tabulek
  $stmt = $conn->prepare( "
        (SELECT title, description, expiration_date, 'Doklady' as category FROM doklady WHERE expiration_date >= :today_date AND user_id = :user_id)
        UNION
        (SELECT title, description, expiration_date, 'Záruky' as category FROM zaruky WHERE expiration_date >= :today_date AND user_id = :user_id)
        ORDER BY expiration_date ASC
        LIMIT 5
    " );
  $stmt->bindParam( ':today_date', $today_date );
  $stmt->bindParam( ':user_id', $_SESSION[ 'user_id' ] );
  $stmt->execute();
  $records = $stmt->fetchAll( PDO::FETCH_ASSOC );

} catch ( PDOException $e ) {
  echo "Chyba: " . $e->getMessage();
  exit();
}
?>
<div class="alert alert-warning d-flex align-items-center mb-2" role="alert">
  <div>
    <span class="material-icons lh-base" style="position: relative;bottom: -3px;">warning</span> Tato aplikace je v otevřeném vývoji a je možné ji testovat. Pokud něco nefunguje prosím kontaktujte nás.
  </div>
</div>
<div class="card mb-4">

  <div class="card-body">
    <p>Vítejte na Expiratoru! Tato služba slouží k uložení expirací různých typů dokumentů a dokladů. V levém panelu níže najdete funkci "Přidat expiraci",
       která vám umožní jednoduše přidat novou kartu s datem expirace. Systém automaticky sleduje, kdy se expirace blíží, a vy můžete ve svém účtu nastavit upozornění.
        Každý den se standardně kontrolují všechny expirace, a pokud některá karta končí za 30 nebo 7 dní, budete upozorněni emailem. Upozornění lze kdykoliv vypnout.
         Karty můžete upravovat a mazat, a ty, které již prošly expirací, se automaticky přesunou do archivu, který najdete pod kartami.</p>
  </div>
</div>
<h2>Nejbližší expirace</h2>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Název</th>
      <th>Popis</th>
      <th>Datum expirace</th>
      <th>Kategorie</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($records)): ?>
    <?php foreach ($records as $record): ?>
    <tr>
      <td><?php echo htmlspecialchars($record['title']); ?></td>
      <td><?php echo htmlspecialchars($record['description']); ?></td>
      <td><?php echo htmlspecialchars($record['expiration_date']); ?></td>
      <td><?php echo htmlspecialchars($record['category']); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <tr>
      <td colspan="4">Žádné záznamy k dispozici.</td>
    </tr>
    <?php endif; ?>
  </tbody>
</table>
