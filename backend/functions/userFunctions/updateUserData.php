<?php
/**
 * Esta Función sirve para modificar los datos de un usuario a partir de los datos de un formulario
 */
require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

if($_SERVER["REQUEST_METHOD"]==='POST') {
    if (isset($_SESSION["user_id"])) {
        if (!empty($_POST)) {
            $sql = 'SELECT u.id AS u.name, u.email, u.role, u.password ud.theme, ud.language
                    FROM users u JOIN users_data ud ON u.id = ud.id
                    WHERE u.id = ?';
            $result = ejecutarQuery($sql [$_SESSION["user_id"]]);
            $userData = $result[0];
            $username = "";
            $email = "";
            $role = "";
            $password = "";
            $theme = "";
            $language = "";

            if (!empty($_POST["username"])) {
                if ($userData["username"] !== $_POST["username"]) {
                    $username = $_POST["username"];
                }    
            }
            if (!empty($_POST["email"])) {
                if ($userData["email"] !== $_POST["email"]) {
                    $email = $_POST["email"];
                }
            }
            if (!empty($_POST["role"])) {
                if ($userData["role"] !== $_POST["role"]) {
                    $role = $_POST["role"];
                }
            }
            if (!empty($_POST["oldPassword"]) && !empty($_POST["newPassword"])) {
                if (password_verify($_POST["oldPassword"], $userData["password"])){
                    if ($userData["password"] !== $_POST["newPassword"]) {
                        $password = $_POST["newPassword"];
                    } else {
                        // DAR FEEDBACK DE QUE ES LA MISMA CONTRASEÑA
                    }
                } else {
                    // DAR FEEDBACK SOBRE LAS CONTRASEÑAS
                }  
            }
            if (!empty($_POST["theme"])) {
                if ($userData["theme"] !== $_POST["theme"]) {
                    $theme = $_POST["theme"];
                }
            }
            if (!empty($_POST["language"])) {
                if ($userData["language"] !== $_POST["language"]) {
                    $language = $_POST["language"];
                }
            }
            // Comprobamos qué modificar
            if (empty($username) && empty($email) && empty($role) && empty($password) && empty($theme) && empty($language)) {
                http_response_code(200); // OK
                echo json_encode(["message" => "Nada se ha modificado"]); // Damos Feedback
                exit;
            } else {
                // MODIFICAMOS LOS DATOS
            }
            
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Alguno de los parámetros está vacío"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "No has iniciado sesión"]); // Redirigir al login
        exit;
    }
} else {
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
    exit;
}
?>