<?php
session_start();


// Verificar si el usuario está autenticado

if (!isset($_SESSION['id_usuario'])) {
    // Si no está autenticado, redirigir al login
    header('Location: logeo.html');
    exit();
}

// Actualizar la última actividad del usuario
$_SESSION['id_usuario'] = $usuario['id']; // Al momento de verificar las credenciales del usuario


// Actualizar la última actividad del usuario
$_SESSION['last_activity'] = time();

// Obtener el nombre de usuario desde la sesión
$username = $_SESSION['username'] ?? 'usuario';

// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id'] ?? null;
$cantidad = $_POST['cantidad'] ?? 1;

if (!$id_producto || $cantidad <= 0) {
    echo "Datos inválidos";
    exit();
}

// Verificar el stock del producto
$queryStock = "SELECT stock FROM productos WHERE id = ?";
$stmtStock = $db->prepare($queryStock);
$stmtStock->bind_param("i", $id_producto);
$stmtStock->execute();
$resultStock = $stmtStock->get_result();
$producto = $resultStock->fetch_assoc();
$stmtStock->close();

if (!$producto || $producto['stock'] < $cantidad) {
    echo "Stock insuficiente";
    exit();
}

// Verificar si el producto ya está en el carrito del usuario
$queryExistente = "SELECT id, cantidad FROM carrito WHERE id_usuario = ? AND id_producto = ?";
$stmtExistente = $db->prepare($queryExistente);
$stmtExistente->bind_param("ii", $id_usuario, $id_producto);
$stmtExistente->execute();
$resultExistente = $stmtExistente->get_result();
$carritoExistente = $resultExistente->fetch_assoc();
$stmtExistente->close();

if ($carritoExistente) {
    // Si ya existe, actualizar la cantidad
    $nuevaCantidad = $carritoExistente['cantidad'] + $cantidad;
    $queryUpdate = "UPDATE carrito SET cantidad = ? WHERE id = ?";
    $stmtUpdate = $db->prepare($queryUpdate);
    $stmtUpdate->bind_param("ii", $nuevaCantidad, $carritoExistente['id']);
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    // Si no existe, agregarlo al carrito
	$queryInsert = "INSERT INTO carrito (id, id, cantidad) VALUES (?, ?, ?)";
	$stmtInsert = $db->prepare($queryInsert);
	$stmtInsert->bind_param("iii", $id_usuario, $id_producto, $cantidad);
	$stmtInsert->execute();
	$stmtInsert->close();

}

$db->close();

// Redirigir de vuelta al carrito
header("Location: carrito.php");
exit();
?>
