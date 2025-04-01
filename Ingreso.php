<?php
session_start(); // Iniciar la sesión

$lockout_time = 60; // Tiempo de suspensión en segundos (1 minuto)
$inactivity_limit = 300; // Tiempo de inactividad en segundos (5 minutos)
$estado = 'false'; // Estado de inicio de sesión fallido inicialmente

$auth = false; // Establecer como falso inicialmente

// Verificar si el usuario está bloqueado
if (isset($_SESSION['last_attempt_time']) && $_SESSION['login_attempts'] >= 3) {
    $remaining_time = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    if ($remaining_time > 0) {
        echo "Demasiados intentos fallidos. Intente nuevamente en $remaining_time segundos.";
        exit; // Salir si el usuario está bloqueado
    } else {
        // Permitir nuevos intentos si ha pasado el tiempo de bloqueo
        $_SESSION['login_attempts'] = 0; // Restablecer los intentos fallidos
    }
}

// Verificar si las credenciales fueron enviadas y si el usuario no está bloqueado
if (isset($_POST['usuario']) && isset($_POST['contraseña'])) {
    $username = $_POST['usuario'];
    $password = $_POST['contraseña'];

    // Conectar a la base de datos (tableshop)
    $db = new mysqli('localhost', 'root', '', 'tableshop');

    // Verificar si la conexión fue exitosa
    if ($db->connect_error) {
        die('Error de conexión a la base de datos: ' . $db->connect_error);
    }

    // Prepara la consulta para evitar inyecciones SQL
    $stmt = $db->prepare("SELECT id, contraseña, admin FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $username); // Usar el nombre de usuario enviado

    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontraron resultados
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_password = $user['contraseña'];
        $es_admin = $user['admin'];

        // Verificar la contraseña
        if (password_verify($password, $stored_password)) {
            $auth = true;
            $_SESSION['login_attempts'] = 0; // Restablecer los intentos fallidos
            $_SESSION['username'] = $username; // Almacenar el nombre de usuario
            $_SESSION['admin'] = $es_admin; // Almacenar el rol de usuario
            $_SESSION['id_usuario'] = $user['id']; // Guarda el id del usuario
            $estado = 'true'; // Cambio a 'true' cuando la autenticación es exitosa
        }
    }

    // Incrementar los intentos fallidos
    if (!$auth) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
    }

    // Registrar el intento en la bitácora
    date_default_timezone_set('America/Mexico_City');
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $bitacora_sql = $db->prepare("INSERT INTO bitacora (fecha, hora, user, tipo) VALUES (?, ?, ?, ?)");
    $bitacora_sql->bind_param("ssss", $fecha, $hora, $username, $estado);
    $bitacora_sql->execute();
    $bitacora_sql->close();

    // Cerrar la conexión
    $stmt->close();
    $db->close();

    // Redirigir al dashboard según el rol
    if ($auth) {
        if ($_SESSION['admin'] == 1) {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    }
}

echo 'Usuario o contraseña incorrectos. Inténtelo de nuevo.';
?>
