<?php
// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Verificar si se pasó el ID del producto
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Asegurarse de que el ID es un número entero

    // Consultar la base de datos para obtener la imagen
    $query = "SELECT imagen FROM productos WHERE id = $id";
    $result = $db->query($query);

    // Verificar si se encontró el producto
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagen = $row['imagen'];

        // Mostrar la imagen en el navegador
        header("Content-Type: image/jpeg"); // Asumimos que las imágenes son JPEG
        echo $imagen;
    } else {
        echo 'Imagen no encontrada para este producto.';
    }
} else {
    echo 'ID del producto no especificado.';
}

$db->close();
?>
