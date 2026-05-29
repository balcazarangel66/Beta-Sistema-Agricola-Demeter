<?php
include 'Conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['user'];
    $password = $_POST['pass'];

    // Buscamos al usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    // Verificamos si existe y si la contraseña coincide
    if ($user && password_verify($password, $user['password'])) {
        
        // 🔥 CAMBIO CLAVE: Usa los mismos nombres que pusimos en presentacion.php
        $_SESSION['id_usuario'] = $user['id_usuario']; 
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $_SESSION['foto_perfil'] = !empty($user['foto_perfil']) ? $user['foto_perfil'] : 'default.png';
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];

        // 🚀 CAMBIO: Mandarlo a presentacion.php para ver el buscador con dropdown
        header("Location: presentacion.php"); 
        exit();
    } else {
        echo "<script>alert('Datos incorrectos, intenta de nuevo'); window.location='login.php';</script>";
    }
}
?>