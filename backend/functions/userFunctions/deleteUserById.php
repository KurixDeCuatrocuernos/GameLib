<?php
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        if (!empty($_POST["id"])) {    
            $sql = 'DELETE FROM users WHERE id = ?';
            try{
                ejecutarQuery($sql, [trim($_POST["id"])]); // En teoría la base de datos borrará los datos en cascada
                http_response_code(200); // OK
                echo json_encode(["message" => "Usuario con id: ".trim($_POST["id"])."borrado con éxito"]); // Deberíamos redirigir al formulario y dar feedback
                exit;
            } catch (Exception $ex) {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error Interno, hubo un error al borrar el usuario, pero no es tu culpa"]); // Deberíamos redirigir al formulario y dar feedback
                exit;
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Alguno de los parámetros está vacío"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }
    } else {
        http_response_code(405); // Not Allowed
        echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
        exit;
    }
    
?>