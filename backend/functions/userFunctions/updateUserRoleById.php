<?php
/**
 * Esta Función recoge todos los usuarios de la aplicación
 */

require_once __DIR__.'/../commonFunctions.php';
require_once __DIR__.'/../../database/db.php';

// Implementamos CORS para las consultas del frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

iniciarSesionSiNoActiva(); // Usaremos la variable $_SESSION

if ($_SERVER['REQUEST_METHOD']!=='POST') {
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "Método no permitido, usa POST"]);
    exit;
}

// Comprobamos que el usuario esté logueado para poder usar la sesión
if (!isset($_SESSION["user_id"]) || !isset($_SESSION['role'])) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        "message" => "No has iniciado sesión"
    ]);
    exit;
}

// Si el usuario logueado no es Admin salimos del programa
if ($_SESSION['role'] !== 2) {
    http_response_code(403); // Forbidden
    echo json_encode([ 
        'message' => 'El usuario no es administrador'
    ]);
    exit;
}

$input = file_get_contents('php://input'); // Recogemos los datos en JSON
$data = json_decode($input, true);// Decodificamos los datos

if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
    http_response_code(400); // Bad request
    echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

$userId = $data['userId'] ?? null;
$currentRole = $data['role'] ?? null;

// Comprobamos que los datos existan
if (!$userId || !$currentRole) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Faltan datos: userId o role"]);
    exit;
}

if ($_SESSION['user_id'] === $userId) {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "No puedes modificar tu propio role"]);
    exit;
}

$newRole = 0;

if ($currentRole === 1) {
    $newRole = 2;
} elseif ($currentRole === 2) {
    $newRole = 1;
} else {
    http_response_code(400); // Bad request
    echo json_encode(["message" => "El usuario tiene un role inválido"]);
    exit;
}

// Unimos los contenidos de las tablas users y users_data
$sql = 'UPDATE users SET role = ? WHERE id = ?';
$result = ejecutarQuery($sql, [$newRole, $userId]);

if ($result === 0) {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "Usuario no encontrado o rol no modificado"]);
    exit;
}

// Si hay usuario lo devolvemos
http_response_code(200); // OK
echo json_encode([
    'message' => 'Role actualizado con éxito'
]);
exit;
?>