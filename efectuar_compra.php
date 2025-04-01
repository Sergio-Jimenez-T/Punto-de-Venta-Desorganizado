<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'tableshop');

// Comprobar si la conexión es exitosa
if ($db->connect_error) {
    die('Error de conexión: ' . $db->connect_error);
}

date_default_timezone_set('America/Mexico_City');
$id_usuario = $_SESSION['id_usuario'];
$fecha_hora = date('Y-m-d H:i:s');

// Obtener los productos del carrito
$query = "SELECT carrito.id_producto, productos.nombre, carrito.cantidad, productos.precio, productos.stock
          FROM carrito 
          JOIN productos ON carrito.id_producto = productos.id 
          WHERE carrito.id_usuario = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Iniciar la transacción
$db->begin_transaction();

try {
    // Insertar los datos de la compra
    $insertCompra = "INSERT INTO compras (id_usuario, fecha_hora) VALUES (?, ?)";
    $stmtCompra = $db->prepare($insertCompra);
    $stmtCompra->bind_param("is", $id_usuario, $fecha_hora);
    $stmtCompra->execute();
    $compraId = $stmtCompra->insert_id; // Obtener el ID de la compra

    // Insertar detalles de la compra y actualizar el stock
    while ($row = $result->fetch_assoc()) {
        // Insertar el detalle de la compra
        $insertDetalle = "INSERT INTO detalles_compras (id_compra, id_producto, cantidad) VALUES (?, ?, ?)";
        $stmtDetalle = $db->prepare($insertDetalle);
        $stmtDetalle->bind_param("iii", $compraId, $row['id_producto'], $row['cantidad']);
        $stmtDetalle->execute();

        // Actualizar el stock del producto
        $newStock = $row['stock'] - $row['cantidad'];  // Usar $row para obtener los datos de cada producto
        if ($newStock < 0) {
            throw new Exception("Stock insuficiente para el producto: " . $row['nombre']);
        }

        $updateStock = "UPDATE productos SET stock = ? WHERE id = ?";
        $stmtUpdateStock = $db->prepare($updateStock);
        $stmtUpdateStock->bind_param("ii", $newStock, $row['id_producto']);
        $stmtUpdateStock->execute();
    }

    // Confirmar la transacción
    $db->commit();

    // Eliminar los productos del carrito después de la compra
    $deleteCarrito = "DELETE FROM carrito WHERE id_usuario = ?";
    $stmtDeleteCarrito = $db->prepare($deleteCarrito);
    $stmtDeleteCarrito->bind_param("i", $id_usuario);
    $stmtDeleteCarrito->execute();

    // Redirigir a la página de ticket de compra en PDF
    header("Location: generar_ticket.php?id_compra=" . $compraId);
    exit;
} catch (Exception $e) {
    // Si ocurre un error, hacer rollback y mostrar el error
    $db->rollback();
    die("Error al procesar la compra: " . $e->getMessage());
}
?>
