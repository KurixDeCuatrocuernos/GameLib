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

// Unimos los contenidos de las tablas users y users_data
$sql = 'SELECT u.id, u.name, u.email, u.role, r.name AS role_name
        FROM users u 
        JOIN roles r ON u.role = r.id
        WHERE u.id != ?
        ORDER BY u.id ASC';
$results = ejecutarQuery($sql, [$_SESSION['user_id']]);

// Comprobamos que hemos recogido el usuario
if (empty($results)) {
    http_response_code(404); // Not Found
    echo json_encode([
        "message" => "No se han encontrado usuarios"
    ]);
    exit;
}

// Si hay usuario lo devolvemos
http_response_code(200); // OK
echo json_encode($results); // Devolvemos el array de usuarios directamente
exit;
?>