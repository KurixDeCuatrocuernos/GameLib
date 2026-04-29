<?php
/**
 * En este módulo se almacenan las funciones específicas de cara a la importación de juegos de GOG
 */
    require_once __DIR__.'/../../../database/db.php';
    require_once __DIR__.'/../../commonFunctions.php';

/**
 * Obtiene el ID del provider para un usuario y plataforma específica
 * Si no existe, lo crea automáticamente
 * 
 * @param int $userId ID del usuario
 * @param string $providerName Nombre de la plataforma (steam, epic, gog, etc.)
 * @return int ID del provider
 */
function getOrCreateProvider($userId, $providerName) {
    // Buscamos si ya existe un provider para el usuario actual con ese nombre de provider
    $sql = 'SELECT id FROM users_providers 
            WHERE user_id = ? AND provider = ?';
    $result = ejecutarQuery($sql, [$userId, $providerName]);
    // Si existe lo recogemos
    if (!empty($result)) {
        return $result[0]['id'];
    }
    // Si no existe crearemos un nuevo provider GOG para el usuario
    $sql = 'INSERT INTO users_providers (user_id, provider) VALUES (?, ?)';
    ejecutarQuery($sql, [$userId, $providerName]);
    // Guardamos el ID del provider creado
    $sql = 'SELECT LAST_INSERT_ID() as id';
    $result = ejecutarQuery($sql, []);
    return $result[0]['id'];
}

/**
 * Extrae la plataforma principal de la lista de plataformas del CSV
 * 
 * @param string $platformList Lista de plataformas (ej: "Steam, Windows")
 * @return string|null Nombre normalizado (steam, epic, gog) o null si no es soportada
 */
function extractPlatformFromList(string $platformList) {
    if (empty($platformList)) {
        return null;
    }
    
    // La lista puede venir como "Steam, Epic Games Store o GOG"
    $platforms = explode(',', $platformList);
    $firstPlatform = strtolower(trim($platforms[0]));
    
    // Nombres de providers que sí queremos añadir
    if (strpos($firstPlatform, 'steam') !== false) return 'steam';
    if (strpos($firstPlatform, 'epic') !== false) return 'epic';
    if (strpos($firstPlatform, 'gog') !== false) return 'gog';
    
    return null; // Por defecto no la devolvemos
}

?>