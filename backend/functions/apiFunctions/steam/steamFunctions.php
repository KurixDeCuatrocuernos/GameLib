<?php
// Este módulo es para almacenar funciones que se reutilizarán en diferentes módulos

require_once __DIR__.'/../igdb/igdbFunctions.php';
require_once __DIR__.'/../../gameFunctions/gameFunctions.php';

define('STEAM_JSON', __DIR__.'/steam.json');

/**
 * Esta función recupera el Steam Api Key necesario para las consultas
 * Devuelve un string con el steam api key
 */
function getSteamApiKey() {
     if (!file_exists(STEAM_JSON)) {
        throw new Exception("Archivo de configuración de Steam no existe");
    }

    $content = file_get_contents(STEAM_JSON);
    if ($content === false) {
        throw new Exception("No se pudo leer el archivo de configuración");
    }

    $fileData = json_decode($content, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($fileData) ||
        !array_key_exists('steam_key', $fileData)
    ) {
        throw new Exception("No se pudo obtener el steam_key de Steam API");
    }

    return $fileData['steam_key'];
}

/**
 * Esta función recupera la lista de juegos en la biblioteca de un usuario de Steam
 * Devuelve una lista de strings con nombres de juegos
 */
function getSteamGamesNames ($steamId) {
    $apiKey = getSteamApiKey(); // Recogemos la api key
    
    $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?" . http_build_query([
        "key" => $apiKey, 
        "steamid" => $steamId,
        "include_appinfo" => 1,
        "include_played_free_games" => 1
    ]); // Preparamos la URL para la consulta

    $response = file_get_contents($url); // Recogemos la respuesta de la consulta

    if ($response === false) {
        throw new Exception("No se pudo obtener la biblioteca de Steam");
    }

    $data = json_decode($response, true); // Decodificamos el JSON

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !isset($data["response"]["games"])
    ) {
        // Steam devuelve vacío si el perfil es privado o no tiene juegos
        return []; // Si nos ha devuelto un array vacío devolvemos null
    }

    $games = $data["response"]["games"];

    return array_map(function ($game) {
        return $game["name"] ?? "Unknown"; // Devolvemos sólo los nombres de los juegos
    }, $games);
}

/**
 * Esta función guarda un juego de un usuario en la base de datos a partir de su nombre
 * Devuelve true si lo consigue o false si hay un error (y lanzará un error_log)
 */
function insertSteamGameByIgdb($title, $userId, $userProviderId) {

    if (empty($title) || empty($userId) || empty($userProviderId)) {
        error_log('Parámetros inválidos');
        return false; // Si algún parámetro no se ha proporcionado devolvemos false
    }

    $game = getGameByName($title); // Buscamos el juego en la base de datos

    // Si no encontramos el juego lo buscamos en IGDB
    if (empty($game)) {
        $igdbGame = searchIgdbGameByName($title, 1);

        if (empty($igdbGame)) {
            error_log("No encontrado en IGDB: $title");
            return false; // Si no encontramos el juego devolvemos false
        }
     
        // Procesamos los datos IGDB
        
        $cover = null;
        if (!empty($igdbGame['cover']['url'])) {
            $cover = $igdbGame['cover']['url'];
        }

        if (!empty($igdbGame['first_release_date'])) {
            $date = date('Y-m-d', $igdbGame['first_release_date']);
        }

        insertGame($igdbGame['id'], $cover, $igdbGame['name'], $date); // Insertamos el juego

        $game = getGameByName($igdbGame['name']); // Volvemos a buscar el juego en la base de datos

        if (empty($game)) {
            return false; // Si seguimos sin encontrar el juego tras haberlo metido devolvemos false
        }
    }

    $sql = 'INSERT INTO users_games(game_id, user_id, user_provider_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE game_id = game_id';

    ejecutarQuery($sql, [$game['id'], $userId, $userProviderId]); // Insertamos la relación usuario-juego

    return true; // Si llegamos hasta aquí se ha creado la relación
}

?>