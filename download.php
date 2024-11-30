<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['file'])) {
    $file = $_GET['file'];

    $user_id = $_SESSION['user_id'];
    $file_path = 'userdata/' . $user_id . '/' . $file;

    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    } else {
        echo "Soubor nebyl nalezen.";
    }
} else {
    echo "Soubor nebyl specifikován.";
}
?>
