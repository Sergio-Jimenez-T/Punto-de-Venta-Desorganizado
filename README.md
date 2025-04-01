# 🛒 Sistema de Venta

Este es un sistema de ventas desarrollado en PHP con el fin de realizar un pequeño punto de venta de una tienda de juegos de mesa la cual solicitaba un punto de venta con estilos comodos y facil de utilizar tanto para el cliente y para los administradores.

## 📌 Índice
1. [Estructura Teorica del Proyecto](#estructura-teorica-del-proyecto)
2. [Requisitos](#requisitos)
3. [Instalación](#instalación)
4. [Funcionamiento](#funcionamiento)
5. [Funcionalidades](#funcionalidades)
6. [Contacto](#contacto)

---

## 📂 Estructura Teorica del Proyecto

```
SistemaVenta/
│── controllers/       # Controladores del sistema
│   ├── actualizarCarrito.php
│   ├── addCarrito.php
│   ├── addProducto.php
│   ├── addUsuario.php
│   ├── efectuar_compra.php
│   ├── eliminar.php
│   ├── envio_correo.php
│   ├── generar_ticket.php
│── models/            # Modelos y conexión a la base de datos
│   ├── conexion.php
│   ├── bitacora.php
│   ├── datos.php
│   ├── imagen.php
│── views/             # Vistas del sistema
│   ├── admin_dashboard.php
│   ├── carrito.php
│   ├── dashboard.php
│   ├── detalles_producto.php
│   ├── editar.php
│   ├── grafica.html
│   ├── graficar.js
│   ├── logeo.html
│   ├── includes/
│   │   ├── addUsuarioForm.html
│── public/            # Archivos públicos (CSS, JS, imágenes)
│   ├── assets/
│── PHPMailer-master/  # Librería para envío de correos
│── TCPDF-main/        # Librería para generar PDFs
│── language/          # Archivos de idioma
│── tableshop.sql      # Base de datos del sistema
│── ticket_de_compra.pdf
│── index.php          # Punto de entrada principal
│── README.md          # Documentación del proyecto
```

---

## 🔧 Requisitos
- PHP 7.4+
- Servidor Apache
- MySQL
- Extensiones de PHP: `mysqli`, `mbstring`, `gd`

---

## 🚀 Instalación
1. Clonar este repositorio o descargar los archivos.
2. Configurar la base de datos importando `tableshop.sql` en MySQL.
3. Modificar `models/conexion.php` con los datos de acceso a la base de datos.
4. Ejecutar el servidor Apache y acceder a `index.php` desde el navegador.

---

## 🛠️ Funcionamiento
El sistema permite gestionar ventas, productos y clientes de manera eficiente. Su flujo de trabajo es el siguiente:
1. **Inicio de sesión:** Los usuarios pueden iniciar sesión desde `logeo.html`.
2. **Administración de productos:** Desde `admin_dashboard.php`, los administradores pueden agregar, editar o eliminar productos.
3. **Carrito de compras:** Los clientes pueden agregar productos al carrito desde `carrito.php` y proceder al pago.
4. **Generación de tickets:** Tras confirmar la compra, se genera un ticket en PDF con `generar_ticket.php`.
5. **Envío de correos:** Se envía una confirmación de compra por email con `envio_correo.php`.

---

## ✨ Funcionalidades
✔️ Gestión de productos, clientes y ventas.
✔️ Carrito de compras dinámico.
✔️ Generación de tickets en PDF.
✔️ Envío automático de correos de confirmación.
✔️ Reportes y gráficos de ventas.
✔️ Interfaz amigable y fácil de usar.

---

## 📩 Contacto
Si tienes dudas o sugerencias, puedes contribuir al proyecto o contactarme. 

💡 ¡Tu retroalimentación es bienvenida! 😊

