<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: logeo.html');
    exit;
}

// Conexión a la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'tableshop');

// Verificar conexión
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}
date_default_timezone_set('America/Mexico_City');
// Verificar que se haya proporcionado un ID y la tabla
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tabla']) || !in_array($_GET['tabla'], ['usuarios', 'productos', 'compras'])) {
    die('ID o tabla no válido.');
}

$id = $_GET['id'];
$tabla = $_GET['tabla'];

// Obtener los datos del registro
if ($tabla == 'usuarios') {
    $query = "SELECT * FROM usuarios WHERE id = ?";
} elseif ($tabla == 'productos') {
    $query = "SELECT * FROM productos WHERE id = ?";
} elseif ($tabla == 'compras') {
    $query = "SELECT compras.id, compras.id_usuario, compras.fecha_hora, compras.estado, detalles_compras.id_producto, detalles_compras.cantidad 
              FROM compras 
              JOIN detalles_compras ON compras.id = detalles_compras.id_compra 
              WHERE compras.id = ?";
}

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Registro no encontrado.');
}

$registro = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($tabla == 'usuarios') {
        // Datos de usuario
        $nombre = $_POST['nombre'];
        $username = $_POST['usuario'];
        $password = $_POST['contraseña'];
        $correo = $_POST['correo'];
        $edad = $_POST['edad'];
        $admin = $_POST['admin'];
        $status = $_POST['status'];
        $fecha_actualizacion = date('Y-m-d H:i:s');

        // Encriptar contraseña si fue cambiada
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, contraseña = ?, correo = ?, edad = ?, admin = ?, status = ?, fecha_actualizacion = ? WHERE id = ?");
            $stmt->bind_param("ssssisssi", $nombre, $username, $hashed_password, $correo, $edad, $admin, $status, $fecha_actualizacion, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, correo = ?, edad = ?, admin = ?, status = ?, fecha_actualizacion = ? WHERE id = ?");
            $stmt->bind_param("ssssissi", $nombre, $username, $correo, $edad, $admin, $status, $fecha_actualizacion, $id);
        }

    } elseif ($tabla == 'productos') {
        // Datos de producto
        $nombre_producto = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
	$fecha_actualizacion = date('Y-m-d H:i:s');  // Fecha y hora actual
       	if (!empty($_FILES['imagen']['tmp_name'])) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        $stmt = $mysqli->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ?, fecha_actualizacion = ? WHERE id = ?");
        $stmt->bind_param("ssdisss", $nombre_producto, $descripcion, $precio, $stock, $imagen, $fecha_actualizacion, $id);
   	 } else {
        $stmt = $mysqli->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, fecha_actualizacion = ? WHERE id = ?");
        $stmt->bind_param("ssdisi", $nombre_producto, $descripcion, $precio, $stock, $fecha_actualizacion, $id);
    	}

    } elseif ($tabla == 'compras') {
        // Datos de compra
        $id_usuario = $_POST['id_usuario'];
        $id_producto = $_POST['id_producto'];
        $cantidad = $_POST['cantidad'];
        $fecha_hora = $_POST['fecha_hora'];
        $estado = $_POST['estado'];

        // Actualizar la tabla compras
        $stmt = $mysqli->prepare("UPDATE compras SET id_usuario = ?, fecha_hora = ?, estado = ? WHERE id = ?");
        $stmt->bind_param("issi", $id_usuario, $fecha_hora, $estado, $id);
        $stmt->execute();

        // Actualizar la tabla detalles_compras
        $stmt = $mysqli->prepare("UPDATE detalles_compras SET id_producto = ?, cantidad = ? WHERE id_compra = ?");
        $stmt->bind_param("iii", $id_producto, $cantidad, $id);
    }

    if ($stmt->execute()) {
        echo ucfirst($tabla) . " actualizado exitosamente.";
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error al actualizar " . $tabla . ": " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar <?php echo ucfirst($tabla); ?></title>
	<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input, select, textarea {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="file"] {
            padding: 5px;
        }

        button {
            padding: 10px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .back-button {
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        img {
            margin-top: 10px;
            width: 100px;
            height: auto;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Editar <?php echo ucfirst($tabla); ?></h1>
    <form action="" method="POST" enctype="multipart/form-data">
    <?php if ($tabla == 'usuarios') { ?>
        <!-- Campos para editar usuario -->
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($registro['nombre']); ?>" required>
        <input type="text" name="usuario" value="<?php echo htmlspecialchars($registro['usuario']); ?>" required>
        <input type="password" name="contraseña" placeholder="Nueva contraseña">
        <input type="email" name="correo" value="<?php echo htmlspecialchars($registro['correo']); ?>" required>
        <input type="number" name="edad" value="<?php echo htmlspecialchars($registro['edad']); ?>" required>
        <select name="admin">
            <option value="0" <?php if ($registro['admin'] == 0) echo 'selected'; ?>>Usuario</option>
            <option value="1" <?php if ($registro['admin'] == 1) echo 'selected'; ?>>Administrador</option>
        </select>
        <select name="status">
            <option value="1" <?php if ($registro['status'] == 1) echo 'selected'; ?>>Activo</option>
            <option value="0" <?php if ($registro['status'] == 0) echo 'selected'; ?>>Inactivo</option>
        </select>
    <?php } elseif ($tabla == 'productos') { ?>
        <!-- Campos para editar producto -->
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($registro['nombre']); ?>" required>
        <textarea name="descripcion" required><?php echo htmlspecialchars($registro['descripcion']); ?></textarea>
        <input type="number" name="precio" value="<?php echo htmlspecialchars($registro['precio']); ?>" required>
        <input type="number" name="stock" value="<?php echo htmlspecialchars($registro['stock']); ?>" required>

        <!-- Imagen del producto -->
        <?php if ($registro['imagen']) { ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($registro['imagen']); ?>" alt="Imagen del Producto">
        <?php } ?>
        <input type="file" name="imagen">
    <?php } elseif ($tabla == 'compras') { ?>
        <!-- Campos para editar compra -->
        <input type="number" name="id_usuario" value="<?php echo $registro['id_usuario']; ?>" required>
        <input type="number" name="id_producto" value="<?php echo $registro['id_producto']; ?>" required>
        <input type="number" name="cantidad" value="<?php echo $registro['cantidad']; ?>" required>
        <input type="datetime-local" name="fecha_hora" value="<?php echo str_replace(' ', 'T', $registro['fecha_hora']); ?>" required>
        <select name="estado">
            <option value="pendiente" <?php if ($registro['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
            <option value="completado" <?php if ($registro['estado'] == 'completado') echo 'selected'; ?>>Completado</option>
        </select>
    <?php } ?>

    <button type="submit">Guardar Cambios</button>
</form>

</body>
</html>
