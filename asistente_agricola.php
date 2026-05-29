<?php
// 1. Silenciamos avisos innecesarios que rompen el diseño y manejamos la sesión
error_reporting(E_ALL & ~E_NOTICE);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de IA
$apiKey = "AIzaSyDkOx4pKqV_svhM75bwZiB0Y1HvRqruyAQ"; 
$model = "gemini-flash-latest";

// --- LÓGICA AJAX PARA LA IA ---
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $mensajeUsuario = $input['mensaje'] ?? '';
    
    if ($mensajeUsuario) {
        if (!isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [["role" => "user", "parts" => [["text" => "Eres un agrónomo experto de la plataforma Deméter. Eres amable, profesional y ayudas a los agricultores con sus cultivos."]]]];
        }

        $_SESSION['chat_history'][] = ["role" => "user", "parts" => [["text" => $mensajeUsuario]]];
        
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
        $data = ["contents" => array_slice($_SESSION['chat_history'], -10)]; // Enviamos los últimos 10 mensajes para contexto

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        
        $textoIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento, tuve un pequeño problema con la conexión. ¿Podrías repetir eso?";
        $_SESSION['chat_history'][] = ["role" => "model", "parts" => [["text" => $textoIA]]];
        
        echo json_encode(["respuesta" => $textoIA]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistente Deméter 🌿</title>

    <style>
        /* ESTILOS DE BASE */
        body {
            background: #050505;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 100px; /* Espacio para que la navbar no tape el chat */
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        /* CONTENEDOR PRINCIPAL */
        .chat-container {
            width: 100%;
            max-width: 600px;
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }

        .chat-window {
            background: #0a0a0a;
            border: 1px solid #222;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            scrollbar-width: thin;
            scrollbar-color: #333 #0a0a0a;
        }

        /* BURBUJAS */
        .bubble {
            max-width: 85%;
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .user { 
            align-self: flex-end; 
            background: #007bff; 
            color: white;
            border-bottom-right-radius: 4px;
        }

        .model { 
            align-self: flex-start; 
            background: #1e1e1e; 
            border: 1px solid #333;
            color: #e0e0e0;
            border-bottom-left-radius: 4px;
        }

        /* ÁREA DE ENTRADA */
        .input-area {
            padding: 20px;
            display: flex;
            gap: 12px;
            background: #111;
            border-top: 1px solid #222;
        }

        input {
            flex: 1;
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            outline: none;
            transition: 0.3s;
        }

        input:focus { border-color: #007bff; }

        button {
            background: #007bff;
            border: none;
            color: #fff;
            padding: 0 25px;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover { background: #0056b3; transform: scale(1.05); }
        button:disabled { background: #333; cursor: not-allowed; }

        /* Ajuste para móviles */
        @media (max-width: 480px) {
            body { padding-top: 80px; }
            .chat-container { padding: 0 10px; }
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
<body>

    <?php include 'barra_navegacion.php'; ?>

    <div class="chat-container">
        <div class="chat-window">
            <div class="messages" id="chatBox">
                <div class="bubble model">¡Qué onda! Soy tu asistente de Deméter. ¿En qué puedo ayudarte con tus cultivos hoy? 🌽</div>
            </div>

            <div class="input-area">
                <input type="text" id="msgInput" placeholder="Escribe tu duda agrícola..." autocomplete="off">
                <button id="sendBtn">ENVIAR</button>
            </div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');
        const msgInput = document.getElementById('msgInput');
        const sendBtn = document.getElementById('sendBtn');

        async function enviar() {
            const txt = msgInput.value.trim();
            if(!txt || sendBtn.disabled) return;

            appendMsg(txt, 'user');
            msgInput.value = '';
            sendBtn.disabled = true;

            const loadId = 'l-' + Date.now();
            appendMsg('...', 'model', loadId);

            try {
                const res = await fetch('?ajax=1', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensaje: txt })
                });

                const data = await res.json();
                document.getElementById(loadId).innerText = data.respuesta;

            } catch (e) {
                document.getElementById(loadId).innerText = "Error de conexión. Intenta de nuevo.";
            } finally {
                sendBtn.disabled = false;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function appendMsg(t, r, id='') {
            const d = document.createElement('div');
            d.className = 'bubble ' + r;
            if(id) d.id = id;
            d.innerText = t;
            chatBox.appendChild(d);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        sendBtn.onclick = enviar;
        msgInput.onkeypress = (e) => { if(e.key === 'Enter') enviar(); };
    </script>


    <footer class="footer-bottom">
        &copy; 2026 SISTEMA AGRICOLA DEMÉTER - TECNOLOGÍA AGRÍCOLA SUSTENTABLE
    </footer>

</body>
</html>