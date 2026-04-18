<?php
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/./gameFunctions.php';

    header('Content-Type: application/json');
    
    // Comprobamos que los datos vengan del POST
    if ($_SERVER['REQUEST_METHOD']!=='POST') {
        http_response_code(405); // Not Allowed
        echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
        exit;
    }
    $input = file_get_contents('php://input'); // Recogemos los datos con fetch
    $data = json_decode($input, true); // decodificamos los datos de json
    // Comprobamos que los datos sean de tipo JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad request
        echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    // Comprobamos que los datos existan
    if (empty($data)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Alguno de los parámetros requeridos está vacío"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    // Comprobamos que los datos tengan contenido
    if (!isset($data['id']) || !isset($data["igdb_id"]) || !isset($data["cover"]) || 
        !isset($data["name"]) || !isset($data["release_date"])
    ) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Alguno de los parámetros requeridos está vacío"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    // Comprobamos que los datos tengan un formato correcto
    $cell = checkInputGameData($data['id'], $data['igdb_id'], $data['name'], $data['cover'], $data['release_date']);
    if (!$cell) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Alguno de los parámetros tiene un formato inadecuado"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    // Si estamos aquí, todo ha ido bien
    $oldGame = getGameById($data['id']); // Recogemos el juego actual
    // Comprobamos que el juego existe (no es del todo necesario, pero mejor hacerlo)
    if (empty($oldGame)) {
        http_response_code(404); // Not Found
        echo json_encode(["message" => "Juego no encontrado, imposible actualizar"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    $sets = [];
    $values = [];

    if ($oldGame['name'] !== $data['name']){
        $sets[] = 'name = ?';
        $values[] = $data['name'];
    }

    if ($oldGame['cover'] !== $data['cover']) {
        $sets[] = 'cover = ?';
        $values[] = $data['cover'];
    }

    if ($oldGame['release_date'] !== $data['release_date']) {
        $sets[] = 'release_date = ?';
        $values[] = $data['release_date'];
    }

    if (count($sets) == 0) {
        http_response_code(200); // OK
        echo json_encode(["message" => "No se han modificado datos"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    try{
        $values[] = $data['id'];
        $sql = 'UPDATE games SET '.implode(", ",$sets).' WHERE id = ?';
        ejecutarQuery($sql, $values);

        http_response_code(200); // OK
        echo json_encode(["message" => "Datos del juego actualizados con éxito"]); // Deberíamos redirigir al formulario y dar feedback
        exit;

    } catch (Exception $ex) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Error al actualizar los datos"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    
    /**
     * Esta función comprueba que los datos de un juego son válidos de cara a la base de datos
     * Devuelve true si lo son y false si alguno falla
     */
    function checkInputGameData($id, $igdb_id, $name, $cover, $date) {
        $cell = true;

        if (!filter_var($id, FILTER_VALIDATE_INT)) { // Comprobamos que id es int
            $cell = false;
        }

        if (!filter_var($igdb_id, FILTER_VALIDATE_INT)) { // Comprobamos que el id de igdb es int
            $cell = false;
        }

        if (!is_string($name)) { // Comprobamos que name es string
            $cell = false;
        }

        if (!is_string($cover) || 
            !filter_var($cover, FILTER_VALIDATE_URL) ||
            !preg_match('/^https?:\\/\\//', $cover)) { // Comprobamos que cover es una URL Válida
            $cell = false;
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $date);
        if (!$fecha || $fecha->format('Y-m-d') !== $date) { // Comprobamos que la fecha es tipo Date (Timestamp)
            $cell = false;
        }

        return $cell; 
    }
?>