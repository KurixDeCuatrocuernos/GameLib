<?php
    /**
     * Esta Función sirve para recoger todos los datos de cara a mostrar el perfil del usuario 
     */
    
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/../../database/db.php';

    iniciarSesionSiNoActiva();

    if (isset($_SESSION["user_id"])) {
        // Unimos los contenidos de las tablas users y users_data
        $sql = 'SELECT u.id AS user_id, u.name, u.email, u.role, ud.theme, ud.language
                FROM users u JOIN users_data ud ON u.id = ud.id
                WHERE u.id = ?'; // Este necesité pensarlo
        $result = ejecutarQuery($sql, [$_SESSION["user_id"]]);
        if (!empty($result)) {
            http_response_code(200); // OK
            echo json_encode($result[0]);
            exit;
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["error" => "No se ha encontrado al usuario con ese id"]);
            exit;
        }
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "No has iniciado sesión"]);
        exit;
    }
?>