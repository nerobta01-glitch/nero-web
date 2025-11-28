<?php
// CONFIGURACIÓN NERO · AJUSTA ESTO A TU SERVIDOR

// Datos de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'nerostore_tienda');
define('DB_USER', 'nerostore_user_tienda');
define('DB_PASS', ';@eq0DZ.[2,M'); // cámbiala

// Credenciales de administrador (login)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'nero2025'); // cámbiala

// Ruta física donde se guardan las imágenes subidas
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');

// URL base para acceder a esas imágenes desde el navegador
// Ejemplo: si tu sitio está en https://tusitio.com y /uploads cuelga de la raíz:
define('UPLOAD_URL_BASE', '/uploads/');
