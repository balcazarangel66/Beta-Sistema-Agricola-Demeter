<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Agrícola - Proyecto Deméter</title>

    <style>
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
            background: rgba(26, 31, 38, 0.9); /* Un poco más sólido para que el logo resalte */
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

        /* --- 🔥 LOGO CENTRAL (ESTILO IMAGEN) --- */
        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('Recursos/logo.svg') no-repeat center;
            background-size: 71%; /* Tamaño grande como en la captura */
            
            /* El truco del brillo azul intenso */
            filter: 
                brightness(1.5) 
                drop-shadow(0 0 20px rgba(77, 166, 255, 0.8))
                drop-shadow(0 0 40px rgba(77, 166, 255, 0.4));
            
            mix-blend-mode: screen; 
            z-index: 0;
            pointer-events: none;
            animation: pulseGlow 4s ease-in-out infinite alternate;
        }

        /* --- ANIMACIONES --- */
        @keyframes pulseGlow {
            from { 
                opacity: 0.2; 
                transform: scale(0.98); 
                filter: brightness(1.2) drop-shadow(0 0 15px rgba(77, 166, 255, 0.6));
            }
            to { 
                opacity: 0.4; /* Subimos opacidad para que se vea como la imagen */
                transform: scale(1.02); 
                filter: brightness(2) drop-shadow(0 0 35px rgba(77, 166, 255, 1));
            }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Asegurar que el contenido flote sobre el logo */
        .login-card > * {
            position: relative;
            z-index: 1;
        }

        .login-card h2 {
            color: #4da6ff;
            font-size: 1.8rem;
            margin-top: 10px;
            margin-bottom: 5px;
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

        /* --- INPUTS --- */
        .input-group {
            margin-bottom: 25px;
            text-align: left;
        }

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

        input:focus {
            border-color: #4da6ff;
            background: #1a202c;
            box-shadow: 0 0 15px rgba(77, 166, 255, 0.3);
        }

        /* --- BOTÓN --- */
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
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #63b3ff;
            box-shadow: 0 0 25px rgba(77, 166, 255, 0.6);
            transform: translateY(-3px);
        }

        /* --- FOOTER --- */
        .footer-links {
            margin-top: 25px;
            font-size: 0.8rem;
        }

        .footer-links a {
            color: #4da6ff;
            text-decoration: none;
            font-weight: 600;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2>Bienvenido</h2>
        <p>Sistema Agricola Deméter</p>

        <form action="validar.php" method="POST">
            <div class="input-group">
                <label>USUARIO</label>
                <input type="text" name="user" placeholder="nombre@ejemplo.com" required>
            </div>

            <div class="input-group">
                <label>CONTRASEÑA</label>
                <input type="password" name="pass" placeholder="••••••••" required>
            </div>

            <button type="submit">Entrar al Sistema</button>
        </form>

        <div class="footer-links">
            <a href="olvido.php">¿Olvidaste tu contraseña?</a>
            <div class="divider"></div>
            <span>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></span>
        </div>
    </div>
</body>
</html>