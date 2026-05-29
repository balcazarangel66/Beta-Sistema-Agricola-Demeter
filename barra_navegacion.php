<?php
session_start();

// --- LÓGICA USUARIO ---
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
    $inicial = strtoupper(substr($nombre_display, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEMÉTER</title>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0b0e14;
            color: white;
            padding-top: 90px;
        }

        /* NAVBAR */
        nav {
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(11, 14, 20, 0.95);
            position: fixed;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(77, 166, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: #a0aec0;
            text-decoration: none;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links a:hover {
            color: #4da6ff;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

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
        

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #00ff00;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.2);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-content input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            background: #0b0e14;
            border: 1px solid #2d3748;
            color: white;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
        }

        .modal-content input:focus {
            border-color: #4da6ff;
        }

        .btn-save {
            width: 100%;
            padding: 15px;
            background: #00ff00;
            color: #0b0e14;
            border: none;
            border-radius: 12px;
            font-weight: 900;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s;
        }

        .btn-save:hover {
            background: #00cc00;
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #ff4d4d;
            border: 1px solid #ff4d4d;
            border-radius: 12px;
            margin-top: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255, 77, 77, 0.1);
        }

        /* ESTILO DEL BOTÓN CANCELAR TIPO ENLACE */
        .btn-cancelar {
            background: none;
            border: none;
            color: #718096;
            margin-top: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-block;
            transition: color 0.3s;
        }

        .btn-cancelar:hover {
            color: #cbd5e0;
        }

        /* 🔔 ALERTAS */
        .notification-container {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #ff4d4d;
            font-size: 0.6rem;
            padding: 3px 6px;
            border-radius: 50%;
            display: none;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 35px;
            width: 320px;
            background: #1a1f26;
            border-radius: 15px;
            padding: 10px;
            border: 1px solid rgba(77,166,255,0.3);
        }
    </style>
</head>

<body>

    <nav>
        <div style="font-weight: 900; letter-spacing: 3px;">DEMÉTER</div>

        <div class="nav-links">
            <a href="presentacion.php">Inicio</a>
            <a href="precios.html">Panel de precios agrícolas</a>
            <a href="plagas.html">Plagas agrícolas</a>
        </div>

        <div style="display:flex; align-items:center; gap:15px;">
            <?php if (!$es_invitado): ?>
            <div class="notification-container" onclick="toggleNotificaciones(event)">
                🔔
                <div id="notifBadge" class="notification-badge">0</div>
                <div id="notifDropdown" class="notification-dropdown">
                    <div id="notifContent">Cargando alertas...</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="user-profile" onclick="gestionarPerfil()">
                <span style="font-size: 0.8rem;">
                    HOLA, <strong><?php echo strtoupper($nombre_display); ?></strong>
                </span>
                <div class="user-avatar-container">
                    <?php if ($foto_display && $foto_display != 'default.png'): ?>
                        <img src="Recursos/fotos_perfil/<?php echo $foto_display . '?v=' . time(); ?>">
                    <?php else: ?>
                        <span style="font-weight: bold;"><?php echo $inicial; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div id="modalPerfil" class="modal-perfil">
        <div class="modal-content">
            <h3>MI PERFIL</h3>

            <form action="actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                <label for="inputFoto" class="profile-avatar">
                    <?php if ($foto_display && $foto_display != 'default.png'): ?>
                        <img id="avatarPreview" src="Recursos/fotos_perfil/<?php echo $foto_display . '?v=' . time(); ?>">
                    <?php else: ?>
                        <span style="font-size: 2.5rem; font-weight: bold;"><?php echo $inicial; ?></span>
                    <?php endif; ?>
                </label>

                <input type="file" id="inputFoto" name="foto" style="display:none;" onchange="previewImage(this)">

                <input type="text" name="nuevo_nombre" value="<?php echo $nombre_display; ?>" required placeholder="Nombre">
                <input type="email" name="nuevo_email" value="<?php echo $email_display; ?>" required placeholder="Correo electrónico">

                <button type="submit" class="btn-save">GUARDAR CAMBIOS</button>
            </form>

            <button class="btn-logout" onclick="window.location.href='cierre_sesion.php'">CERRAR SESIÓN</button>
            
            <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
        </div>
    </div>

    <script>
        // MANEJO DEL MODAL
        function gestionarPerfil() {
            document.getElementById('modalPerfil').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalPerfil').style.display = 'none';
        }

        // PREVIEW DE IMAGEN
        function previewImage(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    const avatarLabel = document.querySelector('.profile-avatar');
                    avatarLabel.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 🔔 ALERTAS
        function toggleNotificaciones(e) {
            e.stopPropagation();
            const d = document.getElementById('notifDropdown');
            d.style.display = d.style.display === 'block' ? 'none' : 'block';
        }

        function cargarAlertas() {
            fetch('alertas.php')
            .then(r => r.text())
            .then(data => {
                document.getElementById('notifContent').innerHTML = data;
                if (data.includes("urgente-item")) {
                    document.getElementById('notifBadge').style.display = 'block';
                }
            })
            .catch(err => console.log("Error cargando alertas:", err));
        }

        window.addEventListener('load', cargarAlertas);

        window.onclick = function(e) {
            if (e.target.classList.contains('modal-perfil')) {
                cerrarModal();
            }
            if (!e.target.closest('.notification-container')) {
                document.getElementById('notifDropdown').style.display = 'none';
            }
        }
    </script>

</body>
</html>