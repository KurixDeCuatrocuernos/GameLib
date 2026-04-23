<?php
// Este Módulo es el más importante ya que hará uso del id del usuario, del id del juego y el id de Steam del usuario para guardar los 
//     datos en la base de datos. Por ello se hace uso del resto de funciones que se han creado.

require_once __DIR__.'/../../../database/db.php'; // Importamos la conexoón con la base de datos
require_once __DIR__.'/../../commonFunctions.php'; // Importamos las funciones comunes
require_once __DIR__.'/steamFunctions.php'; // Importamos las funciones de Steam API
require_once __DIR__.'/../../gameFunctions/gameFunctions.php'; // Importamos las funciones de games
require_once __DIR__.'/../igdb/igdbFunctions.php'; // Importamos las funciones de IGDB API

header('Content-Type: application/json'); // Encabezado de la respuesta

iniciarSesionSiNoActiva(); // Iniciamos sesión para la supervariable $_SESSION

if (!isset($_SESSION['user_id'])) { // MEJOR REDIRIGIR AL LOGIN
    http_response_code(401); // Forbidden
    echo json_encode([
        "message" => "Usuario no autenticado" // Deberíamos redirigir al Login y dar feedback
    ]);
    exit; // Salimos si el usuario no ha iniciado sesión 
}

$userId = $_SESSION['user_id'];

// Obtenemos el steam_id y el id de users_providers

$sql = 'SELECT id as provider_id, steam_id 
        FROM users_providers
        WHERE user_id = ? AND provider = "steam" AND steam_id IS NOT NULL';

$result = ejecutarQuery($sql, [$userId]);

if (empty($result)) { 
    http_response_code(404); // Not Found
    echo json_encode([
        "message" => 'Cuenta de Steam no vinculada' // Deberíamos redirigir a la página para vincular librerías
    ]);
    exit; // Salimos si el usuario no ha vinculado Steam
}

$providerId = $result[0]['provider_id'];
$steamId = $result[0]['steam_id'];

try {

    $steamGamesNames = getSteamGamesNames($steamId);
    
    if (empty($steamGamesNames)) {
        http_response_code(200); // La consulta ha sido correcta, pero no hay juegos (puede ocurrir)
        echo json_encode([
            "message" => "No se han encontrado juegos en tu biblioteca de Steam" // Deberíamos filtrar las posibles respuestas del código 200 para ver si es o no correcto 
        ]);
        exit; 
    }

    $insertedGames = 0;
    $failed = 0;
    $failedGames = [];

    foreach ($steamGamesNames as $gameName) {
        
        $game = getGameByName($gameName); // Recogemos cada juego por su nombre
        
        if (!$game) {
            $igdbResult = searchGameByName($gameName); // Buscamos el juego en IGDB
            
            if (!empty($igdbResult)) {
                $igdbGame = $igdbResult[0]; // Recogemos sólo el primer juego
                $igdbId = $igdbGame['id']; // Recogemos el Id
                $name = $igdbGame['name']; // Recogemos el nombre del juego
                $cover = $igdbGame['cover'] ?? null; // Recogemos el cover del juego
                $releaseDate = !empty($igdbGame['first_release_date'])
                    ? date('Y-m-d', $igdbGame['first_release_date'])
                    : date('Y-m-d');
                
                insertGame($igdbId, $cover, $name, $releaseDate);
                $game = getGameByName($name);

            } 
        }

        if ($game) {
            $sql = 'INSERT INTO users_games (game_id, user_id, user_provider_id)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE game_id = game_id';
            ejecutarQuery($sql, [$game['id'], $userId, $providerId]);
            
            $insertedGames++; // Aumentamos la cantidad de juegos insertados
            
        } else {
            $failed++;
            $failedGames[] = $gameName;  
        }
    }

    http_response_code(200); // Success
    echo json_encode([
        "message" => "Biblioteca de Steam Sincronizada",
        "inserted" => $insertedGames,
        "failed" => $failed,
        "total" => count($steamGamesNames),
        "failed_games" => $failedGames
    ]);

} catch (Exception $ex) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "message" => "Error al sincronizar: ".$ex->getMessage() //Ha ocurrido algún error, por lo que lo mostramos
    ]);
}










?>