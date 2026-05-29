<?php
// 1. INICIAR SESIÓN PRIMERO QUE NADA
session_start();
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistema_agricola";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo json_encode(["ok" => false, "error" => $mysqli->connect_error]);
    exit;
}

// 2. DETECTAR SI HAY SESIÓN REAL
$hay_sesion = isset($_SESSION['id_usuario']);
$usuario_id = $hay_sesion ? $_SESSION['id_usuario'] : null;

$input = json_decode(file_get_contents('php://input'), true);

/* BLOQUE DE GUARDADO: 
   Solo se ejecuta si el usuario está logueado. 
   Si no hay sesión, se salta hasta el final.
*/
if ($hay_sesion) {
    // Datos principales
    $cultivo = $input['cultivo'] ?? "maiz";
    $municipio = $input['municipio'] ?? "Desconocido";
    $lat = $input['lat'] ?? 0;
    $lon = $input['lon'] ?? 0;

    // --- Guardar municipio ---
    $stmt = $mysqli->prepare("SELECT id FROM municipios WHERE nombre = ? LIMIT 1");
    $stmt->bind_param("s", $municipio);
    $stmt->execute();
    $stmt->bind_result($municipio_id);
    if(!$stmt->fetch()){
        $stmt->close();
        $stmt = $mysqli->prepare("INSERT INTO municipios (nombre, latitud, longitud) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $municipio, $lat, $lon);
        $stmt->execute();
        $municipio_id = $stmt->insert_id;
    }
    $stmt->close();

    // --- Guardar cultivo ---
    $stmt = $mysqli->prepare("SELECT id FROM cultivos WHERE nombre = ? LIMIT 1");
    $stmt->bind_param("s", $cultivo);
    $stmt->execute();
    $stmt->bind_result($cultivo_id);
    if(!$stmt->fetch()){
        $stmt->close();
        $stmt = $mysqli->prepare("INSERT INTO cultivos (nombre, duracion_dias) VALUES (?, 120)");
        $stmt->bind_param("s", $cultivo);
        $stmt->execute();
        $cultivo_id = $stmt->insert_id;
    }
    $stmt->close();

    // --- Guardar pronóstico ---
    $stmt = $mysqli->prepare("INSERT INTO pronostico_clima (municipio_id, fecha, temp, humedad, lluvia, descripcion, uv, fase_lunar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $fecha_clima = date("Y-m-d H:i:s");
    $stmt->bind_param("isdddsss", $municipio_id, $fecha_clima, $input['temp'], $input['humedad'], $input['lluvia'], $input['descripcion'], $input['uv'], $input['fase_lunar']);
    $stmt->execute();
    $stmt->close();

    // --- Guardar Calendario ---
    $eventos = $input['eventos'];
    if (!empty($eventos)) {
        $stmt = $mysqli->prepare("INSERT INTO calendario_agricola (cultivo_id, municipio_id, fecha_evento, tipo_evento, comentario, fecha_siembra, fecha_cosecha, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($eventos as $e) {
            $fecha_evento = !empty($e['fecha']) ? date("Y-m-d", strtotime($e['fecha'])) : null;
            $fecha_siembra_val = !empty($input['fecha_siembra']) ? date("Y-m-d", strtotime($input['fecha_siembra'])) : null;
            $fecha_cosecha_val = !empty($input['fecha_cosecha']) ? date("Y-m-d", strtotime($input['fecha_cosecha'])) : null;

            $stmt->bind_param("iisssssi", $cultivo_id, $municipio_id, $fecha_evento, $e['tipo'], $comentario, $fecha_siembra_val, $fecha_cosecha_val, $usuario_id);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// 3. RESPUESTA FINAL
if ($hay_sesion) {
    echo json_encode(["ok" => true, "mensaje" => "Guardado en DB"]);
} else {
    // Si no hubo sesión, mandas el mensaje de agradecimiento
    echo json_encode([
        "ok" => true, 
        "mensaje" => "Gracias por usar nuestro sistema",
        "modo" => "invitado" 
    ]);
}
?>