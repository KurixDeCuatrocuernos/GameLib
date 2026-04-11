<?php
/**
 * Esta es la función para iniciar sesión
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

$message = "";

salirSiHaySesion("../../pages/usersPage.php"); // Salimos si ya hay sesión activa

if ($_SERVER["REQUEST_METHOD"]=="POST") { // Si hay POST continuamos
    
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($password)) {

        if (empty($username)) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "El username y el email estan vacíos"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        } else {
            
            $sql = 'SELECT * FROM users WHERE ';

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $sql .='email = ?'; // Si es email buscamos email
            } else {
                $sql .= 'name = ?'; // Si es username buscamos por username
            }

            $result = ejecutarQuery($sql, [$username]); // ejecutamos la consulta

            if (empty($result)) { 
                http_response_code(401); // Unauthorized
                echo json_encode(["message" => "No se encuentra un usuario con ese email o username y contraseña"]); // Redirigimos al formulario y damos feedback GENÉRICO
                exit;
            } else {
                if (password_verify($password,$result[0]["password"])) {
                    $user = $result[0];
                    iniciarSesionSiNoActiva();
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["name"];
                    $_SESSION["role"] = $user["role"];
                    
                    http_response_code(200); // OK
                    echo json_encode(["message" => "Se ha iniciado sesión con éxito"]); // Redirigimos al formulario y damos feedback GENÉRICO                    
                    exit;
                } else {
                    http_response_code(401); // Unauthorized
                    echo json_encode(["message" => "No se encuentra un usuario con ese email o username y contraseña"]); // Redirigimos al formulario y damos feedback GENÉRICO
                    exit;
                }
            }
        }  
    } else {
        http_response_code(400); // Empty Data
        echo json_encode(["message" => "La contraseña está vacía"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
    exit;
}
?>