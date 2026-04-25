<?php
/**
 * Endpoint para recibir, validar y almacenar el steam_id del usuario
 * Tras autenticación con Steam OpenID
 */

require_once __DIR__.'/../../../database/db.php';
require_once __DIR__.'/../../commonFunctions.php';

header('Content-Type: application/json');

// Obtenemos los parámetros (POST desde AJAX o GET desde redirección directa)
$params = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

// Validamos que tengamos los datos necesarios
if (empty($params['openid_claimed_id'])) {
    http_response_code(400); //Bad Request
    echo json_encode([
        "success" => false, 
        "error" => "missing_parameters"
    ]);
    exit;
}

// Si la petición viene de Steam (GET), validamos con Steam
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $validationParams = $params;
    $validationParams['openid.mode'] = 'check_authentication';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query($validationParams)
        ]
    ]);
    
    $response = file_get_contents("https://steamcommunity.com/openid/login", false, $context);
    
    // Si no hay datos en $response bloqueamos el acceso 
    if ($response === false || strpos($response, "is_valid:true") === false) {
        http_response_code(401); // Unauthorized 
        echo json_encode([
            "success" => false, 
            "error" => "invalid_response"
        ]);
        exit;
    }
}

// Extraemos el SteamID64
preg_match("/openid\\/id\\/([0-9]+)/", $params['openid_claimed_id'], $matches);
if (!isset($matches[1])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "no_steam_id"]);
    exit;
}

$steamId = $matches[1];

// Verificamos que la sesión esté iniciada
iniciarSesionSiNoActiva();
// Si no hay sesión devolvemos el error correspondiente
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        "success" => false, 
        "error" => "no_session"
    ]); 
    exit;
}

// Preparamos la consulta a users_providers
$sql = 'INSERT INTO users_providers(user_id, provider, steam_id) 
        VALUES (?, "steam", ?) 
        ON DUPLICATE KEY UPDATE steam_id = VALUES(steam_id)';

// Guardamos el nuevo user_provider en la base de datos
$result = ejecutarQuery($sql, [$_SESSION['user_id'], $steamId]);

// Si no hay result lo consideramos un error interno del servidor
if ($result === false || $result === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "success" => false, 
        "error" => "database_error"
    ]);
    exit;
}

$_SESSION['steam_id'] = $steamId;

// Si todo ha ido bien devolvemos que todo ha ido bien
echo json_encode([
    "success" => true, 
    "steam_id" => $steamId
]);
exit;
?>