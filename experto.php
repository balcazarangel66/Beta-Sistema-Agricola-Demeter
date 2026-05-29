<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$tipo = strtolower($input['tipo'] ?? '');
$lluvia = $input['lluvia'] ?? false;
$viento = (float)($input['viento'] ?? 0);
$temp = (float)($input['temp'] ?? 0);
$municipio = $input['municipio'] ?? 'Desconocido';

$respuesta = "✅ Condiciones aceptables para laborar.";

// --- LÓGICA DE REGLAS POR ACTIVIDAD ---

if ($tipo == "riego") {
    if ($lluvia) {
        $respuesta = "❌ CANCELAR: Ya hay lluvia. Regar sería desperdicio de agua y riesgo de asfixia radicular.";
    } elseif ($temp > 32) {
        $respuesta = "⚠️ PRECAUCIÓN: Mucho calor. El agua se evaporará rápido. Riega solo al caer el sol.";
    } elseif ($viento > 7) {
        $respuesta = "⚠️ VIENTO: El viento desviará los aspersores. El riego no será uniforme.";
    } else {
        $respuesta = "✅ ÓPTIMO: Buen momento para hidratar el cultivo.";
    }
}

elseif ($tipo == "fumigacion" || $tipo == "fumigación") {
    if ($lluvia) {
        $respuesta = "❌ CANCELAR: La lluvia lavará el agroquímico antes de que la planta lo absorba.";
    } elseif ($viento > 5) {
        $respuesta = "❌ PELIGRO: Viento excesivo ($viento m/s). Riesgo de deriva (el químico caerá en otro lado).";
    } elseif ($temp > 28) {
        $respuesta = "⚠️ CALOR: Algunos productos pueden quemar la hoja (fitotoxicidad) con sol fuerte.";
    } else {
        $respuesta = "✅ ÓPTIMO: El químico se fijará correctamente.";
    }
}

elseif ($tipo == "fertilizacion" || $tipo == "fertilización") {
    if ($lluvia) {
        $respuesta = "⚠️ OJO: Si la lluvia es ligera, ayuda a que el grano baje al suelo. Si es tormenta, se lo llevará.";
    } elseif ($temp < 5) {
        $respuesta = "⚠️ FRÍO: La planta está en dormancia por frío, no aprovechará bien los nutrientes.";
    } else {
        $respuesta = "✅ ÓPTIMO: La planta está activa para absorber el fertilizante.";
    }
}

elseif ($tipo == "poda") {
    if ($lluvia) {
        $respuesta = "❌ CANCELAR: La humedad en cortes frescos invita a hongos y bacterias (Chancros).";
    } elseif ($temp < 0) {
        $respuesta = "❌ PELIGRO: Podar en helada daña los tejidos internos de la rama.";
    } else {
        $respuesta = "✅ ÓPTIMO: Cicatrización rápida garantizada.";
    }
}

elseif ($tipo == "deshierbe") {
    if ($temp > 35) {
        $respuesta = "⚠️ SALUD: Riesgo de golpe de calor para el personal. Trabajar con mucha hidratación.";
    } elseif ($lluvia) {
        $respuesta = "⚠️ SUELO: El lodo dificultará el arranque manual de maleza.";
    } else {
        $respuesta = "✅ ADELANTE: Tierra seca facilita sacar la raíz.";
    }
}

elseif ($tipo == "cosecha") {
    if ($lluvia) {
        $respuesta = "❌ CANCELAR: Cosechar con humedad pudre el fruto en el almacenamiento.";
    } else {
        $respuesta = "✅ ÉXITO: Buen tiempo para levantar la producción.";
    }
}

echo json_encode([
    "ok" => true,
    "recomendacion" => $respuesta,
    "municipio" => strtoupper($municipio)
]);