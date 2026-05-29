<?php

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

// =========================================================================
// BLOQUE 1: GRANOS (MAÍZ Y FRIJOL)
// =========================================================================
function obtenerGranosSNIIM($url) {
    $contexto = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
            "timeout" => 10
        ]
    ]);

    $html = @file_get_contents($url, false, $contexto);
    if (!$html) return null;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8" ?>' . $html);
    $xpath = new DOMXPath($dom);

    $rows = $xpath->query("//tr");
    $resultados = [];

    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName("td");

        if ($cols->length >= 5) {
            $destino = $cols->item(1) ? trim($cols->item(1)->textContent) : 'Mercado General';
            $min = $cols->item(2) ? trim($cols->item(2)->textContent) : '';
            $max = $cols->item(3) ? trim($cols->item(3)->textContent) : '';
            $fre = $cols->item(4) ? trim($cols->item(4)->textContent) : '';

            $min = str_replace([',', ' ', '$'], '', $min);
            $max = str_replace([',', ' ', '$'], '', $max);
            $fre = str_replace([',', ' ', '$'], '', $fre);

            if (is_numeric($min) && is_numeric($max) && is_numeric($fre) && (float)$fre > 0) {
                $destino = mb_convert_encoding($destino, 'UTF-8', 'UTF-8, ISO-8859-1');
                $resultados[] = [
                    "mercado" => $destino,
                    "minimo" => (float)$min,
                    "maximo" => (float)$max,
                    "frecuente" => (float)$fre
                ];
            }
        }
    }
    return (count($resultados) > 0) ? $resultados[0] : null;
}

// =========================================================================
// BLOQUE 2: FRUTAS Y HORTALIZAS (CACAHUATE)
// =========================================================================
function obtenerFrutasSNIIM($url) {
    $contexto = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0\r\n",
            "timeout" => 10
        ]
    ]);

    $html = @file_get_contents($url, false, $contexto);
    if (!$html) return null;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8" ?>' . $html);
    $xpath = new DOMXPath($dom);

    $rows = $xpath->query("//tr");
    $resultados = [];

    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName("td");

        if ($cols->length >= 5) {

            $destino = trim($cols->item(1)->textContent ?? '');
            $min     = trim($cols->item(2)->textContent ?? '');
            $max     = trim($cols->item(3)->textContent ?? '');
            $fre     = trim($cols->item(4)->textContent ?? '');

            $min = str_replace([',','$',' '], '', $min);
            $max = str_replace([',','$',' '], '', $max);
            $fre = str_replace([',','$',' '], '', $fre);

            if (is_numeric($min) && is_numeric($max) && is_numeric($fre)) {
                $resultados[] = [
                    "mercado" => $destino,
                    "minimo" => (float)$min,
                    "maximo" => (float)$max,
                    "frecuente" => (float)$fre
                ];
            }
        }
    }

    return $resultados[0] ?? null;
}

// =========================================================================
// BLOQUE 3: PROCESAMIENTO ADAPTATIVO
// =========================================================================
$hoy = date("d/m/Y");

$productos = [
    "maiz" => "https://www.economia-sniim.gob.mx/nuevo/Consultas/MercadosNacionales/PreciosDeMercado/Agricolas/ResultadosConsultaFechaGranos.aspx?Semana=3&Mes=5&Anio=2026&ProductoId=605&OrigenId=11&Origen=Guanajuato&DestinoId=-1&Destino=Todos&RegistrosPorPagina=500",
    
    "frijol_flor_junio" => "https://www.economia-sniim.gob.mx/nuevo/Consultas/MercadosNacionales/PreciosDeMercado/Agricolas/ResultadosConsultaFechaGranos.aspx?Semana=3&Mes=5&Anio=2026&ProductoId=339&OrigenId=11&Origen=Guanajuato&DestinoId=-1&Destino=Todos&RegistrosPorPagina=500",
    
    "cacahuate" => "https://www.economia-sniim.gob.mx/nuevo/Consultas/MercadosNacionales/PreciosDeMercado/Agricolas/ResultadosConsultaFechaFrutasYHortalizas.aspx?fechaInicio=26/05/2026&fechaFinal=26/05/2026&ProductoId=181&OrigenId=11&Origen=Guanajuato&DestinoId=-1&Destino=Todos&PreciosPorId=1&RegistrosPorPagina=500"
];

$response = [];

foreach ($productos as $nombre => $url) {
    if ($nombre === "cacahuate") {
        $data = obtenerFrutasSNIIM($url);
    } else {
        $data = obtenerGranosSNIIM($url);
    }

    if ($data) {
        $response[$nombre] = [
            "error" => false,
            "minimo" => $data["minimo"],
            "maximo" => $data["maximo"],
            "frecuente" => $data["frecuente"],
            "tonelada" => $data["frecuente"] * 1000,
            "mercado" => $data["mercado"]
        ];
    } else {
        // Si no encuentra nada, reporta el error real de obtención
        $response[$nombre] = [
            "error" => true,
            "mensaje" => "No se encontraron datos en tiempo real en el SNIIM para este producto."
        ];
    }
}

echo json_encode([
    "error" => false,
    "datos" => $response,
    "fuente" => "SNIIM Real-Time Pure Scraper",
    "fecha" => date("Y-m-d H:i:s")
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);