<?php
// Recupera las variables de entorno de Railway
$servername = getenv('MYSQL_HOST');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$dbname = getenv('MYSQL_DATABASE');
$port = getenv('MYSQL_PORT');

// Crear conexión usando las variables de entorno
// El puerto debe pasarse como un parámetro separado en la mayoría de los casos
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Opcional: Establecer el juego de caracteres
$conn->set_charset("utf8mb4");
?>
