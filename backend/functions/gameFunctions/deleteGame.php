<?php
    
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/./gameFunctions.php';

    if ($_SERVER['REQUEST_METHOD']!=='POST') { // Lo correcto es usar DELETE
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
    if (!isset($data['id'])
    ) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Un parámetro está vacío"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    if (!filter_var($data['id'], FILTER_VALIDATE_INT)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Un parámetro no tiene el tipo adecuado"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    // Si estamos aquí, borramos
    $game = getGameById($data['id']);
    if (empty($game)) {
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se ha encontrado el juego, imposible borrar"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    // Borramos el juego
    $sql = 'DELETE FROM games WHERE id = ?';
    ejecutarQuery($sql, [$data['id']]);

    http_response_code(200); // OK
    echo json_encode(["message" => "Juego borrado con éxito"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
?>