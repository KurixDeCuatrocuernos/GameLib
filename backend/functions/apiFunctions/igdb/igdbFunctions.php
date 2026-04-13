<?php
/**
 * En este documento se han creado las funciones que se usarán en otras funciones
 */

define('IGDB_JSON', __DIR__.'/igdbData.json');

/**
 * Esta función obtiene un juego de la API IGDB a partir de su titulo
 */
function getIgdbGameByName($name) {
    
}

/**
 * Esta función obtiene un juego de la API IGDB a partir de su id
 */
function getIgdbGameById($id) {

}

/**
 * Esta función convierte la url de igdb en una URL consumible por el front
 */
function getGameCoverByUrl($url) {

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
    ]; // 

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