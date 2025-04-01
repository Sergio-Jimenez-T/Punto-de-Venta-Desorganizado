<?php
// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Asegúrate de que la sesión esté iniciada
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id'] ?? null;
$cantidad = $_POST['cantidad'] ?? 1;

if (!$id_producto || $cantidad <= 0) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit();
}
// Verificar si el usuario existe
$queryUsuario = "SELECT id FROM usuarios WHERE id = ?";
$stmtUsuario = $db->prepare($queryUsuario);
$stmtUsuario->bind_param("i", $id_usuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();
$stmtUsuario->close();

if ($resultUsuario->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'El usuario no existe']);
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
    echo json_encode(["status" => "error", "message" => "Stock insuficiente"]);
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
    $queryInsert = "INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmtInsert = $db->prepare($queryInsert);
    $stmtInsert->bind_param("iii", $id_usuario, $id_producto, $cantidad);
    $stmtInsert->execute();
    $stmtInsert->close();
}

// Enviar respuesta en formato JSON
echo json_encode(["status" => "success", "message" => "Producto agregado al carrito"]);
$db->close();
?>
