<?php
/**
 * En este documento se han creado las funciones que se usarán en otras funciones
 */

define('IGDB_JSON', __DIR__.'/igdbData.json');

/**
 * Esta función obtiene una lista de juegos de la API IGDB a partir de su titulo y un límite
 */
function searchIgdbGameByName($name, $limit) {
    // Revisamos que exista el nombre
    if (empty($name) || !is_string($name)){
        error_log("No se ha recibido name, se recibió: $name");
        return null;
    }
    $name = trim($name);
    $name = str_replace(['"', "'"], '', $name); // Eliminamos posibles comillas para evitar errores en la consulta
    // Preparamos la query
    $query = "
        fields id, name, cover, first_release_date;
        search \"$name\";
        limit $limit;
    ";
    // Ejecutamos la query
    $response = consultaIGDB($query);
    // Revisamos los datos
    if (empty($response)) {
        throw new Exception("No se obtuvo respuesta de IGDB");
    }
    // Decodificamos el Json
    $data = json_decode($response, true);
    // Revisamos la decodificación
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        throw new Exception("No se pudo decodficar los datos de JSON");
    }
    if (empty($data)) {
        return []; // No hay resultados, pero todo ha salido bien
    }
    // Devolvemos los datos
    return $data;
}

/**
 * Esta función ejecuta una consulta CURL a la API de IGDB
 * Devuelve los datos en JSON o null
 */
function consultaIGDB($query) {
    if (empty($query)){
        error_log("No se ha recibido query, se recibió: $query");
        return null;
    }

    $clientId = getClientId(); // Recogemos el client Id
    $authToken = getAccessToken(); // Recogemos el access Token
    $url = 'https://api.igdb.com/v4/games'; // URL al endpoint de la API

    $prepare = curl_init(); // Inicuamos la consulta cURL

    // Preparamos la consulta con su header y la query, especificando URL, POST y si queremos que nos devuelva un resultado o no
    curl_setopt_array($prepare, [
        CURLOPT_URL => $url, 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Client-ID: $clientId",
            "Authorization: Bearer $authToken",
            "Content-Type: text/plain"
        ],
        CURLOPT_POSTFIELDS => $query
    ]);
    $response = curl_exec($prepare); // Ejecutamos la consulta cURL
    // Revisamos la respuesta a la consulta (sólo errores)
    if (curl_errno($prepare)) { 
        error_log("Hubo un error con cURL: ".curl_error($prepare));
        return null;
    }

    curl_close($prepare); // Cerramos la consulta

    return $response; // Devolvemos el resultado
}

/**
 * Esta función obtiene un juego de la API IGDB a partir de su id
 */
function getIgdbGameById($id) {
    if (!$id || !is_numeric($id)){
        error_log("No se ha recibido id válido, se recibió: $id");
        return null;
    }

    $sql = 'SELECT * FROM games WHERE igdb_id = ?';
    $game = ejecutarQuery($sql, [$id]);

    if (!isset($game[0])) {

        $query = "
            fields name, cover, first_release_date;
            where id = $id;
        ";

        $response = consultaIGDB($query);
        if (empty($response)) {
            throw new Exception("No se obtuvo respuesta de IGDB");
        }        
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("No se pudo decodficar los datos de JSON");
        }

        $gameData = $data[0] ?? null;
        if (!$gameData) {
            throw new Exception("No se encontraron datos de ese juego");
        }

        // Si estamos aquí, hay datos 

        $sql = 'INSERT INTO games(igdb_id, name, cover, release_date) VALUES (?, ?, ?, ?)';
        // Revisamos y transformamos los datos recibidos
        if (!is_string($gameData['name'])) {
            throw new Exception("el título del juego no es un string");
        }
        $date = !empty($gameData['first_release_date'])
            ? date("Y-m-d", $gameData['first_release_date'])
            : date("Y-m-d");
        $cover = $gameData['cover'];
        if (empty($cover) || !is_string($cover)) {
            throw new Exception("No hay cover o no es una URL");
        }
        // Insertamos los datos
        ejecutarQuery($sql, [$id, $gameData['name'], $cover, $date]);
        

        $sql = 'SELECT * FROM games WHERE igdb_id = ?'; // Se podría optimizar si recogiésemos mediante mysqli_insert_id(), pero prefiero realizar una consulta extra a refactorizar el wrapper (y todo el proyecto en realidad).
        $game = ejecutarQuery($sql, [$id]);

        return $game[0];
    }
    return $game[0];
}

/**
 * Esta función convierte la url de igdb en una URL consumible por el front
 */
function getGameCoverById($id) {
    $noCover = "https://placehold.co/300x450?text=No+Cover";
    // Revisamos que haya id
    if (empty($id)){
        error_log("No se ha recibido id, se recibió: $id");
        return $noCover;
    }

    // Buscamos el juego en la base de datos
    $sql = 'SELECT * FROM games WHERE id = ? LIMIT 1';
    $game = ejecutarQuery($sql, [$id]);
    $game = $game[0] ?? null; // Recogemos el juego del array que devuelve la función o nulo

    // Si no existe el juego, lo intentamos obtener una sola vez
    if (empty($game)) {
        $game = getGameById($id);

        if (empty($game)) {
            error_log("No se pudo obtener el juego con id: $id");
            return $noCover;
        }
    }

    // Revisamos que haya cover
    if (empty($game['cover'])) {
        error_log("El juego no tiene cover");
        return $noCover;
    }

    // Revisamos que el cover sea una URL
    if (empty($game['cover']) || !is_string($game['cover'])) {
        error_log("El cover no contiene URL");
        return $noCover;
    }

    // Construir URL final
    $url = "https:" . $game['cover'];
    $finalUrl = str_replace("t_thumb", "t_cover_big", $url);

    // Revisamos que la URL sea válida
    if (!filter_var($finalUrl, FILTER_VALIDATE_URL)) {
        error_log("La url final no es válida: $finalUrl");
        return $noCover;
    }
    // Si todo ha ido bien devolvemos la URL
    return $finalUrl;
}

/**
 * Esta función devuelve el client_id de IGDB API
 */
function getClientId() {

    if (!file_exists(IGDB_JSON)) {
        throw new Exception("Archivo de configuración IGDB no existe");
    }

    $content = file_get_contents(IGDB_JSON);
    $fileData = json_decode($content, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($fileData) ||
        empty($fileData['client_id'])
    ) {
        throw new Exception("No se pudo obtener el client_id de IGDB");
    }

    return $fileData['client_id'];
}

/**
 * Esta función recupera el token de acceso para las consultas de igdb
 */
function getAccessToken() {
    
    // Si no existe el fichero lo creamos
    if (!file_exists(IGDB_JSON)) {
        resetAccessToken();
    }

    $fileContent = file_get_contents(IGDB_JSON);
    $fileData = json_decode($fileContent, true);
    // Si hay algún problema con los datos recreamos los datos
    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($fileData) ||
        empty($fileData['access_token']) ||
        empty($fileData['expires_at']) ||
        $fileData['expires_at'] < time()
    ) {
        resetAccessToken();

        // Volvemos a leer el fichero después de regenerar
        $fileContent = file_get_contents(IGDB_JSON);
        $fileData = json_decode($fileContent, true);
    }

    return $fileData['access_token']; // Devolvemos el token de acceso
}

/**
 * Esta función comprueba los dasto del access token y lo regenera si no existe o ha expirado
 */
function checkApiData() {

    if (!file_exists(IGDB_JSON)) {
        error_log("No se han encontrado los datos para obtener el token");
        return false;
    }

    $fileContent = file_get_contents(IGDB_JSON);
    $fileData = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($fileData)) {
        error_log("Los datos recogidos del fichero no están en formato JSON ni Array");    
        return false;
    }
    // Comprobamos que estén todos los datos
    if (
        !empty($fileData['client_id']) &&
        !empty($fileData['client_secret']) &&
        !empty($fileData['access_token']) &&
        !empty($fileData['expires_at'])
    ) {
        if ($fileData['expires_at'] < time()) { // El token de sesión ha expirado
            if (resetAccessToken()){
                error_log("Token de acceso regenerado");
                return true;         
            } 
            error_log("Hubo un fallo al regenerar el token de acceso");
            return false;
        } else { // El token de sesión no ha expirado
            error_log("El token de acceso era válido");
            return true;
        } 
    } else { // Alguno de los valores está vacío
        if (resetAccessToken()){
            error_log("Token de acceso establecido");
            return true;
        }
        error_log("Hubo un error al regenerar el token de acceso");
        return false;
    }
}

/**
 * Esta función recoge un nuevo token de acceso para las consultas a IGDB API
 */
function resetAccessToken() {

    $fileContent = file_get_contents(IGDB_JSON);
    $fileData = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($fileData)) {
        error_log("Los datos recogidos del fichero no están en formato JSON ni Array");
        return false;
    }

    $clientId = $fileData['client_id'];
    $clientSecret = $fileData['client_secret'];

    $url = "https://id.twitch.tv/oauth2/token";
    $data = [
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "grant_type" => "client_credentials"
    ];

    $options = [
        "http" => [
            "header" => "content-type: application/x-www-form-urlencoded",
            "method" => "POST",
            "content" => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context); // Recogemos la respuesta de la API 
    if ($response === false) {
        error_log("No se ha podido obtener el token de acceso a IGDB");
        return false;
    }    
    
    $data = json_decode($response, true); // Decodificamos la respuesta de la API
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        error_log("Los datos recogidos de la API no están en formato JSON");
        return false;
    } 
    // Comprobamos que los datos estén bien
    if (!isset($data['access_token']) || !isset($data['expires_in'])) { 
        error_log("Respuesta inválida de IGDB");    
        return false;
    }

    $accessToken = $data['access_token']; // guardamos el token de acceso
    $tokenExpires = $data['expires_in']; // guardamos cuándo expira el token 

    // En este punto data ya nos da igual
    $data = [
        "client_id" => $clientId, 
        "client_secret" => $clientSecret,
        "access_token" => $accessToken,
        "expires_at" =>  time() + $tokenExpires
    ];
    $json = json_encode($data, JSON_PRETTY_PRINT);

    file_put_contents(IGDB_JSON, $json, LOCK_EX);

    return true;
}

?>