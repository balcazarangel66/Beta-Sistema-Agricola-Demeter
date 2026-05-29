<?php
include 'Conexion.php';
session_start();

// Si no hay sesión, lo mandamos fuera
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = $_POST['nuevo_nombre'];
    $nuevo_email = $_POST['nuevo_email'];
    
    // 🔥 CAMBIO CLAVE: Usamos el nombre de sesión correcto
    $foto_actual = $_SESSION['foto_perfil'];

    // --- LÓGICA PARA LA IMAGEN (Se queda igual) ---
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nombre_archivo = $_FILES['foto']['name'];
        $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        
        // Nombre único para el Proyecto Deméter
        $nuevo_nombre_foto = "perfil_" . $id_usuario . "_" . time() . "." . $extension;
        $ruta_destino = "Recursos/fotos_perfil/" . $nuevo_nombre_foto;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $foto_final = $nuevo_nombre_foto;
        } else {
            $foto_final = $foto_actual; // Falla la subida
        }
    } else {
        $foto_final = $foto_actual; // No subió foto nueva
    }

    // --- ACTUALIZAR BASE DE DATOS ---
    try {
        // 🔥 CAMBIO CLAVE AQUÍ: De 'foto' a 'foto_perfil' para que coincida con tu captura
        $sql = "UPDATE usuarios SET nombre_usuario = ?, email = ?, foto_perfil = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_nombre, $nuevo_email, $foto_final, $id_usuario]);

        // 🔥 Actualizar las variables de sesión
        $_SESSION['nombre_usuario'] = $nuevo_nombre;
        $_SESSION['email'] = $nuevo_email;
        $_SESSION['foto_perfil'] = $foto_final;

        echo "<script>alert('¡Perfil de Deméter actualizado!'); window.location='presentacion.php';</script>";
    } catch (PDOException $e) {
        echo "Error al actualizar en la BD: " . $e->getMessage();
    }
}
?>