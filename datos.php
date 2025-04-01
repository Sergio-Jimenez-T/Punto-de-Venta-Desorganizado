<?php
// Conexión a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión: ' . $db->connect_error);
}

// Obtener las fechas desde el formulario (si se envían)
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

// Consulta base
$query = "SELECT productos.nombre AS producto, SUM(detalles_compras.cantidad) AS cantidad_vendida 
          FROM productos
          JOIN detalles_compras ON productos.id = detalles_compras.id_producto
          JOIN compras ON detalles_compras.id_compra = compras.id"; // Unimos con la tabla compras para obtener fecha

// Agregar el filtro de fechas si se envían
if ($fechaInicio && $fechaFin) {
    $query .= " WHERE compras.fecha_hora BETWEEN '$fechaInicio' AND '$fechaFin'"; // Filtramos por fecha_hora en la tabla compras
}

$query .= " GROUP BY productos.nombre";

$result = $db->query($query);

if (!$result) {
    die('Error en la consulta: ' . $db->error);
}

$valores = [];
while ($row = $result->fetch_assoc()) {
    $valores[] = $row;
}

header('Content-Type: application/json');
echo json_encode($valores);

$db->close();
?>
