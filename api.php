<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permite acceso desde cualquier origen
header("Access-Control-Allow-Headers: Content-Type"); // Permite el encabezado Content-Type
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

// Incluir la configuración de la conexión a la base de datos
include "config.php";

$method = $_SERVER["REQUEST_METHOD"];
// Decodificar el cuerpo de la solicitud para POST, PUT, DELETE
$input = json_decode(file_get_contents("php://input"), true);

// --- Manejo del Preflight (OPTIONS) --------------------------------
if ($method === "OPTIONS") {
    http_response_code(200);
    exit();
}

// --- Manejo de Errores y Conexión ----------------------------------
if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit();
}

// 1. GET (Listar) ----------------------------------------------------
if ($method === "GET") {
    // Consulta para obtener todos los elementos de la ruleta
    $result = $conn->query("SELECT id, nombre, disponibilidad FROM ruleta ORDER BY id ASC");
    $data = [];

    if ($result) {
        while($row = $result->fetch_assoc()){
            // Asegurarse de que disponibilidad sea un booleano o entero, si es necesario
            $row['disponibilidad'] = (int)$row['disponibilidad'];
            $data[] = $row;
        }
        echo json_encode($data);
        http_response_code(200); // OK
    } else {
        http_response_code(500); // Error interno del servidor
        echo json_encode(["error" => "Error al ejecutar la consulta GET: " . $conn->error]);
    }
    exit();
}

// 2. POST (Crear/Insertar) ------------------------------------------
if ($method === "POST") {
    // Validación básica
    if (!isset($input["nombre"]) || !isset($input["disponibilidad"])) {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(["error" => "Faltan datos requeridos (nombre o disponibilidad)."]);
        exit();
    }
    
    // Preparar la sentencia de inserción
    $stmt = $conn->prepare("INSERT INTO ruleta (nombre, disponibilidad) VALUES (?, ?)");
    $stmt->bind_param("si", $input["nombre"], $input["disponibilidad"]);
    
    if ($stmt->execute()) {
        http_response_code(201); // Creado
        echo json_encode(["message" => "Opción Agregada con éxito.", "id" => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al agregar la opción: " . $stmt->error]);
    }
    $stmt->close();
    exit();
}

// 3. PUT (Modificar/Actualizar) ---------------------------------------
if ($method === "PUT") {
    // Validación básica de datos y ID
    if (!isset($input["id"]) || !isset($input["nombre"]) || !isset($input["disponibilidad"])) {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos requeridos (id, nombre, o disponibilidad)."]);
        exit();
    }
    
    // Preparar la sentencia de actualización
    $stmt = $conn->prepare("UPDATE ruleta SET nombre=?, disponibilidad=? WHERE id=?");
    // Tipos: s (string), i (integer), i (integer)
    $stmt->bind_param("sii", $input["nombre"], $input["disponibilidad"], $input["id"]);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode(["message" => "Opción Modificada con éxito."]);
        } else {
            http_response_code(404); // No encontrado
            echo json_encode(["message" => "No se encontró la opción con el ID especificado para modificar."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al modificar la opción: " . $stmt->error]);
    }
    $stmt->close();
    exit();
}

// 4. DELETE (Eliminar) ------------------------------------------------
if ($method === "DELETE") {
    // Validación básica de ID
    if (!isset($input["id"])) {
        http_response_code(400);
        echo json_encode(["error" => "Falta el ID para la eliminación."]);
        exit();
    }
    
    // Preparar la sentencia de eliminación
    $stmt = $conn->prepare("DELETE FROM ruleta WHERE id=?");
    $stmt->bind_param("i", $input["id"]);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode(["message" => "Opción Eliminada con éxito."]);
        } else {
            http_response_code(404); // No encontrado
            echo json_encode(["message" => "No se encontró la opción con el ID especificado para eliminar."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al eliminar la opción: " . $stmt->error]);
    }
    $stmt->close();
    exit();
}

// 5. Método no permitido ---------------------------------------------
http_response_code(405); // Método no permitido
echo json_encode(["error" => "Método HTTP no permitido."]);
?>
