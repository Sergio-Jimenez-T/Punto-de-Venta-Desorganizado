<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: logeo.html');
    exit;
}

// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Verificar si se enviaron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];

    // Si no se ingresó fecha, establecer la fecha local en CDMX
    if (empty($_POST['fecha_registro'])) {
        date_default_timezone_set('America/Mexico_City');
        $fecha = date('Y-m-d');
    } else {
        $fecha = $_POST['fecha_registro'];
    }

    // Verificar si se subió una imagen correctamente
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']); // Leer la imagen en binario

        // Usar una consulta preparada para insertar datos BLOB
        $query = $db->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
        $query->bind_param("ssdibs", $nombre, $descripcion, $precio, $stock, $imagen, $fecha);
        $query->send_long_data(4, $imagen); // Enviar datos binarios de imagen

        if ($query->execute()) {
            echo '✅ Producto agregado exitosamente.';
			 // Redirigir al dashboard de admin
            header('Location: admin_dashboard.php');
        } else {
            echo '❌ Error al agregar el producto: ' . $db->error;
        }

        $query->close();
    } else {
        echo '❌ Error al subir la imagen.';
    }
}

$db->close();
?>
