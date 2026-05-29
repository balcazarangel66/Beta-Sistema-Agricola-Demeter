<?php
$host = "localhost";
$db = "sistema_agricola"; // <-- nombre actualizado
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo de errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Seguridad para consultas preparadas
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    /*echo "Conexión exitosa a la base de datos sistema_agricola perra";*/
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>