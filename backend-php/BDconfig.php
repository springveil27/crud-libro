<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'crud_libros';
$user = 'root';  // XAMPP
$pass = '';      // si no has puesto contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión a la base de datos',
        'details' => $e->getMessage()
    ]);
    exit;
}
