<?php
session_start(); // Iniciar la sesión

if (!isset($_SESSION['id_usuario'])) {
    // Si no está autenticado, redirigir al login
    header('Location: logeo.html');
    exit();
}

// Si está autenticado, proceder con la lógica de la página
$id_usuario = $_SESSION['id_usuario'];


// Actualizar la última actividad del usuario
$_SESSION['last_activity'] = time();

// Obtener el nombre de usuario desde la sesión
$username = $_SESSION['username'] ?? 'usuario';

// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Obtener productos desde la base de datos, incluyendo el precio y el stock
$query = "SELECT id, nombre, imagen, precio, stock FROM productos";
$result = $db->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC); // Obtener los productos como un array asociativo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        :root {
            --primary-color: #007BFF;
            --secondary-color: #FFD700;
            --hover-color: #FF6347;
            --background-color: #f1f8ff;
            --navbar-bg-color: rgba(0, 0, 0, 0.5);
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
            color: #fff;
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

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 120px 2rem 2rem;
            color: #333;
        }

        h1 {
            width: 100%;
            text-align: center;
            font-size: 2.5rem;
            color: #ffff;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .product-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 200px;
            margin: 20px;
            text-align: center;
            padding: 20px;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            cursor: pointer;
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .product-card h3 {
            color: #333;
            margin-top: 20px;
        }

        .product-card p {
            color: var(--primary-color);
            font-weight: bold;
            margin-top: 5px;
        }

        .product-card .stock {
            color: #28a745;
            font-weight: normal;
            margin-top: 5px;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }

        .product-card:hover img {
            transform: scale(1.1);
        }

    </style>
</head>
<body>    
    <div class="navbar">
        <h2>🏡 Bienvenido a la página principal, <?php echo htmlspecialchars($username); ?> 🏘️!</h2>
        <nav>
            <a href="dashboard.php">🏠 Página Principal</a>
            <a href="carrito.php">🛒 Carrito</a>
            <a href="logeo.html" class="btn-regresar">❌Regresar al Login❌</a>
        </nav>
    </div>

    <div class="container">
        <h1>Productos Disponibles</h1>
        
        <?php foreach ($products as $product) { ?>
            <div class="product-card" onclick="window.location.href='detalles_producto.php?id=<?php echo $product['id']; ?>'">
                <img src="imagen.php?id=<?php echo $product['id']; ?>" alt="Imagen del producto">
                <h3><?php echo htmlspecialchars($product['nombre']); ?></h3>
                <p>Precio: $<?php echo number_format($product['precio'], 2); ?></p>
                <p class="stock">Stock disponible: <?php echo $product['stock']; ?> unidades</p>
            </div>
        <?php } ?>
    </div>

</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$db->close();
?>
