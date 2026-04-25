<?php
/**
 * Endpoint para recibir, validar y almacenar el steam_id del usuario
 * Tras autenticación con Steam OpenID
 */

require_once __DIR__.'/../../../database/db.php';
require_once __DIR__.'/../../commonFunctions.php';

header('Content-Type: application/json');

// Obtener parámetros (POST desde AJAX o GET desde redirección directa)
$params = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

// Validar que tenemos los datos necesarios
if (empty($params['openid_claimed_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "missing_parameters"]);
    exit;
}

// Si la petición viene directamente de Steam (GET), validar con Steam
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
    
    if ($response === false || strpos($response, "is_valid:true") === false) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "invalid_response"]);
        exit;
    }
}

// Extraer SteamID64
preg_match("/openid\\/id\\/([0-9]+)/", $params['openid_claimed_id'], $matches);
if (!isset($matches[1])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "no_steam_id"]);
    exit;
}

$steamId = $matches[1];

// Asegurar sesión iniciada
iniciarSesionSiNoActiva();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "no_session"]);
    exit;
}

// Guardar en la base de datos
$sql = 'INSERT INTO users_providers(user_id, provider, steam_id) 
        VALUES (?, "steam", ?) 
        ON DUPLICATE KEY UPDATE steam_id = VALUES(steam_id)';

$result = ejecutarQuery($sql, [$_SESSION['user_id'], $steamId]);

if ($result === false || $result === null) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "database_error"]);
    exit;
}

$_SESSION['steam_id'] = $steamId;

echo json_encode(["success" => true, "steam_id" => $steamId]);
exit;
?>