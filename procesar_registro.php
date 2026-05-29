<?php
include 'Conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['nombre_usuario'];
    $email = $_POST['email'];
    // Encriptamos la clave para que nadie la vea en HeidiSQL
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    // --- LÓGICA DE ROLES ---
    $rol = 'usuario';
    $admins_top = ['tu_correo@gmail.com', 'admin@demeter.com']; 
    if (in_array($email, $admins_top)) {
        $rol = 'admin';
    }

    // --- INSERTAR EN LA BASE DE DATOS (SIN FOTO) ---
    try {
        // Quitamos 'foto_perfil' de la lista de columnas y de los VALUES
        $sql = "INSERT INTO usuarios (nombre_usuario, password, email, rol) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        // Solo mandamos 4 datos en el execute
        $stmt->execute([$usuario, $password, $email, $rol]);

        echo "<script>
                alert('¡Usuario registrado con éxito, Xam! Ya puedes loguearte.');
                window.location.href = 'login.php';
              </script>";
              
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>