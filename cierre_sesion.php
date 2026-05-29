<?php
session_start(); // Nos colgamos de la sesión actual
session_unset(); // Borra las variables de la sesión
session_destroy(); // Destruye la sesión por completo

// Te manda de regreso a la página principal
header("Location: presentacion.php");
exit();
?>