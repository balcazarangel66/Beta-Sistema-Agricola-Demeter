<?php
session_start();

// --- LÓGICA DE DETECCIÓN DINÁMICA ---
$nombre_display = "Invitado";
$foto_display = ""; 
$email_display = "";
$es_invitado = true;
$inicial = "?";

if (isset($_SESSION['id_usuario'])) {
    $nombre_display = $_SESSION['nombre_usuario'];
    $foto_display = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : "";
    $email_display = $_SESSION['email'];
    $es_invitado = false;
    // Extraemos la primera letra para el avatar tipo Google
    $inicial = strtoupper(substr($nombre_display, 0, 1));
}

// Listado de municipios
$municipios = [
    "Abasolo", "Acámbaro", "Apaseo el Alto", "Apaseo el Grande", "Atarjea", "Celaya", "Comonfort", 
    "Coroneo", "Cortazar", "Cuerámaro", "Doctor Mora", "Dolores Hidalgo", "Guanajuato", "Huanímaro", 
    "Irapuato", "Jaral del Progreso", "Jerécuaro", "León", "Manuel Doblado", "Moroleón", 
    "Ocampo", "Pénjamo", "Purísima del Rincón", "Romita", "Salamanca", "Salvatierra", 
    "San Diego de la Unión", "San Felipe", "San Francisco del Rincón", "San José Iturbide", 
    "San Luis de la Paz", "Santa Catarina", "Santa Cruz de Juventino Rosas", "Santiago Maravatío", 
    "Silao", "Tarandacuao", "Tarimoro", "Tierra Blanca", "Uriangato", "Valle de Santiago", 
    "Victoria", "Villagrán", "Xichú", "Yuriria"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deméter | Inteligencia Agrícola</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0b0e14;
            color: white;
            overflow-x: hidden;
        }

        /* --- NAVBAR --- */
        nav {
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(11, 14, 20, 0.95);
            position: fixed;
            top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(77, 166, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .nav-links { display: flex; gap: 30px; }
        .nav-links a { color: #a0aec0; text-decoration: none; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .nav-links a:hover { color: #4da6ff; }

        .user-profile { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            cursor: pointer; 
            padding: 5px 15px; 
            border-radius: 30px;
            transition: 0.3s;
        }
        .user-profile:hover { background: rgba(77, 166, 255, 0.1); }

        .user-avatar-container { 
            width: 38px; height: 38px; 
            border-radius: 50%; 
            border: 2px solid #00ff00;
            display: flex; align-items: center; justify-content: center;
            background: #1a1f26; color: #00ff00; font-weight: bold;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
            overflow: hidden;
        }
        .user-avatar-container img { width: 100%; height: 100%; object-fit: cover; }

        /* --- MODALES --- */
        .modal-perfil {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 2000;
            justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: #1a1f26;
            padding: 40px;
            border-radius: 25px;
            border: 1px solid #4da6ff;
            width: 90%; max-width: 400px;
            text-align: center;
            box-shadow: 0 0 30px rgba(77, 166, 255, 0.2);
        }

        .modal-content h3 { color: #4da6ff; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }
        
        /* AVATAR INTERACTIVO EN MODAL */
        .profile-main-avatar {
            width: 110px; height: 110px;
            border-radius: 50%; border: 3px solid #00ff00;
            margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;
            background: #0b0e14; color: #00ff00; font-size: 3rem; font-weight: 900;
            overflow: hidden; cursor: pointer; position: relative;
            transition: 0.3s;
        }
        
        .profile-main-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.4);
        }

        /* Overlay "Editar" al pasar el mouse */
        .profile-main-avatar:hover::after {
            content: "EDITAR";
            position: absolute;
            bottom: 0; width: 100%; background: rgba(0,0,0,0.6);
            color: white; font-size: 0.6rem; padding: 5px 0; font-weight: bold;
        }

        .profile-main-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .modal-content input {
            width: 100%; padding: 12px; margin: 8px 0;
            background: #0b0e14; border: 1px solid #2d3748;
            color: white; border-radius: 10px; outline: none;
        }

        .btn-save {
            width: 100%; padding: 12px; background: #00ff00; color: #0b0e14;
            border: none; border-radius: 10px; font-weight: 900;
            cursor: pointer; margin-top: 15px; transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-save:hover { transform: scale(1.02); box-shadow: 0 0 15px rgba(0,255,0,0.4); }

        .btn-logout {
            width: 100%; padding: 10px; background: transparent; color: #ff4d4d;
            border: 1px solid #ff4d4d; border-radius: 10px; font-weight: bold;
            cursor: pointer; margin-top: 10px; transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(255, 77, 77, 0.1); }

        /* --- DISEÑO HERO Y BUSCADOR --- */
        .hero {
            height: 90vh;
            background: linear-gradient(rgba(11, 14, 20, 0.7), rgba(11, 14, 20, 0.7)), 
                        url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?auto=format&fit=crop&q=80&w=2070'); 
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 100px 20px 0;
        }

        .hero h1 { font-size: clamp(2rem, 5vw, 3.5rem); letter-spacing: 10px; margin-bottom: 10px; text-transform: uppercase; font-weight: 900; }
        .hero p { color: #4da6ff; font-size: 1.2rem; margin-bottom: 40px; letter-spacing: 2px; }

        .search-box {
            background: rgba(26, 31, 38, 0.85); padding: 30px; border-radius: 20px;
            backdrop-filter: blur(10px); border: 1px solid rgba(77, 166, 255, 0.3);
            width: 100%; max-width: 800px; display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .input-group { flex: 1; min-width: 200px; text-align: left; position: relative; }
        .input-group label { display: block; font-size: 0.7rem; color: #4da6ff; margin-bottom: 5px; font-weight: bold; }
        .input-group input, .input-group select { 
            width: 100%; padding: 12px; background: #0b0e14; border: 1px solid #2d3748; 
            border-radius: 8px; color: white; outline: none; transition: 0.3s;
        }

        .custom-dropdown {
            position: absolute;
            top: 105%; left: 0; width: 100%;
            max-height: 180px; overflow-y: auto;
            background: #1a1f26; border: 1px solid #4da6ff;
            border-radius: 10px; display: none; z-index: 1500;
        }
        .dropdown-item {
            padding: 12px; cursor: pointer; color: #a0aec0; transition: 0.2s; font-size: 0.85rem;
        }
        .dropdown-item:hover { background: rgba(0,255,0,0.1); color: #00ff00; }

        .btn-search {
            padding: 12px 35px; background: #4da6ff; border: none; border-radius: 8px;
            color: #0b0e14; font-weight: 900; cursor: pointer; height: 45px;
            transition: 0.3s; text-transform: uppercase;
        }
        .btn-search:hover { background: #00ff00; transform: translateY(-3px); }

        /* --- SECCIÓN DE MUNICIPIOS --- */
        .region-section { background: #0b0e14; padding: 100px 50px; display: flex; flex-wrap: wrap; gap: 50px; align-items: center; justify-content: center; }
        .region-info { flex: 1; min-width: 300px; max-width: 500px; background: rgba(26, 31, 38, 0.5); padding: 40px; border-radius: 20px; border-left: 4px solid #00ff00; }
        .map-container { flex: 1; min-width: 350px; height: 450px; border-radius: 25px; overflow: hidden; border: 1px solid rgba(77, 166, 255, 0.3); }
        .map-container iframe { width: 100%; height: 100%; border: 0; filter: invert(90%) hue-rotate(180deg) brightness(0.8); }

        .municipios-list {
            margin-top: 25px; display: flex; flex-wrap: wrap; justify-content: center; 
            max-height: 200px; overflow-y: auto; padding: 10px; 
        }
        .tag-municipio { 
            display: inline-block; padding: 6px 12px; background: rgba(77, 166, 255, 0.05); 
            color: #4da6ff; border-radius: 5px; font-size: 0.7rem; margin: 4px; 
            border: 1px solid rgba(77, 166, 255, 0.2); transition: 0.3s; cursor: pointer;
        }
        .tag-municipio:hover { background: rgba(0, 255, 0, 0.1); border-color: #00ff00; color: #00ff00; }

        .guest-banner {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
            background: rgba(77, 166, 255, 0.15); backdrop-filter: blur(10px);
            border: 1px solid #4da6ff; padding: 10px 25px; border-radius: 50px; z-index: 999;
            font-size: 0.8rem; display: flex; gap: 15px; align-items: center;
        }

        /* --- FOOTER PERREÓN --- */
        footer {
            background: linear-gradient(to top, #05070a, #0b0e14);
            padding: 60px 50px 20px;
            border-top: 1px solid rgba(77, 166, 255, 0.1);
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section h4 {
            color: #00ff00;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .footer-section p, .footer-section a {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.85rem;
            line-height: 1.8;
            transition: 0.3s;
        }

        .footer-section a:hover {
            color: #4da6ff;
            padding-left: 5px;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #4a5568;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .icon-circle {
            width: 35px;
            height: 35px;
            background: rgba(77, 166, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4da6ff;
            border: 1px solid rgba(77, 166, 255, 0.2);
            transition: 0.3s;
        }

        .icon-circle:hover {
            background: #00ff00;
            color: #0b0e14;
            transform: translateY(-3px);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.3);
        }

        /* 🔔 CAMPANITA */
.notification-container {
    position: relative;
    margin-right: 20px;
    cursor: pointer;
}

.bell {
    font-size: 1.3rem;
    color: #a0aec0;
    transition: 0.3s;
}

.bell:hover {
    color: #00ff00;
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: #ff4d4d;
    color: white;
    font-size: 0.6rem;
    padding: 3px 6px;
    border-radius: 50%;
    font-weight: bold;
}

.notification-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 35px;
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
    background: #1a1f26;
    border-radius: 15px;
    border: 1px solid rgba(77,166,255,0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.6);
    padding: 10px;
    z-index: 2000;
}


    </style>
</head>
<body>

    <nav>
        <div style="font-weight: 900; letter-spacing: 3px; font-size: 1.2rem;">DEMÉTER</div>
        <div class="nav-links">
           <div class="nav-links">
            <a href="presentacion.php">Inicio</a>
            <a href="asistente_agricola.php">Asistente Agricola</a>
            <a href="calendario.php">Calendarios</a> 
</div>
        </div>
       
            <div style="display:flex; align-items:center; gap:15px;">

            <?php if (!$es_invitado): ?>
                <div class="notification-container" onclick="toggleNotificaciones(event)">
                    <div class="bell">🔔</div>
                    <div id="notifBadge" class="notification-badge" style="display:none;">0</div>

                    <div id="notifDropdown" class="notification-dropdown">
                        <div id="notifContent">Cargando alertas...</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PERFIL -->
            <div class="user-profile" onclick="gestionarPerfil()">
                <span style="font-size: 0.8rem;">
                    HOLA, <strong><?php echo strtoupper($nombre_display); ?></strong>
                </span>
                <div class="user-avatar-container">
                    <?php if ($foto_display && $foto_display != 'default.png'): ?>
                        <img src="Recursos/fotos_perfil/<?php echo $foto_display; ?>">
                    <?php else: ?>
                        <?php echo $inicial; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </nav>

    <div id="modalPerfil" class="modal-perfil">
        <div class="modal-content">
            <h3>Mi Perfil</h3>
            
            <form action="actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                <label for="inputFoto" class="profile-main-avatar" id="avatar-preview-container">
    <?php if ($foto_display && $foto_display != 'default.png'): ?>
        <img src="Recursos/fotos_perfil/<?php echo $foto_display; ?>" id="img-preview">
    <?php else: ?>
        <span id="text-preview"><?php echo $inicial; ?></span>
    <?php endif; ?>
</label>

<input 
    type="file" 
    id="inputFoto"
    name="foto" 
    accept="image/png, image/jpeg" 
    style="display:none;" 
    onchange="previewImage(this)">

                <input type="text" name="nuevo_nombre" value="<?php echo $nombre_display; ?>" required>
                <input type="email" name="nuevo_email" value="<?php echo $email_display; ?>" required>
                
                <button type="submit" class="btn-save">GUARDAR CAMBIOS</button>
            </form>

            <button class="btn-logout" onclick="window.location.href='cierre_sesion.php'">CERRAR SESIÓN</button>
            <button type="button" onclick="cerrarModal('modalPerfil')" style="background:transparent; color:gray; border:none; margin-top:15px; cursor:pointer;">Cancelar</button>
        </div>
    </div>

    <div id="modalInvitado" class="modal-perfil">
        <div class="modal-content" style="border-color: #00ff00;">
            <h3 style="color: #00ff00;">🔒 Función Exclusiva</h3>
            <p style="color: #a0aec0; margin-bottom: 25px;">Esta función requiere una cuenta activa.</p>
            <button onclick="window.location.href='login.php'" class="btn-save">INICIAR SESIÓN</button>
            <button onclick="cerrarModal('modalInvitado')" style="background:transparent; color:gray; border:none; margin-top:15px; cursor:pointer; font-size: 0.8rem;">Continuar explorando</button>
        </div>
    </div>

    <header class="hero">
        <h1>SISTEMA AGRÍCOLA DEMÉTER</h1>
        <p>El placer de cultivar con inteligencia</p>
        <div class="search-box">
            <div class="input-group">
                <label>MUNICIPIO</label>
                <input type="text" id="lugar_p" placeholder="Escribe o elige un municipio" 
                        autocomplete="off" onclick="toggleDropdown()" onkeyup="filtrarMunicipios()">
                
                <div id="dropdown_municipios" class="custom-dropdown">
                    <?php foreach ($municipios as $muni): ?>
                        <div class="dropdown-item" onclick="seleccionarDesdeDropdown('<?php echo $muni; ?>')">
                            <?php echo $muni; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="input-group">
                <label>CULTIVO</label>
                <select id="cultivo_p">
                    <option value="">-- Elige --</option>
                    <option value="maiz">Maíz</option>
                    <option value="aguacate">Aguacate</option>
                    <option value="fresa">Fresa</option>
                    <option value="fresa">Tomate</option>
                </select>
            </div>
            <button class="btn-search" onclick="enviarAlSistema()">
    MONITOREAR
</button>

<script>
function enviarAlSistema() {
    // 1. Obtenemos los valores de los inputs de precentasion.php
    const muni = document.getElementById('lugar_p').value;
    const cult = document.getElementById('cultivo_p').value;

    // 2. Validamos que no vayan vacíos (opcional pero recomendado)
    if(!muni || !cult) {
        alert("Por favor selecciona municipio y cultivo");
        return;
    }

    // 3. Redirigimos pasando los datos por la URL
    window.location.href = `Sistema_Agricola.php?municipio=${encodeURIComponent(muni)}&cultivo=${encodeURIComponent(cult)}`;
}
</script>

        </div>
    </header>

    <section class="region-section">
        <div class="region-info">
            <h2>Cobertura Regional</h2>
            <p>Datos climáticos del <strong style="color:#00ff00">Estado de Guanajuato</strong>.</p>
            <div class="municipios-list">
                <?php foreach ($municipios as $muni): ?>
                    <span class="tag-municipio" onclick="seleccionarMunicipio('<?php echo $muni; ?>')"><?php echo $muni; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="map-container">
    <iframe 
        id="mapa_guanajuato"
        src="https://www.google.com/maps?q=Estado+de+Guanajuato&output=embed&z=8&t=m" 
        allowfullscreen="" 
        loading="lazy">
    </iframe>
</div>
    </section>

    <?php if ($es_invitado): ?>
        <div class="guest-banner">
            <span>🌱</span>
            <a href="registro.php" style="color: #00ff00; font-weight: bold; text-decoration: none;">REGÍSTRATE AQUÍ</a>
            <span>🌱</span>
        </div>
    <?php endif; ?>

    <script>
        function toggleDropdown() {
            document.getElementById('dropdown_municipios').style.display = 'block';
        }

        function seleccionarDesdeDropdown(valor) {
            document.getElementById('lugar_p').value = valor;
            document.getElementById('dropdown_municipios').style.display = 'none';
        }

        function filtrarMunicipios() {
            const input = document.getElementById('lugar_p').value.toLowerCase();
            const items = document.querySelectorAll('.dropdown-item');
            const dropdown = document.getElementById('dropdown_municipios');
            dropdown.style.display = 'block';
            items.forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(input) ? 'block' : 'none';
            });
        }

        function seleccionarMunicipio(nombre) {
            document.getElementById('lugar_p').value = nombre;
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function gestionarPerfil() {
            const invitado = <?php echo $es_invitado ? 'true' : 'false'; ?>;
            document.getElementById(invitado ? 'modalInvitado' : 'modalPerfil').style.display = 'flex';
        }

        function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

        // PREVISUALIZACIÓN DE IMAGEN DINÁMICA
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('avatar-preview-container');
                    container.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                                           <input type="file" name="foto" accept="image/png, image/jpeg" style="display:none;" onchange="previewImage(this)">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function lanzarSistema() {
            const lugar = document.getElementById('lugar_p').value;
            if (!lugar) { alert("Ingresa un municipio"); return; }
            localStorage.setItem('municipio_demeter', lugar);
            window.location.href = 'clima_principal.php'; 
        }

        window.onclick = (e) => {
            const input = document.getElementById('lugar_p');
            const dropdown = document.getElementById('dropdown_municipios');
            if (e.target.className === 'modal-perfil') e.target.style.display = 'none';
            if (input && !input.contains(e.target) && dropdown && !dropdown.contains(e.target)) dropdown.style.display = 'none';
        }

      function toggleNotificaciones(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    const badge = document.getElementById('notifBadge');
    
    // 1. Alternamos la visibilidad del menú
    const esVisible = dropdown.style.display === 'block';
    dropdown.style.display = esVisible ? 'none' : 'block';

    // 2. LÓGICA DE "LEÍDO": Si el usuario abre el menú, ocultamos el badge
    if (!esVisible && badge) {
        badge.style.display = 'none';
    }
}

function cargarAlertas(verTodo = false) {
    const container = document.getElementById('notifContent');
    const badge = document.getElementById('notifBadge');
    const url = verTodo ? 'alertas.php?ver_todo=1' : 'alertas.php';

    fetch(url)
        .then(res => res.text())
        .then(data => {
            if (data.trim() !== "") {
                container.innerHTML = data;

                if (badge) {
                    const tieneUrgentes = data.includes("urgente-item");

                    if (tieneUrgentes) {
                        badge.innerText = "1";
                        badge.style.background = "#ff4d4d"; 
                        badge.style.display = "block";
                    } else {
                        const cantidad = (data.match(/normal-item/g) || []).length;
                        badge.innerText = cantidad;
                        badge.style.background = "#4da6ff"; 
                        badge.style.display = cantidad > 0 ? "block" : "none";
                    }
                }
            }
        })
        .catch(err => console.error("Error al monitorear clima:", err));
}

// Cargar al iniciar sesión
window.addEventListener('load', () => cargarAlertas(false));

// Cierre inteligente al hacer clic fuera
window.addEventListener('click', () => {
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) dropdown.style.display = 'none';
});


    </script>

    

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4 style="font-weight: 900; font-size: 1.2rem;">DEMÉTER</h4>
                <p>Potenciando el campo de Guanajuato con inteligencia artificial y datos en tiempo real.</p>
                <div class="social-icons">
                    <a href="#" class="icon-circle">FB</a>
                    <a href="#" class="icon-circle">IG</a>
                    <a href="#" class="icon-circle">X</a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Navegación</h4>
                <a href="#">Dashboard de Clima</a><br>
                <a href="#">Mapa de Cultivos</a><br>
                <a href="#">Alertas de Plagas</a><br>
                <a href="#">Soporte Técnico</a>
            </div>

            <div class="footer-section">
                <h4>Legal</h4>
                <a href="#">Términos de Servicio</a><br>
                <a href="#">Política de Privacidad</a><br>
                <a href="#">Cookies</a>
            </div>

            <div class="footer-section">
                <h4>Contacto</h4>
                <p>📍 Celaya, Guanajuato, MX</p>
                <p>📧 soporte@demeter.agri</p>
                <p>📞 +52 (461) 123 4567</p>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2026 SISTEMA AGRICOLA DEMÉTER - TECNOLOGÍA AGRÍCOLA SUSTENTABLE
        </div>
    </footer>
</body>
</html>