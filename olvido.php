<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar - Proyecto Deméter</title>

    <style>
        /* --- TUS ESTILOS NEÓN SE MANTIENEN IGUAL --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: radial-gradient(circle at center, #162030 0%, #0b0e14 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #e0e0e0;
        }

        .login-card {
            position: relative;
            overflow: hidden;
            background: rgba(26, 31, 38, 0.9);
            padding: 45px 40px;
            border-radius: 20px;
            border: 1px solid rgba(77, 166, 255, 0.5);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 20px rgba(77, 166, 255, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(15px);
            animation: slideUp 0.6s ease-out;
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('Recursos/logo.svg') no-repeat center;
            background-size: 71%; 
            filter: brightness(1.5) drop-shadow(0 0 20px rgba(77, 166, 255, 0.8));
            mix-blend-mode: screen; 
            z-index: 0;
            pointer-events: none;
            animation: pulseGlow 4s ease-in-out infinite alternate;
        }

        @keyframes pulseGlow {
            from { opacity: 0.2; transform: scale(0.98); }
            to { opacity: 0.4; transform: scale(1.02); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-card > * { position: relative; z-index: 1; }

        .login-card h2 {
            color: #4da6ff;
            font-size: 1.8rem;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 800;
            text-shadow: 0 0 15px rgba(77, 166, 255, 0.5);
        }

        .login-card p {
            font-size: 0.8rem;
            margin-bottom: 35px;
            opacity: 0.8;
            letter-spacing: 2px;
            color: #a0aec0;
        }

        .input-group { margin-bottom: 25px; text-align: left; }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.75rem;
            color: #4da6ff;
            font-weight: 700;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 14px;
            background: rgba(11, 14, 20, 0.95);
            border: 1px solid #2d3748;
            border-radius: 10px;
            color: white;
            outline: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        input:focus { border-color: #4da6ff; box-shadow: 0 0 15px rgba(77, 166, 255, 0.3); }

        button {
            width: 100%;
            padding: 16px;
            background: #4da6ff;
            border: none;
            border-radius: 10px;
            color: #0b0e14;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover { 
            background: #63b3ff; 
            box-shadow: 0 0 25px rgba(77, 166, 255, 0.6); 
            transform: translateY(-3px); 
        }

        .footer-links { margin-top: 25px; font-size: 0.8rem; }
        .footer-links a { color: #4da6ff; text-decoration: none; font-weight: 600; }
        .divider { height: 1px; background: rgba(255,255,255,0.1); margin: 15px 0; }
        
        .seccion-oculta { display: none; }
    </style>
</head>

<body>
    <div class="login-card">
        <h2>Recuperar</h2>
        <p id="instruccion">Introduce tu correo para recibir el código</p>

        <div id="paso1">
            <div class="input-group">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" id="email" placeholder="tu_correo@ejemplo.com" required>
            </div>
            <button onclick="enviarCodigo()">Enviar Código</button>
        </div>

        <div id="paso2" class="seccion-oculta">
            <div class="input-group">
                <label>CÓDIGO DE 6 DÍGITOS</label>
                <input type="text" id="codigo_input" placeholder="000000" maxlength="6">
            </div>
            <button onclick="validarCodigo()">Validar Código</button>
        </div>

        <div id="paso3" class="seccion-oculta">
            <div class="input-group">
                <label>NUEVA CONTRASEÑA</label>
                <input type="password" id="nueva_pass" placeholder="••••••••">
            </div>
            <div class="input-group">
                <label>CONFIRMAR CONTRASEÑA</label>
                <input type="password" id="confirmar_pass" placeholder="••••••••">
            </div>
            <button onclick="actualizarPassword()">Cambiar Contraseña</button>
        </div>

        <div class="footer-links">
            <div class="divider"></div>
            <a href="login.php">Regresar al Login</a>
        </div>
    </div>

    <script>
        async function enviarCodigo() {
            const email = document.getElementById('email').value;
            if (!email) return alert("Por favor, ingresa tu correo electrónico.");

            const formData = new FormData();
            formData.append('email', email);

            try {
                const resp = await fetch('peticiones_olvido.php?accion=enviar', { method: 'POST', body: formData });
                const res = await resp.json();

                if (res.status === 'success') {
                    // CAMBIO AQUÍ: Ya no mostramos el código, avisamos que revise su correo
                    alert("¡Listo! Revisa tu bandeja de entrada o spam. Te enviamos un código.");
                    
                    document.getElementById('paso1').classList.add('seccion-oculta');
                    document.getElementById('paso2').classList.remove('seccion-oculta');
                    document.getElementById('instruccion').innerText = "Ingresa el código enviado al correo";
                } else {
                    alert(res.msg);
                }
            } catch (error) {
                alert("Error de conexión con el servidor.");
            }
        }

        async function validarCodigo() {
            const codigo = document.getElementById('codigo_input').value;
            if (!codigo) return alert("Debes ingresar el código de 6 dígitos.");

            const formData = new FormData();
            formData.append('codigo', codigo);

            try {
                const resp = await fetch('peticiones_olvido.php?accion=validar', { method: 'POST', body: formData });
                const res = await resp.json();

                if (res.status === 'success') {
                    document.getElementById('paso2').classList.add('seccion-oculta');
                    document.getElementById('paso3').classList.remove('seccion-oculta');
                    document.getElementById('instruccion').innerText = "Configura tu nueva contraseña";
                } else {
                    alert(res.msg);
                }
            } catch (error) {
                alert("Error al validar el código.");
            }
        }

        async function actualizarPassword() {
            const p1 = document.getElementById('nueva_pass').value;
            const p2 = document.getElementById('confirmar_pass').value;

            if (!p1 || !p2) return alert("Completa ambos campos de contraseña.");
            if (p1 !== p2) return alert("Las contraseñas no coinciden.");

            const formData = new FormData();
            formData.append('pass', p1);

            try {
                const resp = await fetch('peticiones_olvido.php?accion=cambiar', { method: 'POST', body: formData });
                const res = await resp.json();

                if (res.status === 'success') {
                    alert("¡Contraseña actualizada con éxito! Volviendo al inicio.");
                    window.location.href = "login.php";
                } else {
                    alert("No se pudo actualizar la contraseña.");
                }
            } catch (error) {
                alert("Error en el proceso de actualización.");
            }
        }
    </script>
</body>
</html>