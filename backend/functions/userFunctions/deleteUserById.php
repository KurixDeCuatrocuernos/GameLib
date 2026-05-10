<?php
require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

// Implementamos CORS para las consultas del frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Al ser borrado usamos delete
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== 'DELETE') { // Deberíamos usar DELETE en lugar de POST
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "Método no permitido, usa DELETE"]);
    exit;
}

iniciarSesionSiNoActiva(); // Iniciamos sesión para poder usar $_SESSION

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
$data = json_decode($input, true); // Decodificamos los datos

if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
    http_response_code(400); // Bad request
    echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

if (!isset($data["id"]) || $data["id"] === '') {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "ID requerido"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

$id = $data["id"];

// Comprobamos que el id es un número
if (!is_numeric($id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "ID inválido"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

$id = (int)$id;

// Evitamos que el administrador se elimine a sí mismo desde aquí
if ($id === $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(["message" => "No puedes eliminar tu propia cuenta"]);
    exit;
}

$sql = 'DELETE FROM users WHERE id = ?';
try{
    ejecutarQuery($sql, [$id]); // En teoría la base de datos borrará los datos en cascada
    http_response_code(200); // OK
    echo json_encode(["message" => "Usuario con id: $id borrado con éxito"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
} catch (Exception $ex) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error Interno, hubo un error al borrar el usuario, pero no es tu culpa"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

?>