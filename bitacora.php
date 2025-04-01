<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Información de inicio de sesión del usuario
$user = $_POST['username'];
$pass = $_POST['password'];

// Consulta para verificar el inicio de sesión
$sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Inicio de sesión exitoso
    echo "Inicio de sesión exitoso.";
    $log_status = 'success';
} else {
    // Inicio de sesión fallido
    echo "Inicio de sesión fallido.";
    $log_status = 'fail';
}

// Registro del intento de inicio de sesión
$log_sql = "INSERT INTO login_log (username, status) VALUES ('$user', '$log_status')";
$conn->query($log_sql);

$conn->close();
?>
