<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8" />
    <title>Clima Agrícola México - Pronóstico 3h</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/pikaday/css/pikaday.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
    </style>
</head>
<body>

    <div class="buscador">
        <h2>Bienvenido</h2>
        <br>
        <h2>🌽Busca el Clima Agrícola en Municipios de México🌽</h2>
        <input type="text" id="lugar" placeholder="Escribe un municipio (Ej: Celaya)" />
        
        <label for="cultivo" style="display: block; margin: 0 auto 10px auto; font-weight: bold; max-width: 350px;">Selecciona cultivo o fruto:</label>
        <select id="cultivo" onchange="actualizarCultivoCalendario(); buscarClima()">
            <option value="">-- Elige un cultivo --</option>
            <option value="maiz">Maíz</option>
            <option value="tomate">Tomate</option>
            <option value="aguacate">Aguacate</option>
            <option value="fresa">Fresa</option>
            <option value="mango">Mango</option>
            <option value="canadeazucar">Caña de azúcar</option>
            <option value="chile">Chile</option>
            <option value="citricos">Cítricos (naranja, limón)</option>
            <option value="papaya">Papaya</option>
            <option value="cafe">Café</option>
        </select>
        <br>
        
    </div>

    <div class="contenedor-mapa">
        <div id="map"></div>
        <div class="info" id="resultado">
            🔍 Busca un lugar para mostrar el clima...
        </div>
    </div>

    <div class="contenedor-mapa" id="pronostico-dias" style="padding: 15px; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 10px;"></div>

    <div id="chart-container">
        <canvas id="chartTempLluvia"></canvas>
    </div>

    <div class="info" id="uv-luna"></div>

    <div class="info" id="consejos-agricolas" style="text-align: left;"></div>

    <div id="calendario-agricola-container">
        <h3>📅 Calendario Agrícola Personalizado</h3>
        <p>Genera un calendario de riego y fertilización para tu cultivo.</p>
        
        <label for="cultivo-select">Selecciona el cultivo:</label>
        <select id="cultivo-select">
            <option value="">-- Elige un cultivo --</option>
            <option value="maiz" data-duracion="120">Maíz (120 días)</option>
            <option value="tomate" data-duracion="90">Tomate (90 días)</option>
            <option value="aguacate" data-duracion="365">Aguacate (Ciclo anual)</option>
            <option value="fresa" data-duracion="180">Fresa (180 días)</option>
            <option value="mango" data-duracion="365">Mango (Ciclo anual)</option>
            <option value="canadeazucar" data-duracion="365">Caña de azúcar (Ciclo anual)</option>
            <option value="chile" data-duracion="100">Chile (100 días)</option>
            <option value="citricos" data-duracion="365">Cítricos (naranja, limón) (Ciclo anual)</option>
            <option value="papaya" data-duracion="240">Papaya (240 días)</option>
            <option value="cafe" data-duracion="365">Café (Ciclo anual)</option>
        </select>

        <label for="fecha-siembra">Fecha de Siembra/Plantación:</label>
        <input type="text" id="fecha-siembra" placeholder="Selecciona una fecha" readonly />

        <label for="fase-crecimiento">Fase de crecimiento:</label>
        <select id="fase-crecimiento">
            <option value="">-- Selecciona la fase --</option>
            <option value="recien_plantado">Recién plantada</option>
            <option value="semanas">Semanas</option>
            <option value="meses">Meses</option>
        </select>
        
        <button id="btn-generar">Generar Calendario</button>

        <div id="calendario">
            </div>

        <div id="mensajes-calendario">
            </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/pikaday/pikaday.js"></script>
    <script>
        var map = L.map('map').setView([23.6345, -102.5528], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        let marker;
        let chart;
        let cachedWeatherData = null; // Cache to store weather data

        const cultivosInfo = {
            maiz: {
                nombre: "Maíz",
                consejoGeneral: "El maíz requiere riego frecuente en etapa de crecimiento y menos en maduración.",
                tempOptimaFert: [18, 30],
                riegos: { // Riego en días
                    recien_plantado: 2, // Cada 2 días
                    semanas: 2, // Cada 2 días
                    meses: 7 // Cada 7 días (disminuye en maduración)
                },
                fertilizaciones: { // Fertilización en días
                    recien_plantado: 7, // Cada 7 días (nitrógeno)
                    semanas: 10, // Cada 10 días (nitrógeno)
                    meses: 20 // Cada 20 días (fósforo y potasio, si aplica)
                },
            },
            tomate: {
                nombre: "Tomate",
                consejoGeneral: "El tomate necesita riego moderado y fertilización durante la floración y fructificación.",
                tempOptimaFert: [16, 28],
                riegos: {
                    recien_plantado: 2,
                    semanas: 1, // Diario o cada 2 días
                    meses: 1 // Diario o cada 2 días (mantener humedad)
                },
                fertilizaciones: {
                    recien_plantado: 10, // Después de unos 10 días
                    semanas: 7, // Frecuente en floración/fructificación
                    meses: 15 // Mantenimiento
                },
            },
            aguacate: {
                nombre: "Aguacate",
                consejoGeneral: "Evita riego excesivo y encharcamientos; prefiere suelos bien drenados.",
                tempOptimaFert: [15, 27],
                riegos: {
                    recien_plantado: 3, // 2-3 veces por semana
                    semanas: 4, // 1-2 veces por semana
                    meses: 7 // Semanal o cada 10 días (árbol maduro)
                },
                fertilizaciones: {
                    recien_plantado: 30, // Mensual (árbol joven)
                    semanas: 20, // Más frecuente en crecimiento activo
                    meses: 60 // Bimensual o trimestral (árbol maduro)
                },
            },
            fresa: {
                nombre: "Fresa",
                consejoGeneral: "Prefiere riego constante y no tolera encharcamientos.",
                tempOptimaFert: [12, 25],
                riegos: {
                    recien_plantado: 1, // Diario
                    semanas: 1, // Diario
                    meses: 1 // Diario, especialmente en producción
                },
                fertilizaciones: {
                    recien_plantado: 7, // Semanal
                    semanas: 7, // Semanal
                    meses: 10 // Cada 10 días
                },
            },
            mango: {
                nombre: "Mango",
                consejoGeneral: "Riego moderado; cuidado con exceso de humedad para evitar enfermedades.",
                tempOptimaFert: [20, 32],
                riegos: {
                    recien_plantado: 2, // Cada 2-3 días
                    semanas: 3, // Cada 3-5 días
                    meses: 10 // Cada 10-15 días (reducir antes de floración)
                },
                fertilizaciones: {
                    recien_plantado: 20, // Cada 2-3 semanas
                    semanas: 15, // Cada 1-2 semanas
                    meses: 30 // Mensual (en épocas de crecimiento)
                },
            },
            canadeazucar: {
                nombre: "Caña de azúcar",
                consejoGeneral: "Necesita abundante agua y temperaturas cálidas para un buen desarrollo.",
                tempOptimaFert: [22, 35],
                riegos: {
                    recien_plantado: 4, // Cada 3-5 días
                    semanas: 6, // Cada 5-7 días
                    meses: 15 // Reducir en maduración (cada 10-15 días)
                },
                fertilizaciones: {
                    recien_plantado: 15, // Cada 2 semanas
                    semanas: 10, // Semanal o cada 10 días
                    meses: 30 // Mensual (mantenimiento)
                },
            },
            chile: {
                nombre: "Chile",
                consejoGeneral: "Requiere riego frecuente y suelo bien drenado.",
                tempOptimaFert: [18, 30],
                riegos: {
                    recien_plantado: 2,
                    semanas: 2, // Cada 2 días
                    meses: 1 // Diario o cada 2 días en fructificación
                },
                fertilizaciones: {
                    recien_plantado: 10,
                    semanas: 7, // Frecuente en floración/fructificación
                    meses: 15
                },
            },
            citricos: {
                nombre: "Cítricos (naranja, limón)",
                consejoGeneral: "Prefieren riego moderado y buena fertilización en primavera y verano.",
                tempOptimaFert: [15, 28],
                riegos: {
                    recien_plantado: 3, // 2-3 veces por semana
                    semanas: 4, // 1-2 veces por semana
                    meses: 10 // Cada 7-15 días (según estación)
                },
                fertilizaciones: {
                    recien_plantado: 30, // Mensual (árbol joven)
                    semanas: 20, // Cada 2-3 semanas
                    meses: 30 // Mensual o estacional (árbol maduro)
                },
            },
            papaya: {
                nombre: "Papaya",
                consejoGeneral: "Necesita riego constante y temperaturas cálidas; no tolera frío ni encharcamientos.",
                tempOptimaFert: [22, 33],
                riegos: {
                    recien_plantado: 1, // Diario
                    semanas: 2, // Cada 2-3 días
                    meses: 2 // Cada 2-3 días
                },
                fertilizaciones: {
                    recien_plantado: 7, // Semanal
                    semanas: 7, // Semanal
                    meses: 10 // Cada 10 días
                },
            },
            cafe: {
                nombre: "Café",
                consejoGeneral: "Prefiere sombra parcial y riego regular, sin encharcamientos.",
                tempOptimaFert: [15, 25],
                riegos: {
                    recien_plantado: 2, // Cada 2-3 días
                    semanas: 3, // Cada 3-5 días
                    meses: 5 // Cada 5-7 días
                },
                fertilizaciones: {
                    recien_plantado: 15, // Cada 2 semanas
                    semanas: 10, // Semanal o cada 10 días
                    meses: 20 // Cada 2-3 semanas
                },
            },
        };

        function calcularFaseLunar(fecha = new Date()) {
            const lunacion = 29.530588853;
            const nuevaLuna = new Date(2000, 0, 6, 18, 14); // Epoch of new moon
            const diff = (fecha - nuevaLuna) / 1000 / 86400;
            const fase = diff % lunacion;

            if (fase < 1.84566) return "Luna Nueva";
            else if (fase < 5.53699) return "Luna Creciente Casi Nueva";
            else if (fase < 9.22831) return "Cuarto Creciente";
            else if (fase < 12.91963) return "Gibosa Creciente";
            else if (fase < 16.61096) return "Luna Llena";
            else if (fase < 20.30228) return "Gibosa Menguante";
            else if (fase < 23.99361) return "Cuarto Menguante";
            else if (fase < 27.68493) return "Luna Menguante Casi Nueva";
            else return "Luna Nueva";
        }

        async function buscarClima() {
            const inputLugar = document.getElementById('lugar');
            const lugar = inputLugar.value.trim();
            const cultivo = document.getElementById('cultivo').value;

            if (!lugar && !cachedWeatherData) { // Only alert if no place entered and no cached data
                document.getElementById('consejos-agricolas').innerHTML = '<p>Ingresa un municipio y selecciona un cultivo para obtener consejos.</p>';
                return; // No need to alert twice
            }

            let lat, lon, temp, humedad, descripcion, lluvia, uv, faseLunar, dias;

            if (lugar && lugar !== cachedWeatherData?.location) { // New location search
                try {
                    inputLugar.value = ''; // Clear search field

                    // Geocodificación
                    const respLugar = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(lugar + ' México')}`);
                    const dataLugar = await respLugar.json();

                    if (dataLugar.length === 0) {
                        document.getElementById('resultado').innerText = '❌ Lugar no encontrado.';
                        document.getElementById('pronostico-dias').innerHTML = '';
                        document.getElementById('chart-container').innerHTML = '<canvas id="chartTempLluvia"></canvas>';
                        document.getElementById('uv-luna').innerHTML = '';
                        document.getElementById('consejos-agricolas').innerHTML = '<p>Selecciona un cultivo para obtener consejos personalizados.</p>';
                        if(chart) chart.destroy();
                        cachedWeatherData = null; // Clear cache on not found
                        return;
                    }

                    lat = dataLugar[0].lat;
                    lon = dataLugar[0].lon;

                    map.setView([lat, lon], 10);
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lon]).addTo(map);

                    const apiKey = 'acc32174c125bd302406f8c726b2af85';

                    // Pronóstico 5 días
                    const respPronostico = await fetch(`https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric&lang=es`);
                    const dataPronostico = await respPronostico.json();

                    if (!dataPronostico.list || dataPronostico.list.length === 0) {
                        document.getElementById('resultado').innerText = 'No hay datos de pronóstico disponibles.';
                        return;
                    }

                    // Datos pronóstico más cercano
                    const pronosticoCercano = dataPronostico.list[0];
                    temp = pronosticoCercano.main.temp;
                    humedad = pronosticoCercano.main.humidity;
                    descripcion = pronosticoCercano.weather[0].description;
                    lluvia = pronosticoCercano.rain?.["3h"] ?? 0;

                    document.getElementById('resultado').innerHTML = `
                        📍 <strong>${lugar.toUpperCase()}</strong><br>
                        🌡️ Temperatura: ${temp.toFixed(1)}°C<br>
                        💧 Humedad: ${humedad}%<br>
                        🌦️ Pronóstico: ${descripcion}<br>
                        ☔ Lluvia pronosticada en próximas 3h: ${lluvia} mm
                    `;

                    // Pronóstico 5 días (cada 24h)
                    dias = [];
                    for(let i = 0; i < dataPronostico.list.length; i += 8){
                        dias.push(dataPronostico.list[i]);
                    }

                    const pronosticoDiasDiv = document.getElementById('pronostico-dias');
                    pronosticoDiasDiv.innerHTML = '';

                    const fechasGrafica = [];
                    const tempsGrafica = [];
                    const lluviasGrafica = [];

                    dias.forEach(dia => {
                        const fecha = new Date(dia.dt * 1000);
                        fechasGrafica.push(fecha.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' }));
                        tempsGrafica.push(dia.main.temp);
                        lluviasGrafica.push(dia.rain?.["3h"] ?? 0);

                        pronosticoDiasDiv.innerHTML += `
                            <div class="dia">
                                <strong>${fecha.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric' })}</strong><br>
                                <img class="icono-clima" src="https://openweathermap.org/img/wn/${dia.weather[0].icon}@2x.png" alt="Icono clima" /><br>
                                Temp: ${dia.main.temp.toFixed(1)}°C<br>
                                ${dia.rain?.["3h"] ? `Lluvia: ${dia.rain["3h"]} mm` : 'Sin lluvia'}
                            </div>
                        `;
                    });

                    // Graficar temperatura y lluvia
                    if(chart) chart.destroy();

                    const ctx = document.getElementById('chartTempLluvia').getContext('2d');
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: fechasGrafica,
                            datasets: [
                                {
                                    label: 'Temp (°C)',
                                    type: 'line',
                                    data: tempsGrafica,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    yAxisID: 'y1',
                                    tension: 0.3,
                                    fill: true,
                                },
                                {
                                    label: 'Lluvia (mm)',
                                    type: 'bar',
                                    data: lluviasGrafica,
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    yAxisID: 'y2',
                                }
                            ]
                        },
                        options: {
                            scales: {
                                y1: {
                                    type: 'linear',
                                    position: 'left',
                                    title: { display: true, text: 'Temperatura (°C)' },
                                    beginAtZero: true,
                                },
                                y2: {
                                    type: 'linear',
                                    position: 'right',
                                    title: { display: true, text: 'Lluvia (mm)' },
                                    beginAtZero: true,
                                    grid: { drawOnChartArea: false },
                                }
                            }
                        }
                    });

                    // UV index - clima actual
                    const respUV = await fetch(`https://api.openweathermap.org/data/2.5/uvi?appid=${apiKey}&lat=${lat}&lon=${lon}`);
                    const dataUV = await respUV.json();
                    uv = dataUV.value;

                    // Calcular fase lunar actual
                    faseLunar = calcularFaseLunar();

                    document.getElementById('uv-luna').innerHTML = `
                        ☀️ Índice UV actual: <strong>${uv.toFixed(1)}</strong><br>
                        🌙 Fase lunar: <strong>${faseLunar}</strong>
                    `;

                    // Cache the data
                    cachedWeatherData = {
                        location: lugar,
                        lat, lon, temp, humedad, descripcion, lluvia, uv, faseLunar, dias
                    };

                } catch (error) {
                    console.error(error);
                    document.getElementById('resultado').innerText = 'Error al obtener datos. Intenta de nuevo.';
                    document.getElementById('pronostico-dias').innerHTML = '';
                    document.getElementById('chart-container').innerHTML = '<canvas id="chartTempLluvia"></canvas>';
                    document.getElementById('uv-luna').innerHTML = '';
                    document.getElementById('consejos-agricolas').innerHTML = '<p>Selecciona un cultivo para obtener consejos personalizados.</p>';
                    if(chart) chart.destroy();
                    cachedWeatherData = null;
                    return;
                }
            } else if (cachedWeatherData) { // Use cached data if location hasn't changed
                    lat = cachedWeatherData.lat;
                    lon = cachedWeatherData.lon;
                    temp = cachedWeatherData.temp;
                    humedad = cachedWeatherData.humedad;
                    descripcion = cachedWeatherData.descripcion;
                    lluvia = cachedWeatherData.lluvia;
                    uv = cachedWeatherData.uv;
                    faseLunar = cachedWeatherData.faseLunar;
                    dias = cachedWeatherData.dias;
                    // Re-render current weather info as it might have been cleared by a previous error
                    document.getElementById('resultado').innerHTML = `
                        📍 <strong>${cachedWeatherData.location.toUpperCase()}</strong><br>
                        🌡️ Temperatura: ${temp.toFixed(1)}°C<br>
                        💧 Humedad: ${humedad}%<br>
                        🌦️ Pronóstico: ${descripcion}<br>
                        ☔ Lluvia pronosticada en próximas 3h: ${lluvia} mm
                    `;
                    document.getElementById('uv-luna').innerHTML = `
                        ☀️ Índice UV actual: <strong>${uv.toFixed(1)}</strong><br>
                        🌙 Fase lunar: <strong>${faseLunar}</strong>
                    `;
            } else { // No location entered and no cached data, and no crop selected, so no action
                return;
            }

            // Display agricultural advice only if a crop is selected and we have weather data
            if (cultivo && cachedWeatherData) {
                mostrarConsejosAgricolas(cultivo, dias, uv, faseLunar);
            } else if (!cultivo && cachedWeatherData) {
                document.getElementById('consejos-agricolas').innerHTML = '<p>Selecciona un cultivo para obtener consejos personalizados.</p>';
            }
        }

        function mostrarConsejosAgricolas(cultivo, diasPronostico, uv, faseLunar) {
            if (!cultivo) {
                document.getElementById('consejos-agricolas').innerHTML = '<p>Selecciona un cultivo para obtener consejos personalizados.</p>';
                return;
            }

            const infoCultivo = cultivosInfo[cultivo];
            if (!infoCultivo) {
                document.getElementById('consejos-agricolas').innerHTML = '<p>Información del cultivo no disponible.</p>';
                return;
            }

            let buenosDiasRiego = [];
            let buenosDiasFertilizacion = [];

            diasPronostico.forEach(dia => {
                const fecha = new Date(dia.dt * 1000);
                const temp = dia.main.temp;
                const lluvia = dia.rain?.["3h"] ?? 0;
                const uvDia = uv; // Using current UV for simplicity, ideally you'd have forecast UV

                // Condición para riego: poca lluvia y temp no muy fría
                if (lluvia < 1 && temp > 10) {
                    buenosDiasRiego.push(fecha.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' }));
                }

                // Condición para fertilización: temp dentro rango optimo y poco lluvia y UV aceptable
                if (
                    lluvia < 1 &&
                    temp >= infoCultivo.tempOptimaFert[0] &&
                    temp <= infoCultivo.tempOptimaFert[1] &&
                    uvDia < 8 &&
                    (faseLunar.includes('Llena') || faseLunar.includes('Gibosa') || faseLunar.includes('Cuarto Creciente'))
                ) {
                    buenosDiasFertilizacion.push(fecha.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' }));
                }
            });

            // Texto riego
            const textoRiego = buenosDiasRiego.length > 0
                ? `Puedes regar en los siguientes días: <strong>${buenosDiasRiego.join(', ')}</strong>.`
                : 'Actualmente no se recomiendan días para regar debido a las condiciones climáticas.';

            // Texto fertilización
            const textoFert = buenosDiasFertilizacion.length > 0
                ? `Los días ideales para fertilizar son: <strong>${buenosDiasFertilizacion.join(', ')}</strong>.`
                : 'Actualmente no se recomienda fertilizar debido a las condiciones climáticas o lunares.';

            // Consejos generales cultivo
            const consejoGeneral = infoCultivo.consejoGeneral;

            const textoCompleto = `
                <h3>Consejos para ${infoCultivo.nombre}</h3>
                <ul>
                    <li>${consejoGeneral}</li>
                    <li>${textoRiego}</li>
                    <li>${textoFert}</li>
                    <li>Fase lunar actual: <strong>${faseLunar}</strong></li>
                    <li>Índice UV actual: <strong>${uv.toFixed(1)}</strong></li>
                </ul>
            `;

            document.getElementById('consejos-agricolas').innerHTML = textoCompleto;
        }


        // FUNCIONES PARA EL CALENDARIO Y FASE DE CRECIMIENTO

        function actualizarCultivoCalendario() {
            const cultivoPrincipal = document.getElementById('cultivo').value;
            const cultivoSelectCalendario = document.getElementById('cultivo-select');
            
            // Si hay un cultivo seleccionado arriba, lo seleccionamos también en el calendario
            if (cultivoPrincipal) {
                cultivoSelectCalendario.value = cultivoPrincipal;
            } else {
                cultivoSelectCalendario.value = ""; // Limpiar si no hay selección
            }
        }

        const picker = new Pikaday({
            field: document.getElementById('fecha-siembra'),
            format: 'YYYY-MM-DD',
            // Permitimos seleccionar fechas desde hace un año hasta dentro de un año
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 1)),
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
            i18n: { /* Traducción de Pikaday */
                previousMonth : 'Mes anterior',
                nextMonth     : 'Mes siguiente',
                months        : ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                weekdays      : ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
                weekdaysShort : ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb']
            }
        });

        document.getElementById('btn-generar').addEventListener('click', () => {
            const cultivoSelect = document.getElementById('cultivo-select');
            const cultivo = cultivoSelect.value;
            const duracion = parseInt(cultivoSelect.selectedOptions[0].dataset.duracion, 10);
            const fechaSiembra = document.getElementById('fecha-siembra').value;
            const faseCrecimiento = document.getElementById('fase-crecimiento').value; // Nuevo selector

            if (!cultivo) {
                alert('Selecciona un cultivo para el calendario.');
                return;
            }
            if (!fechaSiembra) {
                alert('Selecciona la fecha de siembra.');
                return;
            }
            if (!faseCrecimiento) {
                alert('Selecciona la fase de crecimiento de la planta.');
                return;
            }

            generarCalendario(cultivo, duracion, fechaSiembra, faseCrecimiento);
            guardarTodo();
        });

        function generarCalendario(cultivo, duracion, fechaSiembra, faseCrecimiento) {
            const calendarioDiv = document.getElementById('calendario');
            const mensajesDiv = document.getElementById('mensajes-calendario');
            calendarioDiv.innerHTML = '';
            mensajesDiv.innerHTML = '';

            const fechaInicio = new Date(fechaSiembra);
            const fechaFin = new Date(fechaInicio);
            fechaFin.setDate(fechaFin.getDate() + duracion);

            let diasCultivo = [];
            for(let d = new Date(fechaInicio); d <= fechaFin; d.setDate(d.getDate() + 1)) {
                diasCultivo.push(new Date(d));
            }

            const diasSemanaEsp = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']; // Días de la semana en español para la tabla
            let htmlCalendario = '<table style="width:100%; border-collapse: collapse;"><thead><tr>';
            diasSemanaEsp.forEach(dia => {
                htmlCalendario += `<th>${dia}</th>`;
            });
            htmlCalendario += '</tr></thead><tbody><tr>';


            // Ajustar para que lunes sea el primer día de la semana (en JS domingo es 0)
            const primerDiaSemana = fechaInicio.getDay(); // Mantenemos el domingo como 0, y luego ajustamos el for

            for (let i=0; i < primerDiaSemana; i++) {
                htmlCalendario += '<td></td>';
            }

            const infoCultivo = cultivosInfo[cultivo];
            const intervaloRiego = infoCultivo.riegos[faseCrecimiento];
            const intervaloFertilizacion = infoCultivo.fertilizaciones[faseCrecimiento];

            diasCultivo.forEach((fecha, i) => {
                const dia = fecha.getDate();
                let estiloCelda = ''; // Para los estilos CSS
                let actividades = []; // Para almacenar todas las actividades del día

                // Riego
                if (intervaloRiego && (i % intervaloRiego === 0)) { // Si hay un intervalo y es día de riego
                     actividades.push('💧');
                }

                // Fertilización
                if (intervaloFertilizacion && (i % intervaloFertilizacion === 0)) { // Si hay un intervalo y es día de fertilización
                    actividades.push('🌿');
                }
                
                // Cosecha (solo el último día del ciclo)
                if (i === diasCultivo.length - 1) {
                    actividades.push('🌾');
                }

                // Determinar el color y estilo de la celda según las actividades
                if (actividades.includes('💧') && actividades.includes('🌿')) {
                    estiloCelda = 'color:purple; font-weight:bold;'; // Combinación de colores si hay ambas
                } else if (actividades.includes('💧')) {
                    estiloCelda = 'color:blue; font-weight:bold;';
                } else if (actividades.includes('🌿')) {
                    estiloCelda = 'color:green; font-weight:bold;';
                } else if (actividades.includes('🌾')) { // Cosecha sin otros eventos
                    estiloCelda = 'color:red; font-weight:bold;';
                }

                const marcadoresHtml = actividades.join(' '); // Unir los marcadores

                htmlCalendario += `<td style="padding:5px; border: 1px solid #ccc; text-align:center; ${estiloCelda}" title="${actividades.map(a => a === '💧' ? 'Riego' : a === '🌿' ? 'Fertilización' : 'Cosecha').join(', ')}">${dia} ${marcadoresHtml}</td>`;

                if ((primerDiaSemana + i + 1) % 7 === 0) htmlCalendario += '</tr><tr>';
            });

            htmlCalendario += '</tr></tbody></table>';

            calendarioDiv.innerHTML = htmlCalendario;

            // New date formatting logic
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            function formatearFecha(fechaStr) {
                const fecha = new Date(fechaStr);
                // Ajustar para el desfase horario si es necesario, usando UTC
                const fechaUTC = new Date(fecha.getTime() + fecha.getTimezoneOffset() * 60000);
                return `${fechaUTC.getDate()} de ${meses[fechaUTC.getMonth()]} de ${fechaUTC.getFullYear()}`;
            }

            const fechaCosechaFormateada = formatearFecha(fechaFin.toISOString().split('T')[0]); // Formatear fecha de fin

            let mensaje = `<strong>${infoCultivo.nombre.charAt(0).toUpperCase() + infoCultivo.nombre.slice(1)}</strong> cultivado desde <strong>${formatearFecha(fechaSiembra)}</strong> hasta el <strong>${fechaCosechaFormateada}</strong> (durante <strong>${duracion}</strong> días).<br><br>`;

            if (intervaloRiego) {
                mensaje += `💧 <em>Días recomendados para riego</em>: cada ${intervaloRiego} día(s).<br>`;
            } else {
                mensaje += `💧 <em>Información de riego no disponible para esta fase.</em><br>`;
            }

            if (intervaloFertilizacion) {
                mensaje += `🌿 <em>Días recomendados para fertilización</em>: cada ${intervaloFertilizacion} día(s).<br>`;
            } else {
                mensaje += `🌿 <em>Información de fertilización no disponible para esta fase.</em><br>`;
            }
            
            mensaje += '🌾 <em>Día aproximado de cosecha</em>: último día del ciclo.<br><br>';

            mensaje += '<em>Nota:</em> Ajusta el riego y fertilización según condiciones climáticas y del suelo.';

            mensajesDiv.innerHTML = mensaje;
        } 


       /*==============================================================================*/

   async function guardarTodo() {
    if (!cachedWeatherData) {
        alert("Primero busca el clima");
        return;
    }

    const cultivo = document.getElementById('cultivo-select').value;
    const fechaSiembra = document.getElementById('fecha-siembra').value;
    const fase = document.getElementById('fase-crecimiento').value;

    // 🔥 FECHA BASE REAL
    const fechaBase = new Date(fechaSiembra);
    
    /*===================Fechas de siembra==============================*/
    // 🔥 CALCULAR COSECHA
    const duracion = parseInt(
        document.getElementById('cultivo-select')
        .selectedOptions[0]
        .dataset.duracion
    );

    const fechaCosecha = new Date(fechaBase);
    fechaCosecha.setDate(fechaBase.getDate() + duracion);

    const fechaCosechaFormateada = fechaCosecha.toISOString().split('T')[0];

    /*===================================================================*/
    const eventos = [];

    document.querySelectorAll('#calendario td').forEach((td, index) => {

        // Ignorar celdas vacías
        if (td.innerText.trim() === "") return;

        // Crear fecha REAL (no solo el número del día)
        const fechaReal = new Date(fechaBase);
        fechaReal.setDate(fechaBase.getDate() + index);

        const fechaFormateada = fechaReal.toISOString().split('T')[0];

        if (td.title.includes('Riego')) {
            eventos.push({
                fecha: fechaFormateada,
                tipo: 'riego',
                comentario: ''
            });
        }

        if (td.title.includes('Fertilización')) {
            eventos.push({
                fecha: fechaFormateada,
                tipo: 'fertilizacion',
                comentario: ''
            });
        }
    });

    const data = {
        municipio: cachedWeatherData.location,
        lat: cachedWeatherData.lat,
        lon: cachedWeatherData.lon,
        cultivo: cultivo,
        fecha: new Date().toISOString(),
        temp: cachedWeatherData.temp,
        humedad: cachedWeatherData.humedad,
        lluvia: cachedWeatherData.lluvia,
        descripcion: cachedWeatherData.descripcion,
        uv: cachedWeatherData.uv,
        fase_lunar: cachedWeatherData.faseLunar,
        /*===========Nuevas fechas de siembra y cosecha===============*/
        fecha_siembra: fechaSiembra,
        fecha_cosecha: fechaCosechaFormateada,
        /*=============================================================*/
        eventos: eventos // 🔥 YA NO stringify aquí
    };

    await fetch('guardar_todo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.text())
    .then(msg => alert(msg))
    .catch(err => console.error(err));
}

    </script>

<script>
window.onload = function() {
    // 1. Analizamos la URL para buscar parámetros
    const urlParams = new URLSearchParams(window.location.search);
    const muniData = urlParams.get('municipio');
    const cultData = urlParams.get('cultivo');

    // 2. Si existen datos en la URL, los ponemos en los inputs
    if (muniData) {
        document.getElementById('lugar').value = muniData;
    }
    
    if (cultData) {
        document.getElementById('cultivo').value = cultData;
    }

    // 3. ¡IMPORTANTE! Disparamos la búsqueda automáticamente
    if (muniData && cultData) {
        // Llamamos a tus funciones existentes para que cargue el mapa y clima de una vez
        buscarClima(); 
        actualizarCultivoCalendario();
    }
};
</script>

</body>
</html>