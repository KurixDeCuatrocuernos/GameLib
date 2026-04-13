<?php

    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';

    function isDate($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }  
    
    // ============================================================================
    // DAO
    // ============================================================================
    /**
     * Esta función devuelve un juego desde la base de datos
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
     * Esta función devuelve 20 juegos al azar de la base de datos
     * Devuelve un array con juegos
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
?>