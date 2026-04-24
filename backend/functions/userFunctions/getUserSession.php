<?php
/**
 * Esta es la función para iniciar sesión
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

// Habilitamos CORS para REACT:
header("Access-Control-Allow-Origin: http://localhost:5173"); // Dirección de React
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Revisamos si la petición es Options (para probar la conexión)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200); // OK
    exit;
}

// Salimos si ya hay sesión activa: REDIRIGIR AL FRONT
// salirSiHaySesion("http://localhost:5173/dashboard"); // La comentaremos mientras desarrollamos el Frontend 

if ($_SERVER["REQUEST_METHOD"] !== 'GET') { // Si no hay POST salimos
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "message" => "Método no permitido, se esperaba GET y se recibió: ".$_SERVER['REQUEST_METHOD']
    ]); // Deberíamos redirigir de vuelta al index
    exit;
}

iniciarSesionSiNoActiva();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Forbidden
    echo json_encode([
        "message" => "No Hay Sesión"
    ]); // Redirigimos al formulario y damos feedback GENÉRICO                    
    exit;
}

$sql = 'SELECT u.name as username, r.name as role_name 
        FROM users u 
        JOIN roles r ON u.role = r.id
        WHERE u.id = ? 
        LIMIT 1';
$result = ejecutarQuery($sql, [$_SESSION['user_id']]);

if (empty($result[0])) {
    http_response_code(401); // Forbidden
    echo json_encode([
        "message" => "Usuario no encontrado"
    ]); // Redirigimos al formulario y damos feedback GENÉRICO                    
    exit;
}


http_response_code(200); // OK
echo json_encode([
    "username" => $result[0]['username'],
    "role" => $result[0]['role_name']
]); // Redirigimos al formulario y damos feedback GENÉRICO                    
exit;

?>