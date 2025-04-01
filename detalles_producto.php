<?php
session_start(); // Iniciar la sesión

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];  // Asegúrate de que esté configurado correctamente.

$db = new mysqli('localhost', 'root', '', 'tableshop');
if ($db->connect_error) {
    die('Error de conexión a la base de datos: ' . $db->connect_error);
}

// Actualizar la última actividad del usuario
$_SESSION['last_activity'] = time();

// Obtener el nombre de usuario desde la sesión
$username = $_SESSION['username'] ?? 'usuario';

// Obtener el id_usuario del nombre de usuario
$query = "SELECT id FROM usuarios WHERE usuario = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $username); // 's' para string
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['id_usuario'] = $user['id'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    exit();
}


// Obtener el ID del producto desde la URL
$productId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;
if (!$productId) {
    echo "ID de producto no válido";
    exit;
}
// Obtener productos relacionados, excluyendo el actual
$queryRelated = "SELECT id, nombre, imagen, precio, stock FROM productos WHERE id != ? ORDER BY RAND() LIMIT 5";
$stmtRelated = $db->prepare($queryRelated);
$stmtRelated->bind_param("i", $productId);
$stmtRelated->execute();
$resultRelated = $stmtRelated->get_result();
$relatedProducts = $resultRelated->fetch_all(MYSQLI_ASSOC);

// Obtener el detalle del producto desde la base de datos
$query = "SELECT id, nombre, imagen, precio, stock, descripcion FROM productos WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Producto no encontrado";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto</title>
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
	
/* Estilos de la sección de detalles */
	.product-details {
	    max-width: 1000px;
	    margin-top: 140px;
	    margin-left: 345px;
	    padding: 20px;
	    background: rgba(255, 255, 255, 0.9);
	    border-radius: 20px;
	    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
	    display: flex;
	    justify-content: space-between;
	    align-items: center;
	}

.product-image img {
    width: 100%;
    height: auto;
    max-width: 350px;
    border-radius: 8px;
}

.product-info {
    flex: 1;
    margin-left: 20px;
	font-size: 30px;
}

.product-info h2 {
    color: #333;
    margin-bottom: 10px;
	font-size: 60px;
}

.product-info .price {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007BFF;
	font-size: 30px;
}

.product-info .stock {
    color: #28a745;
}

/* Estilo de los productos relacionados */
.related-products {
    margin-top: 25px;
    background-color: rgba(255, 255, 255, 0);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
}

.related-products h3 {
    text-align: center;
    color: cyan;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 20px;
}

.product-list {
    display: flex;
    overflow-x: 50%;
    gap: 20px;
}

.product-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 180px;
    text-align: center;
    padding: 20px;
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
    margin-bottom: 20px;
}

.product-card:hover {
    transform: scale(1.3);
}

.product-card img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.product-card h4 {
    font-size: 1.1rem;
    margin: 10px 0;
}

.product-card p {
    color: #007BFF;
    font-weight: bold;
}
/* Estilo del botón "Agregar al carrito" */
.add-to-cart-btn {
    background-color: #28a745; /* Un color verde para destacar */
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 1rem;
    margin-top: 20px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.add-to-cart-btn:hover {
    background-color: #218838; /* Color más oscuro al pasar el ratón */
}
/* Contenedor de productos */
.related-products {
    overflow: hidden;
    position: relative;
    width: 97%;
}

.product-list {
    display: flex;
    gap: 160px;
    white-space: nowrap;
    transition: transform 0.5s ease-in-out;
}

.product-card {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 180px;
    text-align: center;
    padding: 20px;
    cursor: pointer;
    margin-bottom: 20px;
    flex-shrink: 0;
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}

/* Animación para cada producto */
@keyframes moveProduct {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    70% {
        transform: translateX(-15%); /* Se mueve más antes de desaparecer */
        opacity: 1;
    }
    80% {
        opacity: 0; /* Se oculta antes de llegar al final */
    }
    100% {
        transform: translateX(220%); /* Aparece en el otro lado */
        opacity: 1; /* Se vuelve a mostrar */
    }
}
    </style>
</head>
<body>
	<div class="navbar">
        <h2>Te gusta este producto <?php echo htmlspecialchars($username); ?>? talvez te gusten tambien los productos de abajo👇🏼👇🏼</h2>
        <nav>
            <a href="dashboard.php">🏠 Página Principal 🔙</a>
            <a href="carrito.php">🛒 Carrito</a>
            <a href="logeo.html" class="btn-regresar">❌Regresar al Login❌</a>
        </nav>
    </div>
	
   <div class="product-details">
    <div class="product-image">
        <img id="productImage" src="imagen.php?id=<?php echo $product['id']; ?>" alt="Imagen del producto">
    </div>
    <div class="product-info">
        <h2 id="productName"><?php echo htmlspecialchars($product['nombre']); ?></h2>
        <p><strong>Descripción:</strong> <span id="productDesc"><?php echo htmlspecialchars($product['descripcion']); ?></span></p>
        <p class="price">Precio: $<span id="productPrice"><?php echo number_format($product['precio'], 2); ?></span></p>
        <p class="stock">Stock disponible: <span id="productStock"><?php echo $product['stock']; ?></span> unidades</p>
       <input type="number" id="cantidad" value="1" min="1">
<button onclick="agregarAlCarrito(<?php echo $product['id']; ?>)" class="add-to-cart-btn">Agregar al carrito</button>

<p id="mensajeCarrito" style="color: green; display: none;"></p>



		</div>
</div>
<!-- Productos relacionados al final de la página -->
<div class="related-products">
    <h3>Otros productos disponibles</h3>
    <div class="product-list">
        <?php 
// Obtener los productos relacionados sin duplicarlos
foreach ($relatedProducts as $relatedProduct) { ?>
    <div class="product-card" onclick="showProductDetails(<?php echo $relatedProduct['id']; ?>)">
        <img src="imagen.php?id=<?php echo $relatedProduct['id']; ?>" alt="Imagen de producto">
        <h4><?php echo htmlspecialchars($relatedProduct['nombre']); ?></h4>
        <p>Precio: $<?php echo number_format($relatedProduct['precio'], 2); ?></p>
    </div>
<?php } ?>

    </div>
</div>

 <script>
        document.addEventListener("DOMContentLoaded", function() {
            const productList = document.querySelector(".product-list");
            const products = Array.from(document.querySelectorAll(".product-card"));

            function moveProduct() {
                if (products.length === 0) return;

                const firstProduct = products.shift(); // Remueve el primero de la lista

                firstProduct.style.transform = "translateX(-100%)";
                firstProduct.style.opacity = "0";

                setTimeout(() => {
                    firstProduct.style.transform = "translateX(100%)";
                    productList.appendChild(firstProduct);

                    setTimeout(() => {
                        firstProduct.style.opacity = "1";
                        firstProduct.style.transform = "translateX(0)";
                    }, 500);

                    products.push(firstProduct);

                }, 1000);
            }

            setInterval(moveProduct, 3000);
        });

        function agregarAlCarrito(idProducto) {
    let cantidad = document.getElementById("cantidad").value;
    if (cantidad < 1) {
        alert("La cantidad debe ser al menos 1.");
        return;
    }

    let formData = new URLSearchParams();
    formData.append("id", idProducto); 
    formData.append("cantidad", cantidad);

    console.log("Enviando:", formData.toString()); 

    fetch('agregar_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.text()) 
    .then(data => {
        console.log("Respuesta del servidor:", data); 

        try {
            const jsonData = JSON.parse(data);
            let mensaje = document.getElementById("mensajeCarrito");
            if (jsonData.status === "success") {
                mensaje.style.color = "green";
                mensaje.innerText = "Producto agregado al carrito";
            } else {
                mensaje.style.color = "red";
                mensaje.innerText = "Error: " + jsonData.message;
            }
            mensaje.style.display = "block";
            setTimeout(() => { mensaje.style.display = "none"; }, 3000);
        } catch (error) {
            console.error('Error al procesar la respuesta:', error);
            let mensaje = document.getElementById("mensajeCarrito");
            mensaje.style.color = "red";
            mensaje.innerText = "Error al agregar el producto al carrito: " + data;
            mensaje.style.display = "block";
            setTimeout(() => { mensaje.style.display = "none"; }, 3000);
        }
    })
    .catch(error => console.error('Error:', error));
}

    </script>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$db->close();
?>
