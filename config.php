<?php
// config.php

// Definování proměnných pro připojení k databázi
$host = 'md414.wedos.net';       // Adresa serveru databáze
$dbname = 'd258752_expi'; // Název databáze
$username = 'a258752_expi'; // Uživatelské jméno
$password = 'dkGGsCnC'; // Heslo

try {
    // Vytvoření nového PDO objektu pro připojení k databázi
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Zobrazí chybu, pokud se připojení nezdaří
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>