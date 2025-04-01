<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: logeo.html');
    exit;
}

// Verificar si se ha pasado el ID del registro y la tabla
if (isset($_GET['id']) && isset($_GET['tabla'])) {
    // Conectar a la base de datos
    $db = new mysqli('localhost', 'root', '', 'tableshop');
    if ($db->connect_error) {
        die('Error de conexión a la base de datos: ' . $db->connect_error);
    }

    // Obtener la tabla y el ID
    $tabla = $_GET['tabla'];
    $id = $_GET['id'];

    // Asegurarse de que la tabla esté en la lista de tablas permitidas
    $tablas_permitidas = ['usuarios', 'bitacora', 'productos', 'pedidos'];
    if (in_array($tabla, $tablas_permitidas)) {
        // Eliminar el registro de la tabla correspondiente
        $sql = "DELETE FROM $tabla WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            // Redirigir a la página principal de la tabla correspondiente
            header("Location: admin_dashboard.php?tabla=$tabla");
            exit;
        } else {
            echo 'Error al eliminar el registro: ' . $db->error;
        }

        $stmt->close();
    } else {
        echo 'Tabla no válida.';
    }

    $db->close();
} else {
    echo 'No se proporcionaron parámetros.';
}
?>
