<?php
/**
 * Esta Función sirve para recoger todos los datos de cara a mostrar el perfil del usuario 
 */

require_once __DIR__.'/../commonFunctions.php';
require_once __DIR__.'/../../database/db.php';

// Implementamos CORS para las consultas del frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

iniciarSesionSiNoActiva(); // Usaremos la variable $_SESSION

if ($_SERVER['REQUEST_METHOD']!=='GET') { // Lo correcto es usar GET
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "Método no permitido, usa GET"]); // Deberíamos redirigir de vuelta al index
    exit;
}
// Comprobamos que el usuario esté logueado para poder usar la sesión
if (!isset($_SESSION["user_id"]) || !isset($_SESSION['role'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "No has iniciado sesión"]);
    exit;
}

// Unimos los contenidos de las tablas users y users_data
$sql = 'SELECT u.id AS user_id, u.name, u.email, u.role, ud.theme, ud.language
        FROM users u JOIN users_data ud ON u.id = ud.id
        WHERE u.id = ?'; // Este necesité pensarlo
$result = ejecutarQuery($sql, [$_SESSION["user_id"]]);
// Comprobamos que haemos recogido el usuario
if (empty($result)) {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "No se ha encontrado al usuario con ese id"]);
    exit;
}
// Si hay usuario lo devolvemos
http_response_code(200); // OK
    echo json_encode([
        "message" => "Usuario obtenido con éxito",
        "data" => $result[0]
    ]);
    exit;
?>