<?php
/**
 * Endpoint para obtener los juegos de la biblioteca del usuario actual
 * Devuelve la lista de juegos con el nombre del provider (Steam, Epic, GOG)
 * GET: No requiere parámetros
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

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

// Verificar método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método no permitido. Usa GET"]);
    exit;
}

// Iniciar sesión y verificar autenticación
iniciarSesionSiNoActiva();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Usuario no autenticado"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Consulta para obtener los juegos del usuario con el nombre del provider
$sql = "SELECT 
            g.id,
            g.igdb_id,
            g.name,
            g.cover,
            g.release_date,
            up.provider
        FROM users_games ug
        INNER JOIN games g ON ug.game_id = g.id
        INNER JOIN users_providers up ON ug.user_provider_id = up.id
        WHERE ug.user_id = ?
        ORDER BY g.name ASC"; // He probado la consulta y funciona

$result = ejecutarQuery($sql, [$userId]);

// Devolver directamente el array de juegos (sin wrapper de éxito)
http_response_code(200); // OK
echo json_encode($result);
exit;
?>