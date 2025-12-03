<?php
ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permite acceso desde cualquier origen
header("Access-Control-Allow-Headers: Content-Type"); // Permite el encabezado Content-Type
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

// Incluir la configuración de la conexión a la base de datos
include "config.php";

// Obtener la API Key de Wheel of Names de las variables de entorno de Railway
$wheel_api_key = getenv('WHEEL_OF_NAMES_KEY');
$wheel_api_url = "https://wheelofnames.com/api/v2/wheels";

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

// --------------------------------------------------------------------
// NUEVA SECCIÓN: MANEJO DE SOLICITUDES DE LA RULETA (POST)
// --------------------------------------------------------------------
if ($method === "POST" && isset($input["action"]) && $input["action"] === "generate_wheel") {
    
    // 1. Verificar la API Key
    if (!$wheel_api_key) {
        http_response_code(500);
        echo json_encode(["error" => "La clave WHEEL_OF_NAMES_KEY no está configurada en el servidor."]);
        exit();
    }
    
    // 2. Obtener los nombres desde la Base de Datos
    $result = $conn->query("SELECT nombre FROM ruleta WHERE disponibilidad = 1");
    $names = [];

    if ($result) {
        while($row = $result->fetch_assoc()){
            // La API de Wheel of Names espera un arreglo de cadenas (strings)
            $names[] = [
                "text" => $row['nombre'] 
            ];
        }
    }
    

    // 3. Crear el cuerpo de la solicitud para Wheel of Names
    $payload = [
        "wheelConfig" => [
            // --- PROPIEDADES ESENCIALES ---
            "title" => "Ruleta Dinámica",
            "description" => "Opciones generadas desde la base de datos de Railway.",
            "entries" => $names, // Arreglo con los objetos {"text": "Nombre"}
            
            // --- AJUSTES DE ESTILO SOLICITADOS ---
            "pageBackgroundColor" => "#F8F8F8", 
            "displayWinnerDialog" => true,
            "launchConfetti" => true,
            "drawShadow" => true,
            "drawOutlines" => true,
            "centerText" => "¡Gira Ahora!",
            
            // Ajuste de Fuente
            "fontSettings" => [
                "fontSize" => 15 
            ],

            // --- PROPIEDADES QUE PUEDEN SER CLAVE PARA ESTILO/COMPORTAMIENTO ---
            "isAdvanced" => true,  // Habilitar si usas alguna propiedad avanzada (como fontSettings o colorSettings)
            "allowDuplicates" => true, // Permitir duplicados si es necesario (el valor por defecto debería ser True)
        ],
        
        // --- PROPIEDADES DEL NIVEL SUPERIOR (FUERA DE wheelConfig) ---
        // La propiedad 'shareMode' a veces es requerida y debe ir fuera de wheelConfig
        "shareMode" => "private" 
    ];
    
    // 4. Configurar y ejecutar la solicitud cURL a la API externa
    $ch = curl_init($wheel_api_url);
    
    // Cabeceras HTTP requeridas por la API (Autorización y Tipo de Contenido)
    $headers = [
        "x-api-key: " . $wheel_api_key,
        "Content-Type: application/json"
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devolver la transferencia como una cadena
    curl_setopt($ch, CURLOPT_POST, true);           // Configurar como solicitud POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // Adjuntar los datos JSON
    
    $api_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 5. Manejar la respuesta de la API externa
    if ($http_code === 201 || $http_code === 200) { 
        // Decodificar la respuesta JSON de Wheel of Names
        $response_data = json_decode($api_response, true); 
        
        if (isset($response_data['data']['path'])) {
            $wheel_path = $response_data['data']['path'];
            // Construir la URL completa para que el frontend la use
            $final_url = "https://wheelofnames.com/es/".$wheel_path; 
            
            http_response_code(200);
            // Devolver un JSON fácil de usar con la URL completa
            echo json_encode([
                "success" => true,
                "url" => $final_url,
                "path" => $wheel_path
            ]);
            
        } else {
            // Error si no se encuentra la clave 'path'
            http_response_code(500); 
            echo json_encode(["error" => "Respuesta inesperada de la API de Wheel of Names."]);
        }
    } else {
        // Manejar errores de la API externa (ej. 401, 404, etc.)
        http_response_code(500);
        echo json_encode([
            "error" => "Error al crear la ruleta en Wheel of Names.",
            "http_code" => $http_code,
            "api_response" => json_decode($api_response, true)
        ]);
    }
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
        ob_end_flush();
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
