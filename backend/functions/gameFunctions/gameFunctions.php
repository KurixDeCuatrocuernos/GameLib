<?php

    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/../apiFunctions/igdb/igdbFunctions.php';

    function isDate($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    
    // ============================================================================
    // DAO
    // ============================================================================
    /**
     * Esta función devuelve un juego desde la base de datos a partir de su id
     * Devuelve el juego o nulo
     */
    function getGameById ($id){
        $sql = 'SELECT * FROM games WHERE id = ? LIMIT 1'; // preparamos la consulta
        $result = ejecutarQuery($sql, [$id]); // Ejecutamos la consulta
        // Devolvemos el resultado si hay datos, o null si no hay datos.
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     *  Esta función devuelve un juego desde la base de datos a partir de su name
     *  Devuelve el juego o nulo
     */
    function getGameByName($name) {
        $sql = 'SELECT * FROM games WHERE name LIKE ? LIMIT 1'; // preparamos la consulta
        $result = ejecutarQuery($sql, ["%$name%"]); // Ejecutamos la consulta
        // Devolvemos el resultado si hay datos, o null si no hay datos.
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Esta función devuelve 20 juegos al azar de la base de datos
     * Devuelve un array con juegos
     * CUIDADO: Puede provocar Bucle infinito si no hay suficientes juegos insertados
     */
    function getRandomGamesByLimit() {        
        $maxId = ejecutarQuery('SELECT MAX(id) as max_id FROM games')[0]['max_id']; // Recogemos el máximo id de la base de datos actualmente
        $games = [];
        $idsUsados = [];

        while (count($games) < 20) { // Se seguirá aplicando mientras haya menos juegos en $games que 20
            $randomId = rand(1, $maxId); // Generamos un id aleatoria entre 1 y el id máximo
            if (in_array($randomId, $idsUsados)) continue; // Si el id ya se ha usado no lo queremos
            $result = getGameById($randomId);// Buscamos el usuario con la query
            if (!empty($result)) { // Si hay resultado...
                $games[] = $result; // Guardamos el usuario en games
                $idsUsados[] = $result['id']; // guardamos el id para no repetirlo 
            }
        }
        return $games; // Devolvemos un array de juegos recogidos
    }
/**
 * Esta función busca un juego en la base de datos a partir de su nombre
 * Si no lo encuentra, lo busca en IGDB
 * Devuelve un array con 'source' (db o igdb) y 'data' (el juego o null)
 */
function searchGameByName($name) {
    if (empty($name) || !is_string($name)) {
        throw new Exception("El nombre proporcionado no es válido");
    }
    $name = trim(str_replace('"','', $name));
    $sql = 'SELECT * FROM games WHERE name LIKE ?';
    $result = ejecutarQuery($sql, ["%$name%"]);
    
    // Caso 1: Está en la BD local
    if (!empty($result)) {
        return [
            'source' => 'db',
            'data' => $result[0]  // ← Un solo juego
        ];
    }
    
    // Caso 2: Buscamos en IGDB
    $igdbResult = searchIgdbGameByName($name, 1);
    
    if (!empty($igdbResult) && is_array($igdbResult)) {
        // IGDB puede devolver un array de juegos o un solo juego
        if (isset($igdbResult['id'])) {
            // Es un solo juego
            $game = $igdbResult;
        } else {
            // Es un array de juegos
            $game = $igdbResult[0] ?? null;
        }
        
        if ($game) {
            return [
                'source' => 'igdb',
                'data' => $game  // ← Un solo juego
            ];
        }
    }
    
    // Caso 3: No encontrado ni en la base de datos ni en IGDB
    return [
        'source' => null,
        'data' => null
    ];
}

    /**
     * Esta función inserta un juego en la base de datos
     */
    function insertGame($igdbId, $cover, $name, $date) {

        if (empty($igdbId) || empty($name) || empty($date)) {
            return false; // Si alguno de los parámetros está vacío devolvemos false
        }

        if (
            !is_numeric($igdbId) ||
            !is_string($name) ||
            ($cover !== null && !is_string($cover))
        ) {
            return false; // Si alguno de los parámetros en inválido devolvemos false
        }

        if (!$date instanceof DateTime) {
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                return false;
            }
        }

        $formatedDate = $date->format('Y-m-d');

        $sql = 'INSERT INTO games (igdb_id, name, cover, release_date)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    cover = VALUES(cover),
                    release_date = VALUES(release_date)';

        ejecutarQuery($sql, [$igdbId, $name, $cover, $formatedDate]);

        return true;
    }

    /**
     * Busca un juego por su ID de IGDB
     * @param int $igdbId ID del juego en IGDB
     * @return array|null El juego encontrado o null
     */
    function getGameByIgdbId($igdbId) {
        $sql = 'SELECT * FROM games WHERE igdb_id = ? LIMIT 1';
        $result = ejecutarQuery($sql, [$igdbId]);
        return !empty($result) ? $result[0] : null;
    }

?>