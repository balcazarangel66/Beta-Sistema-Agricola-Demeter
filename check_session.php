<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo json_encode([
    "logueado" => isset($_SESSION['id_usuario'])
]);