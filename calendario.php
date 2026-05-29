<?php
$mysqli = new mysqli("localhost", "root", "", "sistema_agricola");
$res_cultivos = $mysqli->query("SELECT id, nombre FROM cultivos"); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Calendario Agrícola Pro - Sistema Experto</title>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #0b0e14;
        color: #e0e0e0;
        padding: 20px;
    }

    h2 { text-align: center; color: #4da6ff; text-shadow: 0 0 10px rgba(77,166,255,0.3); }

    .selector-container {
        text-align: center;
        margin-bottom: 30px;
    }

    select {
        padding: 12px;
        background: #1a1f26;
        color: white;
        border: 1px solid #4da6ff;
        border-radius: 8px;
        cursor: pointer;
    }

    .mes {
        margin-bottom: 40px;
        background: #1a1f26;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: auto;
    }

    th, td {
        border: 1px solid #333;
        text-align: center;
        height: 70px;
        vertical-align: top;
        position: relative;
    }

    th { background: #252b33; color: #4da6ff; padding: 10px; }

    /* ESTILOS DE EVENTOS */
    .siembra { background: #e63946 !important; color: white; }
    .riego { background: #0077b6 !important; color: white; }
    .fertilizacion { background: #2b9348 !important; color: white; }
    .cosecha { background: #f4a261 !important; color: black; }
    .fumigacion { background: #9d4edd !important; color: white; }
    .deshierbe { background: #40916c !important; color: white; }
    .poda { background: #bc6c25 !important; color: white; }

    .fuera-rango {
        background: #0f131a;
        color: #444;
        opacity: 0.3;
        border: 1px dashed #222;
    }

    .dia-numero { font-weight: bold; display: block; margin-top: 5px; }
    .emoji-ref { font-size: 1.4rem; display: block; margin-top: 5px; }

    /* MENÚ CONTEXTUAL */
    .menu-evento {
        position: absolute;
        background: #1a1f26;
        border: 2px solid #4da6ff;
        border-radius: 10px;
        padding: 5px;
        display: none;
        z-index: 999;
        box-shadow: 0 0 20px rgba(0,0,0,0.8);
    }

    .menu-evento div {
        padding: 10px 20px;
        cursor: pointer;
        transition: 0.2s;
    }

    .menu-evento div:hover {
        background: #4da6ff;
        color: black;
        border-radius: 5px;
    }

    /* TOOLTIP */
    .tooltip-custom {
        position: absolute;
        background: #1a1f26;
        color: #4da6ff;
        border: 1px solid #4da6ff;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        pointer-events: none;
        display: none;
        z-index: 1000;
        box-shadow: 0 0 10px rgba(77,166,255,0.4);
    }

      .footer-bottom {
        width: 100%;
        padding: 25px 0;
        text-align: center;
        background: #0b0e14; /* Mismo fondo que el body */
        color: #4a5568; /* Gris oscuro para que no distraiga */
        font-size: 0.75rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        border-top: 1px solid rgba(77, 166, 255, 0.1); /* Línea sutil azul */
        margin-top: 50px;
        font-weight: 600;
    }

</style>
</head>
<?php include 'barra_navegacion.php'; ?>

<body>

<h2>📅 Calendario Agrícola Inteligente</h2>

<div class="selector-container">
    <select id="filtroCultivo" onchange="cargarCalendarios()">
        <option value="todos">-- Ver todos los cultivos --</option>
        <?php while($c = $res_cultivos->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>">
                <?php echo strtoupper($c['nombre']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div id="calendarios"></div>

<div id="menuEvento" class="menu-evento">
    <div onclick="seleccionarEvento('riego')">💧 Riego</div>
    <div onclick="seleccionarEvento('fertilizacion')">🌱 Fertilización</div>
    <div onclick="seleccionarEvento('fumigacion')">🧴 Fumigación</div>
    <div onclick="seleccionarEvento('deshierbe')">🌿 Deshierbe</div>
    <div onclick="seleccionarEvento('poda')">✂️ Poda</div>
</div>

<div id="tooltip" class="tooltip-custom"></div>

<script>
let fechaSeleccionada = null;
let coordsPorFecha = {};
let ubicacionBaseCultivo = null;
const tooltip = document.getElementById('tooltip');


// ================= TOOLTIP =================
function mostrarTooltip(e, texto){
    if(!texto) return;
    tooltip.style.display = 'block';
    tooltip.innerText = texto;
    moverTooltip(e);
}

function moverTooltip(e){
    tooltip.style.left = (e.pageX + 15) + 'px';
    tooltip.style.top = (e.pageY + 15) + 'px';
}

function ocultarTooltip(){
    tooltip.style.display = 'none';
}


// ================= CLIMA =================
async function obtenerClima(lat, lon){
    if(!lat || !lon) return null;

    const apiKey = "acc32174c125bd302406f8c726b2af85";

    try {
        const res = await fetch(
            `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric&lang=es`
        );
        const data = await res.json();
        const clima = data.list[0];

        const tieneLluvia = (clima.rain || (clima.weather && clima.weather[0].main === 'Rain'));

        return {
            lluvia: tieneLluvia ? true : false,
            viento: clima.wind.speed,
            temp: clima.main.temp
        };
    } catch(e){
        console.error("Error API Clima:", e);
        return null;
    }
}


// ================= CALENDARIOS =================
async function cargarCalendarios() {

    const res = await fetch('obtener_eventos.php');
    const data = await res.json();
    if(!data.ok) return;

    const contenedor = document.getElementById('calendarios');
    contenedor.innerHTML = '';

    const filtro = document.getElementById('filtroCultivo').value;
    let eventos = data.eventos;

    if(filtro !== 'todos'){
        eventos = eventos.filter(e => e.cultivo_id == filtro);
    }

    const cultivos = {};

   eventos.forEach(ev => {

    const key = ev.cultivo_id + '_' + ev.municipio;

    if(!cultivos[key]){
        cultivos[key] = {
            id: ev.cultivo_id,
            municipio: ev.municipio,
            lat: ev.lat,
            lon: ev.lon,
            eventos: []
        };
    }

    cultivos[key].eventos.push(ev);
});


    Object.values(cultivos).forEach(cultivo => {

        coordsPorFecha = {};
        ubicacionBaseCultivo = null;

        const registroBase = cultivo.eventos.find(e => e.lat && e.lon);
        if(registroBase){
            ubicacionBaseCultivo = {
                lat: registroBase.lat,
                lon: registroBase.lon,
                municipio: registroBase.municipio
            };
        }

        // 🔥 RANGO REAL (SIEMBRA / COSECHA)
        const siembras = cultivo.eventos.filter(e => e.tipo === 'siembra');
        const cosechas = cultivo.eventos.filter(e => e.tipo === 'cosecha');

        const minStr = siembras.length
            ? new Date(Math.min(...siembras.map(e => new Date(e.fecha)))).toISOString().split('T')[0]
            : '0000-00-00';

        const maxStr = cosechas.length
            ? new Date(Math.max(...cosechas.map(e => new Date(e.fecha)))).toISOString().split('T')[0]
            : '9999-12-31';


        const eventosPorMes = {};

        cultivo.eventos.forEach(ev => {

            coordsPorFecha[ev.fecha] = {
                lat: ev.lat,
                lon: ev.lon,
                municipio: ev.municipio
            };

            const f = new Date(ev.fecha);
            const key = f.getFullYear() + '-' + String(f.getMonth()+1).padStart(2,'0');

            if(!eventosPorMes[key]) eventosPorMes[key] = [];
            eventosPorMes[key].push(ev);
        });


        let htmlCultivo = `
        <div class="bloque-cultivo">
            <h2>🌱 Parcela ${cultivo.id} - ${cultivo.municipio}</h2>
        `;


        Object.keys(eventosPorMes).sort().forEach(mesKey => {

            const [y,m] = mesKey.split('-').map(Number);

            const primer = new Date(y, m-1, 1);
            const ultimo = new Date(y, m, 0);

            let html = `
            <div class="mes">
                <h3>${primer.toLocaleString('es-MX',{month:'long',year:'numeric'}).toUpperCase()}</h3>
                <table>
                    <tr>
                        <th>Lun</th><th>Mar</th><th>Mié</th>
                        <th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
                    </tr><tr>
            `;

            let start = primer.getDay();
            start = start === 0 ? 6 : start - 1;

            for(let i=0;i<start;i++) html += `<td></td>`;


            for(let d=1; d<=ultimo.getDate(); d++){

                const fecha = `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;

                let clase = '', emoji = '', texto = '';

                if(fecha >= minStr && fecha <= maxStr){

                    eventosPorMes[mesKey].forEach(ev => {

                        if(ev.fecha === fecha){

                            const tipos = {
                                'riego': {c:'riego', e:'💧', t:'Riego'},
                                'fertilizacion': {c:'fertilizacion', e:'🌱', t:'Fertilización'},
                                'fertilización': {c:'fertilizacion', e:'🌱', t:'Fertilización'},
                                'fumigacion': {c:'fumigacion', e:'🧴', t:'Fumigación'},
                                'deshierbe': {c:'deshierbe', e:'🌿', t:'Deshierbe'},
                                'poda': {c:'poda', e:'✂️', t:'Poda'},
                                'cosecha': {c:'cosecha', e:'🚜', t:'Cosecha'},
                                'siembra': {c:'siembra', e:'🪵', t:'Siembra'}
                            };

                            if(tipos[ev.tipo]){
                                clase = tipos[ev.tipo].c;
                                emoji = tipos[ev.tipo].e;
                                texto = tipos[ev.tipo].t;
                            }
                        }
                    });

                } else {
                    clase = 'fuera-rango';
                }

                html += `
                <td class="${clase}"
                    onclick="editarDia('${fecha}', event)"
                    onmouseover="mostrarTooltip(event, '${texto}')"
                    onmousemove="moverTooltip(event)"
                    onmouseout="ocultarTooltip()">
                    <span>${d}</span>
                    <span>${emoji}</span>
                </td>
                `;

                if((d+start)%7===0) html += "</tr><tr>";
            }

            html += `</tr></table></div>`;
            htmlCultivo += html;
        });

        htmlCultivo += `</div>`;
        contenedor.innerHTML += htmlCultivo;
    });
}


// ================= EDITAR DÍA =================
function editarDia(fecha, event){

    event.stopPropagation();

    const cultivoId = document.getElementById('filtroCultivo').value;

    if(cultivoId === 'todos'){
        alert("Selecciona un cultivo específico");
        return;
    }

    fechaSeleccionada = fecha;

    const menu = document.getElementById('menuEvento');
    menu.style.display = 'block';
    menu.style.left = (event.pageX - 40) + 'px';
    menu.style.top = (event.pageY - 40) + 'px';
}


// ================= GUARDAR EVENTO =================
async function seleccionarEvento(tipo){

    const cultivoId = document.getElementById('filtroCultivo').value;

    let info = coordsPorFecha[fechaSeleccionada] || ubicacionBaseCultivo;

    const clima = await obtenerClima(info?.lat, info?.lon);

    let recomendacion = "Sin datos climáticos";

    const muni = info?.municipio || "Desconocido";

    if(clima){

        const res = await fetch("experto.php", {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({
                tipo,
                lluvia: clima.lluvia,
                viento: clima.viento,
                temp: clima.temp,
                municipio: muni
            })
        });

        const data = await res.json();
        recomendacion = data.recomendacion;
    }

    const mensaje =
`📍 MUNICIPIO: ${muni}
🌦️ CLIMA:
Temp: ${clima?.temp ?? 'N/A'}°C | Viento: ${clima?.viento ?? 'N/A'} m/s

🧠 RECOMENDACIÓN:
${recomendacion}

¿Deseas continuar?`;

    if(!confirm(mensaje)) return;

    fetch('editar_evento.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({
            fecha: fechaSeleccionada,
            tipo,
            cultivo_id: cultivoId
        })
    }).then(() => {
        document.getElementById('menuEvento').style.display = 'none';
        cargarCalendarios();
    });
}


// ================= CERRAR MENÚ =================
document.addEventListener('click', e => {
    const menu = document.getElementById('menuEvento');
    if(!menu.contains(e.target)) menu.style.display = 'none';
});


cargarCalendarios();
</script>


<footer class="footer-bottom">
        &copy; 2026 SISTEMA AGRICOLA DEMÉTER - TECNOLOGÍA AGRÍCOLA SUSTENTABLE
    </footer>

</body>
</html>