<?php
// backend/functions/apiFunctions/steam/checkSteamStatus.php
require_once __DIR__.'/../../../database/db.php';
require_once __DIR__.'/../../commonFunctions.php';

header('Content-Type: application/json');

iniciarSesionSiNoActiva();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["linked" => false]);
    exit;
}

$sql = 'SELECT id FROM users_providers 
        WHERE user_id = ? AND provider = "steam" AND steam_id IS NOT NULL';

$result = ejecutarQuery($sql, [$_SESSION['user_id']]);

echo json_encode(["linked" => !empty($result)]);
exit;
?>