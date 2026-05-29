<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistema_agricola";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo json_encode([
        "ok" => false,
        "error" => $mysqli->connect_error
    ]);
    exit;
}

// 🔥 ID DEL USUARIO LOGUEADO
$usuario_id = isset($_SESSION['id_usuario']) 
    ? (int)$_SESSION['id_usuario'] 
    : 0;

if ($usuario_id <= 0) {
    echo json_encode([
        "ok" => false,
        "error" => "Usuario no autenticado"
    ]);
    exit;
}

$sql = "
    SELECT 
        ca.cultivo_id, 
        ca.fecha_evento AS fecha, 
        ca.tipo_evento AS tipo, 
        ca.comentario,
        ca.fecha_siembra,
        ca.fecha_cosecha,
        m.latitud AS lat,
        m.longitud AS lon,
        m.nombre AS municipio_nombre
    FROM calendario_agricola ca
    JOIN municipios m ON ca.municipio_id = m.id
    WHERE ca.fecha_evento IS NOT NULL
    AND ca.usuario_id = $usuario_id

    UNION ALL

    SELECT 
        ca.cultivo_id, 
        ca.fecha_siembra AS fecha, 
        'siembra' AS tipo, 
        'Día de inicio' AS comentario,
        ca.fecha_siembra,
        ca.fecha_cosecha,
        m.latitud,
        m.longitud,
        m.nombre AS municipio_nombre
    FROM calendario_agricola ca
    JOIN municipios m ON ca.municipio_id = m.id
    WHERE ca.fecha_siembra IS NOT NULL
    AND ca.usuario_id = $usuario_id

    UNION ALL

    SELECT 
        ca.cultivo_id, 
        ca.fecha_cosecha AS fecha, 
        'cosecha' AS tipo, 
        'Día de fin' AS comentario,
        ca.fecha_siembra,
        ca.fecha_cosecha,
        m.latitud,
        m.longitud,
        m.nombre AS municipio_nombre
    FROM calendario_agricola ca
    JOIN municipios m ON ca.municipio_id = m.id
    WHERE ca.fecha_cosecha IS NOT NULL
    AND ca.usuario_id = $usuario_id

    ORDER BY fecha ASC
";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode([
        "ok" => false,
        "error" => $mysqli->error
    ]);
    exit;
}

$eventos = [];

while ($row = $result->fetch_assoc()) {

    $eventos[] = [
        "cultivo_id" => $row['cultivo_id'],
        "fecha" => $row['fecha'],
        "tipo" => $row['tipo'],
        "comentario" => $row['comentario'],
        "lat" => $row['lat'],
        "lon" => $row['lon'],
        "municipio" => $row['municipio_nombre'],
        "fecha_siembra" => $row['fecha_siembra'],
        "fecha_cosecha" => $row['fecha_cosecha']
    ];
}

echo json_encode([
    "ok" => true,
    "eventos" => $eventos
]);

$mysqli->close();
?>
