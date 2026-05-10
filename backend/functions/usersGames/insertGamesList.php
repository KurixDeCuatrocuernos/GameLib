<?php
/**
 * Endpoint para añadir juegos manualmente desde el formulario GameForm
 * Recibe una lista de juegos con su plataforma
 * POST: JSON con { games: [{ game: { id, name, cover, releaseDate }, platform }] }
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';
require_once __DIR__.'/../gameFunctions/gameFunctions.php';
require_once __DIR__.'/../apiFunctions/gog/gogFunctions.php';

// Habilitamos CORS para el Frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejamos preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // OK
    exit;
}

// Verificamos método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Unauthorized
    echo json_encode([
        "success" => false, 
        "message" => "Método no permitido" 
    ]);
    exit;
}

// Iniciamos sesión si no está activa
iniciarSesionSiNoActiva();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Lacks valid authentication credentials
    echo json_encode([
        "success" => false, 
        "message" => "Usuario no autenticado"
    ]);
    exit;
}

// Leemos los datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$games = $input['games'] ?? [];

// Si no hay juegos salimos
if (empty($games)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "success" => false, 
        "message" => "No hay juegos para añadir"
    ]);
    exit;
}

// Inicializamos contadores de cara a la respuesta
$userId = $_SESSION['user_id'];
$inserted = 0;
$alreadyExists = 0;
$failed = 0;
$failedGames = [];

// Iniciamos la transacción
global $conexion;
mysqli_begin_transaction($conexion);

try {
    foreach ($games as $item) {
        $gameData = $item['game'];
        $platform = trim($item['platform']);
        
        // Validamos los datos
        if (empty($gameData['id']) || empty($gameData['name']) || empty($platform)) {
            $failed++;
            $failedGames[] = $gameData['name'] ?? 'Juego sin nombre';
            continue;
        }
        
        $igdbId = $gameData['id'];
        $gameName = $gameData['name'];
        
        // Procesamos el cover (puede venir como string o como objeto con url)
        $cover = null;
        if (isset($gameData['cover'])) {
            if (is_array($gameData['cover']) && isset($gameData['cover']['url'])) {
                $cover = $gameData['cover']['url'];
            } elseif (is_string($gameData['cover'])) {
                $cover = $gameData['cover'];
            }
        }
        
        // Aseguramos que la URL del cover sea completa
        if ($cover && !str_starts_with($cover, 'http')) {
            $cover = 'https:' . $cover;
        }
        
        // Si no hay cover, usamos placehold por defecto (esto es raro)
        if (empty($cover)) {
            $cover = "https://placehold.co/300x450?text=No+Cover";
        }
        
        // Procesamos la fecha
        $releaseDate = null;
        if (isset($gameData['first_release_date'])) {
            if (is_numeric($gameData['first_release_date'])) {
                // Es timestamp de Unix
                $releaseDate = date('Y-m-d', $gameData['first_release_date']);
            } else {
                $releaseDate = date('Y-m-d', strtotime($gameData['first_release_date']));
            }
        }

        if (empty($releaseDate)) {
            $releaseDate = date('Y-m-d');
        }
        
        // Obtenemos o creamos el provider para esta plataforma
        $providerId = getOrCreateProvider($userId, $platform);
        
        // Verificamos si el juego ya existe en la BD por igdb_id
        $game = getGameByIgdbId($igdbId);
        
        // Si no existe, lo insertamos
        if (!$game) {
            insertGame($igdbId, $cover, $gameName, $releaseDate);
            $game = getGameByIgdbId($igdbId);
        }
        // Si sigue sin poderse insertar aumentamos el contador de fallos y la lista para dar feedback
        if (!$game) {
            $failed++;
            $failedGames[] = $gameName;
            continue;
        }
        
        // Insertar en users_games
        $sql = 'INSERT INTO users_games (game_id, user_id, user_provider_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE game_id = game_id';
        
        $affected = ejecutarQuery($sql, [$game['id'], $userId, $providerId]);
        
        if ($affected === 0) {
            $alreadyExists++;
        } else {
            $inserted++;
        }
    }
    
    mysqli_commit($conexion); // Si todo va bien confirmamos la transacción
    
    // Todo ha salido bien, se hayan insertado todos los juegos o no
    http_response_code(200); // OK
    echo json_encode([
        "success" => true,
        "inserted" => $inserted,
        "already_exists" => $alreadyExists,
        "failed" => $failed,
        "failed_games" => $failedGames
    ]);
    exit;
    
} catch (Exception $e) {
    mysqli_rollback($conexion); // Si algo sale mal cancelamos la transacción
    error_log("Error en insertGameList: " . $e->getMessage());
    http_response_code(500); // Internal Server error
    echo json_encode([
        "success" => false,
        "message" => "Error al procesar la solicitud: " . $e->getMessage()
    ]);
    exit;
}
?>