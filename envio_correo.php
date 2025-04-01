<?php
// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Incluir TCPDF
require('TCPDF-main/tcpdf.php');
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado");
}

$id_usuario = $_SESSION['id_usuario'];
$db = new mysqli('localhost', 'root', '', 'tableshop');

if ($db->connect_error) {
    die('Error de conexión: ' . $db->connect_error);
}

// Obtener el correo del usuario
$queryUser = "SELECT correo, nombre FROM usuarios WHERE id = ?";
$stmtUser = $db->prepare($queryUser);
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$destinatario = $userData['correo'] ?? '';
$nombredestinatario = $userData['nombre'] ?? 'Cliente';
$stmtUser->close();

// Si no hay correo, detener el proceso
if (empty($destinatario)) {
    die("No se encontró el correo del usuario.");
}


// Obtener el nombre real del comprador
$queryUser = "SELECT nombre FROM usuarios WHERE id = ?";
$stmtUser = $db->prepare($queryUser);
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$comprador = $resultUser->fetch_assoc()['nombre'] ?? 'Desconocido';
$stmtUser->close();

// Obtener los productos del carrito
$query = "SELECT productos.nombre, productos.imagen, carrito.cantidad, productos.precio 
          FROM carrito 
          JOIN productos ON carrito.id_producto = productos.id 
          WHERE carrito.id_usuario = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

date_default_timezone_set('America/Mexico_City');
$fecha_hora = date('d-m-Y H:i:s');

// Generar un número de tarjeta aleatorio
$tarjeta = "**** **** **** " . rand(1000, 9999);
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('TableShop');
$pdf->SetTitle('Ticket de Compra');

// Establecer márgenes
$pdf->SetMargins(20, 20, 20); // Margen izquierdo, superior y derecho

$pdf->AddPage();

// Definir la opacidad para el patrón
$pdf->SetAlpha(0.1);  // Ajusta la opacidad entre 0 (transparente) y 1 (opaco)

// Crear un patrón de ajedrez simple en el fondo
$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Rect(10, 10, 180, 277, 'F'); // Fondo blanco (ajustado al área útil del PDF)

$size = 10; // Tamaño de cada casilla
$colores = array([0, 0, 0], [255, 255, 255]); // Colores de las casillas (negro, blanco)

// Dibuja el patrón de ajedrez para cubrir todo el fondo
for ($y = 12; $y < 277; $y += $size) {
    for ($x = 5; $x < 200; $x += $size) {
        $color = $colores[($x / $size + $y / $size) % 2];
        $pdf->SetFillColor($color[0], $color[1], $color[2]);
        $pdf->Rect($x, $y, $size, $size, 'F');
    }
}

// Restaurar la opacidad a 1 para el contenido principal
$pdf->SetAlpha(1);

// Ahora, agrega el contenido normal (como el ticket)
$pdf->SetFont('helvetica', 'B', 25);
$pdf->Cell(0, 10, 'TableShop - Tu tienda de juegos de mesa', 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 20);
$pdf->Cell(0, 10, '"Donde en cada juego cuentas tu historia"', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 17);
$pdf->Cell(0, 10, 'Comprador: ' . utf8_decode($comprador), 0, 1, 'L');
$pdf->Cell(0, 10, 'Fecha y Hora: ' . $fecha_hora, 0, 1, 'L');
$pdf->Cell(0, 10, 'Método de pago: VISA ' . $tarjeta, 0, 1, 'L');
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->SetFont('helvetica', 'B', 19);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(35, 7, 'Imagen', 0, 0, 'C', 0);
$pdf->Cell(50, 7, 'Producto', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Cantidad', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Precio', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Subtotal', 1, 1, 'C', 1);

$total = 0;
$pdf->SetFont('helvetica', '', 18);

while ($row = $result->fetch_assoc()) {
    $subtotal = $row['cantidad'] * $row['precio'];
    $total += $subtotal;
    
    if (!empty($row['imagen'])) {
        $imagenData = 'data:image/png;base64,' . base64_encode($row['imagen']);
        $imgHTML = '<img src="' . $imagenData . '" width="115" height="80" />';
        $pdf->writeHTMLCell(35, 25, '', '', $imgHTML, 0, 0, false, true, 'C', true);
    } else {
        $pdf->Cell(35, 25, 'Sin Imagen', 1, 0, 'C');
    }
     $pdf->SetTextColor(0, 51, 102); // Azul oscuro para el texto de productos
    // Usamos MultiCell para que el texto no se desborde
    $pdf->MultiCell(50, 25, utf8_decode($row['nombre']), 1, 'C', false, 0, '', '', true, 0, 0, true);
    $pdf->Cell(30, 25, $row['cantidad'], 1, 0, 'C');
    $pdf->Cell(30, 25, '$' . number_format($row['precio'], 2), 1, 0, 'C');
    $pdf->Cell(30, 25, '$' . number_format($subtotal, 2), 1, 1, 'C');
}

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(140, 10, 'Total:', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($total, 2), 1, 1, 'C');

// Mensaje final de agradecimiento
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 15);
$pdf->Cell(0, 10, 'Gracias por tu compra en TableShop.', 0, 1, 'C');
$pdf->Cell(0, 10, '¡Disfruta tus juegos y vuelve pronto!', 0, 1, 'C');
// Guardar el PDF en una variable
$pdf_content = $pdf->Output('ticket_de_compra.pdf', 'S'); // Guardar como string

// Guardar en el servidor temporalmente (solo cuando se enviará por correo)
$pdf_file_path = __DIR__ . "/ticket_de_compra.pdf"; 
file_put_contents($pdf_file_path, $pdf_content);

// Enviar el PDF por correo con PHPMailer
$mail = new PHPMailer(true);
try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'serpiword7.7@gmail.com';
    $mail->Password = 'ntlkvrjqrzpadnmd'; // Usa una contraseña de aplicación en lugar de esta
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Configuración del remitente y destinatario
    $mail->setFrom('serpiword7.7@gmail.com', 'TableShop');
    $mail->addAddress($destinatario, $nombredestinatario);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Tu Ticket de Compra - TableShop';
    $mail->Body = "Hola <b>$nombredestinatario</b>,<br><br>Gracias por tu compra en TableShop.<br>Adjunto encontrarás tu ticket de compra.<br><br>¡Esperamos verte de nuevo!";
    $mail->CharSet = 'UTF-8';

    // Adjuntar el PDF generado
    $mail->addAttachment($pdf_file_path);

    // Enviar correo
    $mail->send();
    echo 'El correo ha sido enviado a ' . htmlspecialchars($destinatario);

    // Eliminar el archivo PDF temporal
    unlink($pdf_file_path);
} catch (Exception $e) {
    echo 'Error al enviar el correo: ' . $mail->ErrorInfo;
}

$db->close();
?>
