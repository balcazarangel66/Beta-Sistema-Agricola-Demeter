<?php
// 1. Cargar PHPMailer mediante Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

include 'Conexion.php'; 
header('Content-Type: application/json');
session_start();

$accion = $_GET['accion'] ?? '';

if ($accion == 'enviar') {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $codigo = rand(100000, 999999);
        $id_usuario = $user['id_usuario'];

        // Guardar el código en tu tabla
        $sql = "INSERT INTO recuperacion_claves (id_usuario, codigo, expiracion) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))";
        $pdo->prepare($sql)->execute([$id_usuario, $codigo]);

        // 2. Configuración de Brevo (SMTP Real)
        $mail = new PHPMailer(true);
        try {
            // Configuración del Servidor
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com'; 
            $mail->SMTPAuth   = true;
            $mail->Port       = 587; 
            $mail->Username   = 'a725c8001@smtp-brevo.com'; 
            $mail->Password   = 'xsmtpsib-7cd94124544e315c3393e9bf256679fe9771d35e94bd21e6364a967df6beb2ee-hyH0FZK11fxAUzZs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet    = 'UTF-8'; // <--- Para que los acentos salgan bien

            // Destinatarios
            $mail->setFrom('xam8325@gmail.com', 'Sistema Agrícola Deméter');
            $mail->addAddress($email);

            // Contenido del Correo (Estilo Login Neón)
            $mail->isHTML(true);
            $mail->Subject = '🔑 Código de Recuperación - Proyecto Deméter';
            
            $mail->Body = "
                <div style='background-color: #0d1117; color: #ffffff; padding: 40px; font-family: sans-serif; text-align: center; border-radius: 10px; border: 1px solid #30363d;'>
                    <h1 style='color: #00ff00; font-size: 26px; text-transform: uppercase; letter-spacing: 2px;'>Proyecto Deméter</h1>
                    <p style='font-size: 16px; color: #c9d1d9; margin: 20px 0;'>
                        Hola, tu código de recuperación para el proyecto <br> 
                        <span style='color: #00ff00; font-weight: bold;'>sistema agrícola Deméter</span> es:
                    </p>
                    <div style='background-color: #161b22; border: 2px solid #ff0000; display: inline-block; padding: 15px 30px; border-radius: 8px; margin: 10px 0;'>
                        <span style='font-size: 35px; font-weight: bold; color: #00ff00; letter-spacing: 5px;'>$codigo</span>
                    </div>
                    <p style='font-size: 13px; color: #8b949e; margin-top: 25px;'>
                        Este código expirará en <span style='color: #ff0000;'>15 minutos</span>. <br>
                        Si no solicitaste este cambio, puedes ignorar este mensaje.
                    </p>
                    <hr style='border: 0; border-top: 1px solid #30363d; margin: 30px 0;'>
                    <p style='font-size: 10px; color: #484f58;'>© 2026 TICS - Ingeniería del Conocimiento</p>
                </div>
            ";

            $mail->send();

            $_SESSION['token_temp'] = $codigo;
            $_SESSION['email_temp'] = $email;

            echo json_encode(['status' => 'success', 'msg' => '¡Listo! Revisa tu bandeja de entrada o spam.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error de envío: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'El correo no existe en el sistema']);
    }
}

// --- Las secciones 'validar' y 'cambiar' se mantienen igual ---

if ($accion == 'validar') {
    $codigo = $_POST['codigo'];
    if ($codigo == ($_SESSION['token_temp'] ?? '')) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Código inválido']);
    }
}

if ($accion == 'cambiar') {
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $email = $_SESSION['email_temp'];

    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    if ($stmt->execute([$pass, $email])) {
        session_destroy();
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>