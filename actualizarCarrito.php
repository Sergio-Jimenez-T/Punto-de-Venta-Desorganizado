<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'tableshop');

if ($db->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_carrito = intval($_POST['id_carrito']);
    $cantidad = intval($_POST['cantidad']);

    // Obtener precio y stock actual del producto
    $queryProducto = "SELECT productos.precio, productos.stock FROM carrito JOIN productos ON carrito.id_producto = productos.id WHERE carrito.id = ?";
    $stmtProducto = $db->prepare($queryProducto);
    $stmtProducto->bind_param("i", $id_carrito);
    $stmtProducto->execute();
    $resultProducto = $stmtProducto->get_result();
    $producto = $resultProducto->fetch_assoc();
    $stmtProducto->close();

    if ($producto && $cantidad > 0 && $cantidad <= $producto['stock']) {
        // Actualizar la cantidad en el carrito
        $queryUpdate = "UPDATE carrito SET cantidad = ? WHERE id = ?";
        $stmtUpdate = $db->prepare($queryUpdate);
        $stmtUpdate->bind_param("ii", $cantidad, $id_carrito);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $nuevoTotalProducto = $producto['precio'] * $cantidad;

        // Obtener el total general desde la base de datos
        $queryTotal = "SELECT SUM(carrito.cantidad * productos.precio) AS total_general 
                       FROM carrito 
                       JOIN productos ON carrito.id_producto = productos.id 
                       WHERE carrito.id_usuario = ?";
        $stmtTotal = $db->prepare($queryTotal);
        $stmtTotal->bind_param("i", $_SESSION['id_usuario']);
        $stmtTotal->execute();
        $resultTotal = $stmtTotal->get_result();
        $totalGeneral = $resultTotal->fetch_assoc()['total_general'];
        $stmtTotal->close();

        // Respuesta JSON para el frontend
        echo json_encode([
    "nuevaCantidad" => $cantidad,
    "nuevoTotalProducto" => number_format($nuevoTotalProducto, 2, '.', ''), // Ej: 1091.98
    "totalGeneral" => number_format($totalGeneral, 2, '.', '') // Ej: 1116.97
]);
    } else {
        echo json_encode(["error" => "Stock insuficiente o cantidad inválida"]);
    }
}

$db->close();
?>