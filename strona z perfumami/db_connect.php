<?php
session_start();

$host = 'localhost';
$dbname = 'perfume_store';
$username = 'root'; // Dostosuj, jeśli inne
$password = '';     // Dostosuj, jeśli inne

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Połączenie nieudane: " . $e->getMessage());
}
?>