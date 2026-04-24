<?php
/**
 * En este módulo se almacenan las funciones específicas de cara a la importación de juegos de Epic Games
 */
    require_once __DIR__.'/../../../database/db.php';
    require_once __DIR__.'/../../commonFunctions.php';

/**
 * Esta función recupera o crear el provider para GOG para el usuario
 * DEVUELVE el id del provider
 */
function getEpicProvider($userId) {
    // Buscamos si ya existe un provider GOG para el usuario actual
    $sql = 'SELECT id FROM users_providers 
            WHERE user_id = ? AND provider = "epic"';
    $result = ejecutarQuery($sql, [$userId]);
    // Si existe lo recogemos
    if (!empty($result)) {
        return $result[0]['id'];
    }
    // Si no existe crearemos un nuevo provider GOG para el usuario
    $sql = 'INSERT INTO users_providers (user_id, provider) VALUES (?, "gog")';
    ejecutarQuery($sql, [$userId]);
    // Guardamos el ID del provider creado
    $sql = 'SELECT LAST_INSERT_ID() as id';
    $result = ejecutarQuery($sql, []);
    return $result[0]['id'];
}


?>