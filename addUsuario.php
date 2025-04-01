<?php
// Conexión a la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'tableshop');

// Verificar conexión
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}
date_default_timezone_set('America/Mexico_City');
// Verificar que todos los campos estén presentes
if (isset($_POST['nombre'], $_POST['usuario'], $_POST['contraseña'], $_POST['correo'], $_POST['edad'])) {
    // Recibir datos del formulario
    $nombre = $_POST['nombre'];
    $username = $_POST['usuario'];
    $password = $_POST['contraseña'];
    $correo = $_POST['correo'];
    $edad = $_POST['edad'];

    // Encriptar la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar los datos en la base de datos
    if (isset($_POST['admin']) && isset($_POST['status'])) {
        // Registro desde el dashboard de admin
        $admin = $_POST['admin'];
        $status = $_POST['status'];
        $fecha_registro = date('Y-m-d H:i:s');

        $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, usuario, contraseña, correo, edad, admin, status, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nombre, $username, $hashed_password, $correo, $edad, $admin, $status, $fecha_registro);
    } else {
        // Registro de usuario regular
        $fecha_registro = date('Y-m-d'); // Fecha actual
        $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, usuario, contraseña, correo, edad, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nombre, $username, $hashed_password, $correo, $edad, $fecha_registro);
    }

    if ($stmt->execute()) {
        echo 'Usuario registrado exitosamente.';
        if (isset($_POST['admin']) && isset($_POST['status'])) {
            // Redirigir al dashboard de admin
            header('Location: admin_dashboard.php');
        } else {
            // Redirigir al formulario vacío
            header('Location: logeo.html');
        }
        exit;
    } else {
        echo 'Error al registrar el usuario: ' . $stmt->error;
    }

    $stmt->close();
} else {
    echo 'Faltan datos del formulario.';
}

$mysqli->close();
?>
