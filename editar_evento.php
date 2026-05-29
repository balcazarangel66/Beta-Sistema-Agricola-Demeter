<?php
session_start();
header('Content-Type: application/json');

$usuario_id = (int)$_SESSION['id_usuario'];

$mysqli = new mysqli("localhost", "root", "", "sistema_agricola");
$mysqli->set_charset("utf8");

if ($mysqli->connect_errno) {
    echo json_encode([
        "ok" => false,
        "error" => $mysqli->connect_error
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// ================= DATOS =================
$fecha_simple = $data['fecha'];
$fecha = $fecha_simple . " 00:00:00";

$tipo = strtolower(trim($data['tipo']));
$cultivo_id = intval($data['cultivo_id']);

// normalizar
$tipo = str_replace("ó", "o", $tipo);

// ================= 🔥 OBTENER DATOS BASE =================
$stmtInfo = $mysqli->prepare("
    SELECT municipio_id, fecha_siembra, fecha_cosecha
    FROM calendario_agricola
    WHERE cultivo_id = ?
    AND fecha_siembra IS NOT NULL
    AND fecha_cosecha IS NOT NULL
    LIMIT 1
");

$stmtInfo->bind_param("i", $cultivo_id);
$stmtInfo->execute();

$resInfo = $stmtInfo->get_result();

if ($resInfo->num_rows === 0) {
    echo json_encode([
        "ok" => false,
        "error" => "No existe información base para ese cultivo"
    ]);
    exit;
}

$row = $resInfo->fetch_assoc();

$municipio_id = $row['municipio_id'];
$fecha_siembra = $row['fecha_siembra'];
$fecha_cosecha = $row['fecha_cosecha'];

// ================= BORRAR EVENTO =================
if (empty($tipo) || $tipo == 'borrar' || $tipo == 'ninguno') {

    $stmt = $mysqli->prepare("
        UPDATE calendario_agricola 
        SET tipo_evento = NULL 
        WHERE DATE(fecha_evento) = ?
        AND cultivo_id = ?
        AND usuario_id = ?
    ");

    $stmt->bind_param("sii", $fecha_simple, $cultivo_id, $usuario_id);
    $stmt->execute();

    echo json_encode([
        "ok" => true
    ]);

    exit;
}

// ================= BUSCAR SI YA EXISTE =================
$stmt = $mysqli->prepare("
    SELECT id
    FROM calendario_agricola 
    WHERE DATE(fecha_evento) = ?
    AND cultivo_id = ?
    AND usuario_id = ?
");

$stmt->bind_param("sii", $fecha_simple, $cultivo_id, $usuario_id);
$stmt->execute();

$result = $stmt->get_result();

// ================= UPDATE =================
if ($result->num_rows > 0) {

    $stmt = $mysqli->prepare("
        UPDATE calendario_agricola 
        SET tipo_evento = ?
        WHERE DATE(fecha_evento) = ?
        AND cultivo_id = ?
        AND usuario_id = ?
    ");

    $stmt->bind_param(
        "ssii",
        $tipo,
        $fecha_simple,
        $cultivo_id,
        $usuario_id
    );

// ================= INSERT =================
} else {

    $stmt = $mysqli->prepare("
        INSERT INTO calendario_agricola 
        (
            fecha_evento,
            tipo_evento,
            comentario,
            cultivo_id,
            municipio_id,
            fecha_siembra,
            fecha_cosecha,
            usuario_id
        ) 
        VALUES (?, ?, '', ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssiissi",
        $fecha,
        $tipo,
        $cultivo_id,
        $municipio_id,
        $fecha_siembra,
        $fecha_cosecha,
        $usuario_id
    );
}

// ================= EJECUTAR =================
if ($stmt->execute()) {

    echo json_encode([
        "ok" => true,
        "debug" => [
            "fecha" => $fecha,
            "tipo" => $tipo,
            "cultivo_id" => $cultivo_id,
            "municipio_id" => $municipio_id,
            "usuario_id" => $usuario_id
        ]
    ]);

} else {

    echo json_encode([
        "ok" => false,
        "error" => $stmt->error
    ]);
}

$mysqli->close();
?>