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
     * En cualquier caso se devolverá un array con un origen (db o igdb) y un array de arrays
     * Importante, puede haber diferencias entre IGDB y la Base de datos
     */
    function searchGameByName($name) {
        if (empty($name) || !is_string($name)) {
            throw new Exception("El nombre proporcionado no es válido");
        }
        $name = trim(str_replace('"','', $name)); // Eliminamos posibles comillas y espacios para evitar errores en la consulta
        $sql = 'SELECT * FROM games WHERE name LIKE ?';
        $result = ejecutarQuery($sql, ["%$name%"]);
        // Revisamos si hay datos
        if (empty($result)) {
            $result = searchIgdbGameByName($name, 1); // Buscamos el título en IGDB
            // Devolvemos los datos de IGDB
            return [
                'source' => 'igdb',
                'data' => $result
            ]; 
        }
        // Devolvemos los datos de la base de datos
        return [
            'source' => 'db',
            'data' => $result
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

?>