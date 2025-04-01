<?php 
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: logeo.html');
    exit;
}
date_default_timezone_set('America/Mexico_City');
// Conectar a la base de datos
$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Selección de tabla
$tabla = isset($_GET['tabla']) && in_array($_GET['tabla'], ['usuarios', 'bitacora', 'productos', 'compras']) 
    ? $_GET['tabla'] 
    : 'usuarios';

// Obtener datos de la tabla
if ($tabla == 'compras') {
    $query = "SELECT compras.id AS compra_id, usuarios.nombre AS usuario, productos.nombre AS producto, 
                     detalles_compras.cantidad, compras.fecha_hora, compras.estado 
              FROM compras
              JOIN usuarios ON compras.id_usuario = usuarios.id
              JOIN detalles_compras ON compras.id = detalles_compras.id_compra
              JOIN productos ON detalles_compras.id_producto = productos.id";
} else {
    $query = "SELECT * FROM $tabla";
}

$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            overflow-x: hidden;  /* Eliminar barra de desplazamiento horizontal */
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
            word-wrap: break-word;
        }

        nav {
            text-align: center;
            margin-bottom: 20px;
        }

        nav a {
            margin: 0 10px;
            color: #007bff;
            text-decoration: none;
            font-size: 1rem;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* Estilo para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        td img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }

        .actions a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        /* Contenedor para la tabla con scroll */
        .table-container {
            max-height: 400px;  /* Ajusta la altura según lo necesario */
            overflow-y: auto;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        /* Estilo para la descripción */
        textarea {
            resize: none; /* Evita que se redimensione */
            max-height: 150px; /* Establece un límite de altura */
            overflow-y: auto; /* Agrega scroll si el contenido excede la altura */
        }

        form {
            margin: 20px 0;
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dos columnas */
            gap: 20px;
            align-items: flex-start;
        }

        form input, form select, form button, form textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            align-self: flex-end;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Estilo del botón para regresar */
        .btn-regresar {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-regresar:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Dashboard - <?php echo ucfirst($tabla); ?></h1>

    <nav>
        <a href="?tabla=usuarios">Usuarios</a> |
        <a href="?tabla=bitacora">Bitácora</a> |
        <a href="?tabla=productos">Productos</a> |
        <a href="?tabla=compras">Compras</a>
    </nav>

    <a href="logeo.html" class="btn-regresar">Regresar al Login</a>

    <!-- Mostrar tabla -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <?php while ($field = $result->fetch_field()) { ?>
                        <th><?php echo $field->name; ?></th>
                    <?php } ?>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <?php foreach ($row as $key => $value) { ?>
                            <td>
                                <?php if ($key === 'imagen') { ?>
                                    <img src="imagen.php?id=<?php echo $row['id']; ?>" alt="Producto" />
                                <?php } else { ?>
                                    <?php echo htmlspecialchars($value); ?>
                                <?php } ?>
                            </td>
                        <?php } ?>
                        <td class="actions">
                            <a href="editar.php?id=<?php echo urlencode($row['id']); ?>&tabla=<?php echo $tabla; ?>">Editar</a> |
                            <a href="eliminar.php?id=<?php echo urlencode($row['id']); ?>&tabla=<?php echo $tabla; ?>" onclick="return confirm('¿Seguro que deseas eliminar este registro?');">Eliminar</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Formulario para agregar usuarios -->
    <?php if ($tabla == 'usuarios') { ?>
        <h2>Agregar Nuevo Usuario</h2>
        <form action="addUsuario.php" method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="number" name="edad" placeholder="Edad" required>
            <select name="admin" required>
                <option value="0">Usuario Normal</option>
                <option value="1">Administrador</option>
            </select>
            <select name="status" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>
            <button type="submit">Crear Usuario</button>
        </form>
    <?php } ?>

    <!-- Formulario para agregar productos -->
    <?php if ($tabla == 'productos') { ?>
        <h2>Agregar Nuevo Producto</h2>
        <form action="addProducto.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="nombre" placeholder="Nombre del producto" required>
            <input type="number" step="0.01" name="precio" placeholder="Precio" required>
            <input type="number" name="stock" placeholder="Cantidad en stock" required>
            <textarea name="descripcion" placeholder="Descripción" required></textarea>
            <input type="file" name="imagen" required>
            <button type="submit">Crear Producto</button>
        </form>
    <?php } ?>
	<?php if ($tabla == 'compras') { ?>
        <h2>Grafica de ventas</h2>
        <form action="grafica.html" method="POST" enctype="multipart/form-data">
            <button type="submit">Crear grafica</button>
        </form>
    <?php } ?>
</div>

</body>
</html>

<?php
$db->close();
?>
