<?php
// Este módulo es para recibir, validar y almacenar el steamid del usuario
//  para realizar las consultas a la api de steam

    require_once __DIR__.'/../../../database/db.php';
    require_once __DIR__.'/../../commonFunctions.php';
    
    salirSiNoHaySesion('localhost://index.html'); // Redirigimos a la página de login si no hay sesión

    header('Content-Type: application/json');

    // Reenviar la validación a Steam
    $params = $_GET;
    $params['openid.mode'] = 'check_authentication';

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query($params)
        ]
    ]);

    $response = file_get_contents(
        "https://steamcommunity.com/openid/login",
        false,
        $context
    );

    if ($response === false) {
        http_response_code(502); // Bad Gateway
        echo json_encode(["message" => "Error de Steam: No se pudo recuperar el steam id de steam"]); // Deberíamos redirigir de vuelta al index
        exit;
    }

    // Verificar respuesta
    if (strpos($response, "is_valid:true") === false) {
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "No se pudo iniciar sesión en Steam"]); // Deberíamos redirigir de vuelta al index
        exit;
    }

    // Extraer SteamID64
    preg_match(
        "/openid\\/id\\/([0-9]+)/",
        $_GET['openid_claimed_id'],
        $matches
    );

    if (!isset($matches[1])) {
        http_response_code(400);
        echo json_encode(["message" => "No se recibió respuesta de Steam"]); // Deberíamos redirigir de vuelta al index
        exit;
    }

    $steamid = $matches[1];

    iniciarSesionSiNoActiva(); // haremos session_start() si no se ha hecho ya

    // 🔥 Aquí ya tenemos el usuario autenticado
    if (!insertSteamId($_SESSION['user_id'], $steamid)) { // No se pudo guardar el steamId
        http_response_code(422); // Unprocessable Entity
        echo json_encode(["message" => "No se pudo almacenar el steam id"]); // Deberíamos redirigir de vuelta al index
        exit;
    }

    $_SESSION['steam_id'] = $steamid; // Guardamos el id en la sesión

    // En producción: redirigir a React con token
    http_response_code(200); // OK
    echo json_encode(["message" => "Cuenta de Steam Vinculada con éxito"]); // Deberíamos redirigir de vuelta al index
    exit;

// Esta función almacena el steam_id de un usuario en users_providers
function insertSteamId($userId,$steamId) {
    if (empty($steamId)) {
        return false;
    }
    if (empty($userId)) {
        return false;
    }
    // user_id provider steam_id
    $sql = 'INSERT INTO users_providers(user_id, provider, steam_id) 
    VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE steam_id = VALUES(steam_id);';

    ejecutarQuery($sql, [$userId, 'steam', $steamId]);
    
    return true;
}

?>