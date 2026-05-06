<?php
    
require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';
require_once __DIR__.'/./gameFunctions.php';

// Implementamos CORS para las consultas del frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "Método no permitido, usa DELETE"]); // Deberíamos redirigir de vuelta al index
    exit;
}

$input = file_get_contents('php://input'); // Recogemos los datos con fetch
$data = json_decode($input, true); // decodificamos los datos de json

// Comprobamos que los datos sean de tipo JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad request
    echo json_encode([
        "message" => "Los datos enviados no están en formato JSON"
    ]);
    exit;
}

// Comprobamos que los datos existan
if (empty($data)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "Alguno de los parámetros requeridos está vacío"
    ]);
    exit;
}

// Comprobamos que los datos tengan contenido
if (!isset($data['id'])
) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "No se ha recibido id"
    ]);
    exit;
}

if (!filter_var($data['id'], FILTER_VALIDATE_INT)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "El id no tiene el tipo adecuado"
    ]);
    exit;
}

// Si estamos aquí, borramos
$game = getGameById($data['id']);
if (empty($game)) {
    http_response_code(404); // Not Found
    echo json_encode([
        "message" => "No se ha encontrado el juego, imposible borrar"
    ]);
    exit;
}

// Borramos el juego
$sql = 'DELETE FROM games WHERE id = ?';
ejecutarQuery($sql, [$data['id']]);

http_response_code(200); // OK
echo json_encode([
    "message" => "Juego borrado con éxito"
]);
exit;
?>