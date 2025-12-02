<?php
// config.php

// 1. Recupera las variables de entorno de Railway
$servername = getenv('MYSQL_HOST');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$dbname = getenv('MYSQL_DATABASE');
$port = getenv('MYSQL_PORT'); // El puerto es un número (ej: 3306)

// 2. Comprueba si las variables están vacías (si Railway falló en inyectarlas)
if (empty($servername) || empty($username) || empty($password) || empty($dbname)) {
    // Si faltan, es un error de configuración de Railway.
    error_log("Faltan variables de entorno de MySQL.");
    // Podrías devolver un error 500 aquí si quieres, o simplemente dejar que falle la conexión.
}

// 3. Crear conexión usando las variables de entorno
$conn = new mysqli($servername, $username, $password, $dbname, (int)$port);

// 4. Verificar conexión (opcional, pero buena práctica)
if ($conn->connect_error) {
    // Si falla la conexión (pero encuentra el host) saldrá un error diferente
    die("Connection failed: " . $conn->connect_error);
}

// Opcional: Establecer el juego de caracteres
$conn->set_charset("utf8mb4");

?>
