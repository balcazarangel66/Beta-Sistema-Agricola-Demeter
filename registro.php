<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Proyecto Deméter</title>

    <style>
        /* --- TU DISEÑO ORIGINAL Y ESTILOS NEÓN --- */
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

        /* LOGO CENTRAL (MANTENIDO) */
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

        @keyframes pulseGlow { from { opacity: 0.2; transform: scale(0.98); } to { opacity: 0.4; transform: scale(1.02); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .login-card > * { position: relative; z-index: 1; }

        .login-card h2 { color: #4da6ff; font-size: 1.8rem; margin-top: 10px; text-transform: uppercase; letter-spacing: 4px; font-weight: 800; text-shadow: 0 0 15px rgba(77, 166, 255, 0.5); }
        .login-card p { font-size: 0.8rem; margin-bottom: 35px; opacity: 0.8; letter-spacing: 2px; color: #a0aec0; }

        /* INPUTS Y REGLAS */
        .input-group { margin-bottom: 25px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-size: 0.75rem; color: #4da6ff; font-weight: 700; letter-spacing: 1px; }
        input { width: 100%; padding: 14px; background: rgba(11, 14, 20, 0.95); border: 1px solid #2d3748; border-radius: 10px; color: white; outline: none; transition: 0.3s; }
        input:focus { border-color: #4da6ff; box-shadow: 0 0 15px rgba(77, 166, 255, 0.3); }

        .password-policies { font-size: 0.7rem; text-align: left; margin-top: -15px; margin-bottom: 20px; color: #8b949e; }
        .policy { display: block; transition: 0.3s; }
        .policy.valid { color: #00ff00; }

        /* MODAL NEÓN PERSONALIZADO */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #1a1f26;
            padding: 40px;
            border-radius: 20px;
            border: 2px solid #4da6ff;
            text-align: center;
            box-shadow: 0 0 40px rgba(77, 166, 255, 0.5);
            max-width: 350px;
            animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        @keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .modal-content h3 { color: #00ff00; margin-bottom: 15px; text-transform: uppercase; font-size: 22px; }
        .modal-content p { color: #e0e0e0; font-size: 16px; line-height: 1.5; }
        .modal-btn {
            background: #4da6ff;
            color: #0b0e14;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 25px;
            width: 100%;
            text-transform: uppercase;
        }

        button[type="submit"] { width: 100%; padding: 16px; background: #4da6ff; border: none; border-radius: 10px; color: #0b0e14; font-weight: 800; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; margin-top: 10px; }
        button[type="submit"]:hover { box-shadow: 0 0 25px rgba(77, 166, 255, 0.6); transform: translateY(-3px); }

        .footer-links { margin-top: 25px; font-size: 0.8rem; }
        .footer-links a { color: #4da6ff; text-decoration: none; font-weight: 600; }
        .divider { height: 1px; background: rgba(255,255,255,0.1); margin: 15px 0; }
        
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .error-shake { animation: shake 0.3s ease-in-out 2; border-color: #ff0000 !important; }
    </style>
</head>

<body>
    <div class="modal-overlay" id="modalExito">
        <div class="modal-content">
            <h3 id="modalTitle">¡BIENVENIDO!</h3>
            <p id="modalMsg">Hola <strong id="userName" style="color: #4da6ff;"></strong>, tu registro en <strong>Sistema Agricola Deméter</strong> ha sido exitoso. <br><br> ¡Disfruta explorando el sistema!</p>
            <button class="modal-btn" onclick="window.location.href='login.php'">EMPEZAR AHORA</button>
        </div>
    </div>

    <div class="login-card">
        <h2>Únete</h2>
        <p>Sistema Agrícola Deméter</p>

        <form id="registroForm">
            <div class="input-group">
                <label>NOMBRE DE USUARIO</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Ej: JuanPerez" required>
            </div>

            <div class="input-group">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="input-group">
                <label>CONTRASEÑA</label>
                <input type="password" id="pass" name="password" placeholder="••••••••" required>
            </div>

            <div class="password-policies">
                <span class="policy" id="len">• Mínimo 8 caracteres</span>
                <span class="policy" id="up">• Al menos una Mayúscula</span>
                <span class="policy" id="num">• Al menos un Número</span>
            </div>

            <button type="submit">Finalizar Registro</button>
        </form>

        <div class="footer-links">
            <div class="divider"></div>
            <span>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></span>
        </div>
    </div>

    <script>
        const passInput = document.getElementById('pass');
        const form = document.getElementById('registroForm');
        const userInput = document.getElementById('nombre_usuario');

        // 1. Validación visual
        passInput.addEventListener('input', () => {
            const val = passInput.value;
            document.getElementById('len').classList.toggle('valid', val.length >= 8);
            document.getElementById('up').classList.toggle('valid', /[A-Z]/.test(val));
            document.getElementById('num').classList.toggle('valid', /\d/.test(val));
        });

        // 2. Envío Real y Modal Personalizado
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const val = passInput.value;
            const isValid = val.length >= 8 && /[A-Z]/.test(val) && /\d/.test(val);

            if (!isValid) {
                passInput.classList.add('error-shake');
                setTimeout(() => passInput.classList.remove('error-shake'), 600);
                return;
            }

            const formData = new FormData(form);
            const nombreCapturado = userInput.value; // Agarramos el nombre del input

            // ENVÍO A PHP
            fetch('procesar_registro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Si el PHP funciona bien, llenamos el modal y lo mostramos
                document.getElementById('userName').innerText = nombreCapturado;
                document.getElementById('modalExito').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error de conexión con el servidor");
            });
        });
    </script>
</body>
</html>