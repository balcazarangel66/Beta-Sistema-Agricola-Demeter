<?php
include 'Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email_recuperar'];

    // 1. Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Generar código de 6 dígitos
        $codigo = rand(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime('+15 minutes')); // Expira en 15 min

        // 3. Guardar en la tabla que creamos (recuperacion_claves)
        $insert = $pdo->prepare("INSERT INTO recuperacion_claves (id_usuario, codigo, expiracion) VALUES (?, ?, ?)");
        $insert->execute([$user['id_usuario'], $codigo, $expiracion]);

        // 4. Aquí iría el código de PHPMailer para enviar el $codigo al $email
        echo "<script>alert('Código generado: $codigo (En un sistema real, esto te llegaría al correo)'); window.location='verificar_codigo.php';</script>";
        
    } else {
        echo "<script>alert('Ese correo no está registrado'); window.location='olvido.php';</script>";
    }
}
?>