<?php
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {

        $input = file_get_contents('php://input'); // Recogemos los datos en JSON
        $data = json_decode($input, true); // Decodificamos los datos
        
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        if (!isset($data["id"]) || $data["id"] === '') {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "ID requerido"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        $id = $data["id"];

        if (!is_numeric($id) || (int)$id <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "ID inválido"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        $id = (int)$id;

        $sql = 'DELETE FROM users WHERE id = ?';
        try{
            ejecutarQuery($sql, [$id]); // En teoría la base de datos borrará los datos en cascada
            http_response_code(200); // OK
            echo json_encode(["message" => "Usuario con id: $id borrado con éxito"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        } catch (Exception $ex) {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error Interno, hubo un error al borrar el usuario, pero no es tu culpa"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

    } else {
        http_response_code(405); // Not Allowed
        echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
        exit;
    }
?>