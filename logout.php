<?php
// logout.php

session_start();
session_unset(); // Uvolní všechny proměnné relace
session_destroy(); // Zničí relaci
header("Location: login.php"); // Přesměrování na přihlašovací stránku
exit();
?>
