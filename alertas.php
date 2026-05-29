<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'Conexion.php';

if (isset($conexion)) { $db = $conexion; }
elseif (isset($con)) { $db = $con; }
elseif (isset($mysqli)) { $db = $mysqli; }
else { $db = mysqli_connect("localhost", "root", "", "sistema_agricola"); }

if (!isset($_SESSION['id_usuario'])) { exit; }

$id_logueado = intval($_SESSION['id_usuario']);
$apiKey = "acc32174c125bd302406f8c726b2af85";

$ver_todo = isset($_GET['ver_todo']) ? true : false;

$sql = "SELECT DISTINCT m.nombre FROM calendario_agricola c 
        JOIN municipios m ON c.municipio_id = m.id WHERE c.usuario_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id_logueado);
$stmt->execute();
$resultado = $stmt->get_result();

$urgentes = "";
$normales = "";
$contador_normales = 0;
$limite_normales = 3;
$hay_mas_normales = false;

while ($row = $resultado->fetch_assoc()) {
    $muni = $row['nombre'];
    $url = "https://api.openweathermap.org/data/2.5/weather?q=".urlencode($muni).",MX&appid=$apiKey&units=metric&lang=es";
    $raw = @file_get_contents($url);

    if ($raw) {
        $data = json_decode($raw, true);
        $temp = $data['main']['temp'];
        $viento = $data['wind']['speed'];
        $estado_clima = $data['weather'][0]['main'];
        $desc = ucfirst($data['weather'][0]['description']);
        
        $es_urgente = false;
        $avisos_texto = [];

        // Motor de Inferencia
        if ($temp <= 6) { $es_urgente = true; $avisos_texto[] = "❄️ RIESGO DE HELADA: Protege cultivos."; } 
        if (in_array($estado_clima, ['Rain', 'Drizzle', 'Thunderstorm'])) { $es_urgente = true; $avisos_texto[] = "🌧️ LLUVIA: Se recomienda NO regar."; }
        if ($viento > 5) { $es_urgente = true; $avisos_texto[] = "💨 VIENTO ($viento m/s): No fumigar."; }
        
        if (empty($avisos_texto)) { $avisos_texto[] = "Clima monitoreado: Todo estable."; }

        $color_muni = $es_urgente ? "#ff4d4d" : "#4da6ff";
        // Añadimos la clase 'urgente-item' si es una alerta crítica
        $clase_css = $es_urgente ? "urgente-item" : "normal-item";

        $html = "
        <div class='$clase_css' style='padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);'>
            <div style='display:flex; justify-content:space-between; align-items:center;'>
                <span style='color:$color_muni; font-weight:bold; font-size:0.75rem;'>" . strtoupper($muni) . "</span>
                <span style='color:#fff; font-size:0.8rem;'>{$temp}°C</span>
            </div>";
        
        foreach ($avisos_texto as $av) {
            $color_txt = $es_urgente ? "#ff4d4d" : "#eee";
            $html .= "<div style='color:$color_txt; font-size:0.75rem; margin-top:3px; font-weight:".($es_urgente?'bold':'normal').";'>$av</div>";
        }
        $html .= "<div style='color:#777; font-size:0.65rem; margin-top:2px;'>$desc — Viento: $viento m/s</div></div>";

        if ($es_urgente) {
            $urgentes .= $html;
        } else {
            if ($ver_todo || $contador_normales < $limite_normales) {
                $normales .= $html;
                $contador_normales++;
            } else {
                $hay_mas_normales = true;
            }
        }
    }
}

// Salida HTML
if ($urgentes != "") {
    echo "<div id='hasUrgente' style='background:rgba(255,77,77,0.1); padding:6px; border-bottom:1px solid #ff4d4d;'>
            <small style='color:#ff4d4d; font-weight:bold; margin-left:10px;'>⚠️ ALERTAS CRÍTICAS</small>
          </div>" . $urgentes;
}

if ($normales != "") {
    echo "<div style='background:rgba(77,166,255,0.1); padding:6px; border-bottom:1px solid #4da6ff; margin-top:5px;'>
            <small style='color:#4da6ff; font-weight:bold; margin-left:10px;'>🌤️ ESTADO GENERAL</small>
          </div>" . $normales;
}

// Botones Ver más / Ver menos
if ($ver_todo) {
    echo "<div onclick='event.stopPropagation(); cargarAlertas(false);' style='text-align:center; padding:12px; cursor:pointer; color:#4da6ff; font-size:0.75rem; background: rgba(255,255,255,0.05); font-weight:bold;'>
            - Ver menos
          </div>";
} elseif ($hay_mas_normales) {
    echo "<div onclick='event.stopPropagation(); cargarAlertas(true);' style='text-align:center; padding:12px; cursor:pointer; color:#4da6ff; font-size:0.75rem; background: rgba(255,255,255,0.05); font-weight:bold;'>
            - Ver más municipios...
          </div>";
}
?>