<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: logeo.html');
    exit();
}

$id_usuario = $_SESSION['id_usuario']; 
$username = $_SESSION['username'] ?? 'usuario';

$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

$productosCarrito = [];

// Obtener los productos en el carrito
$queryCarrito = "SELECT carrito.id, carrito.id_producto, productos.nombre, productos.imagen, carrito.cantidad, productos.precio, productos.stock 
                 FROM carrito 
                 JOIN productos ON carrito.id_producto = productos.id 
                 WHERE carrito.id_usuario = ?";
$stmtCarrito = $db->prepare($queryCarrito);
$stmtCarrito->bind_param("i", $id_usuario);
$stmtCarrito->execute();
$resultCarrito = $stmtCarrito->get_result();

if ($resultCarrito->num_rows > 0) {
    $productosCarrito = $resultCarrito->fetch_all(MYSQLI_ASSOC);
}

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $id_producto = $_GET['eliminar'];
    $deleteQuery = "DELETE FROM carrito WHERE id_producto = ? AND id_usuario = ?";
    $stmtDelete = $db->prepare($deleteQuery);
    $stmtDelete->bind_param("ii", $id_producto, $id_usuario);
    $stmtDelete->execute();
    header("Location: carrito.php"); // Recargar el carrito después de eliminar
}

// Obtener las compras previas del usuario
$queryComprasPrevias = "SELECT compras.id, compras.fecha_hora, productos.nombre, detalles_compras.cantidad, productos.precio 
                        FROM compras 
                        JOIN detalles_compras ON compras.id = detalles_compras.id_compra
                        JOIN productos ON detalles_compras.id_producto = productos.id
                        WHERE compras.id_usuario = ?";
$stmtComprasPrevias = $db->prepare($queryComprasPrevias);
$stmtComprasPrevias->bind_param("i", $id_usuario);
$stmtComprasPrevias->execute();
$resultComprasPrevias = $stmtComprasPrevias->get_result();

$comprasPrevias = [];
if ($resultComprasPrevias->num_rows > 0) {
    $comprasPrevias = $resultComprasPrevias->fetch_all(MYSQLI_ASSOC);
}

$stmtCarrito->close();
$stmtComprasPrevias->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
     <style>
		.imagen-producto {
            width: 100px;  /* Tamaño aumentado de la imagen */
            height: 100px; /* Tamaño aumentado de la imagen */
            object-fit: cover; /* Asegura que la imagen se recorte bien sin deformarse */
            border-radius: 10px; /* Bordes redondeados */
            margin-right: 10px; /* Espacio entre la imagen y el texto */
        }
                :root {
            --primary-color: #ffff;
            --secondary-color: #FFD700;
            --hover-color: #FF6347;
            --background-color: #f1f8ff;
            --navbar-bg-color: rgba(0, 0, 0, 0.8);
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://walpaper.es/wallpaper/2015/09/walpaper-de-un-tablero-de-ajedrez-en-hd.jpg') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--navbar-bg-color);
            padding: 1rem 2rem;
            position: fixed;
            width: 98%;
            top: 0;
            color:var(--primary-color) ;
            transition: background-color 0.3s;
        }

        .navbar nav a {
            margin: 0 15px;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }

        .navbar nav a:hover {
            color: var(--hover-color);
        }
        h2 { 
		color: #FFFF;
		margin-top: 100px;
		font-size: 38px; }
	h1 { 
		color: #FFFF;
		margin-top: 2px;
		margin-bottom: 2px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th { background: rgba(255, 255, 255, 0.9);}
        tr:hover { background: rgba(255, 255, 255, 0.8); }
        .total {
            font-size: 38px;
            font-weight: bold;
            color: white;
            margin-top: 20px;
        }
        .cantidad {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }
        .cantidad input {
            width: 40px;
            text-align: center;
            border: none;
            font-size: 16px;
            background: none;
        }
        .accion {
            cursor: pointer;
            padding: 5px 10px;
            background: white;
            color: #2980b9;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .accion:hover { 
	    background-color: #2980b9;
	    color: white; }
        .imagen-producto {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
        .btn-ticket {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #27ae60;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-ticket:hover {
            background-color: #219150;
        }
		.btn-email {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #27ae60;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-email:hover {
            background-color: #219150;
        }
		.comprar-btn {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #27ae60;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
	.eliminar-btn {
    padding: 5px 10px;
    background-color: red;  /* Fondo rojo */
    color: white;  /* Texto blanco */
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.eliminar-btn:hover {
    background-color: darkred;  /* Color de fondo cuando se pasa el ratón */
}

    </style>
</head>
<body>

<div class="navbar">
    <h1>🏡 Bienvenido a tu carrito, <?php echo htmlspecialchars($username); ?> 🏘️!</h1>
    <nav>
        <a href="dashboard.php">🏠 Página Principal 🔙</a>
        <a href="logeo.html" class="btn-regresar">❌Regresar al Login❌</a>
    </nav>
</div>

<?php if (count($productosCarrito) > 0): ?>
    <h2>Tu carrito:</h2>
    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Total por Producto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productosCarrito as $producto): ?>
                <tr>
                    <td>
                        <img src="imagen.php?id=<?php echo $producto['id_producto']; ?>" alt="Imagen del producto" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                    </td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td class="cantidad">
                        <button class="accion" onclick="actualizarCantidad(<?php echo $producto['id']; ?>, <?php echo $producto['id_producto']; ?>, 'restar')">-</button>
                        <input type="text" id="cantidad_<?php echo $producto['id']; ?>" value="<?php echo $producto['cantidad']; ?>" readonly>
                        <button class="accion" onclick="actualizarCantidad(<?php echo $producto['id']; ?>, <?php echo $producto['id_producto']; ?>, 'sumar')">+</button>
                    </td>
                    <td>$<span id="precio_<?php echo $producto['id']; ?>"><?php echo number_format($producto['precio'], 2); ?></span></td>
                    <td><span id="stock_<?php echo $producto['id_producto']; ?>"><?php echo $producto['stock']; ?></span></td>
                    <td>$<span id="total_<?php echo $producto['id']; ?>"><?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span></td>
                    <td><a href="?eliminar=<?php echo $producto['id_producto']; ?>"><button class="eliminar-btn">Eliminar</button></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total">Total: $<span id="total_general">
    <?php echo number_format(array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $productosCarrito)), 2); ?>
    </span></p>
    <form action="generar_ticket.php" method="post">
        <button type="submit" class="btn-ticket">Generar Ticket en PDF</button>
    </form>
    <form action="envio_correo.php" method="post">
        <input type="hidden" name="user_id" value="<?php echo $id_usuario; ?>">
        <button type="submit" class="btn-email">Enviar Ticket</button>
    </form>
    <!-- Botón para efectuar la compra -->
    <a href="efectuar_compra.php"><button class="comprar-btn">Efectuar Compra</button></a>

<?php else: ?>
    <p>No tienes productos en tu carrito.</p>
<?php endif; ?>

<!-- Sección de compras previas -->
<!-- Mostrar compras previas -->
<?php if (count($comprasPrevias) > 0): ?>
    <h2>Compras Anteriores:</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha de Compra</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comprasPrevias as $compra): ?>
                <tr>
                    <td><?php echo $compra['fecha_hora']; ?></td>
                    <td><?php echo $compra['nombre']; ?></td>
                    <td><?php echo $compra['cantidad']; ?></td>
                    <td>$<?php echo number_format($compra['precio'], 2); ?></td>
                    <td>$<?php echo number_format($compra['precio'] * $compra['cantidad'], 2); ?></td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <H2>No has realizado compras previas.</H2>
<?php endif; ?>

</body>
</html>
