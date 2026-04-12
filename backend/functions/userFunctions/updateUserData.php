<?php
/**
 * Esta Función sirve para modificar los datos de un usuario a partir de los datos de un formulario
 * 
 */
require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

if($_SERVER["REQUEST_METHOD"]==='POST') {
    $input = file_get_contents('php://input'); // Recogemos los datos en JSON
    $data = json_decode($input, true);// Decodificamos los datos
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        http_response_code(400); // Bad request
        echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }

    if (!isset($_SESSION["user_id"])) { 
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "No has iniciado sesión"]); // Redirigir al login
        exit;
    }
    $sql = 'SELECT u.id, u.name, u.email, u.role, u.password, ud.theme, ud.language
            FROM users u JOIN users_data ud ON u.id = ud.id
            WHERE u.id = ?';
    $result = ejecutarQuery($sql, [$_SESSION["user_id"]]);
    $userData = $result[0]; // Viejos datos
    // Nuevos datos
    $username = ""; // NOMBRE DE USUARIO
    $email = ""; // CORREO ELECTRÓNICO
    $role = ""; // ROLE
    $password = ""; // CONTRASEÑA (SE REQUERIRÁN LA ANTIGUA Y LA NUEVA Y QUE NO SEAN IGUALES)
    $theme = ""; // TEMA DE LA APP
    $language = ""; // LENGUAJE DE LA APP

    // USERNAME
    if (!empty($data["username"])) {
        if ($userData["name"] !== trim($data["username"])) {
            $username = trim($data["username"]);
        }    
    }
    // EMAIL
    if (!empty($data["email"])) {
        if (filter_var(trim($data["email"]), FILTER_VALIDATE_EMAIL)) {
            if ($userData["email"] !== trim($data["email"])) {
                $email = trim($data["email"]);
            }
        } else {
            // DAR FEEDBACK DEL EMAIL INVÁLIDO
        }
    }
    // ROLE (admin)
    if (!empty($data["role"]) && (int)$_SESSION["role"] == 2) {
        if ($userData["role"] !== $data["role"]) {
            $role = (int)$data["role"];
        }
    }
    // PASSWORD
    if (!empty($data["oldPassword"]) && !empty($data["newPassword"])) { // Si están ambas contraseñas continuamos
        if (password_verify($data["oldPassword"], $userData["password"])){
                $password = password_hash($data["newPassword"], PASSWORD_BCRYPT);
        } else {
            // DAR FEEDBACK DE QUE LA CONTRASEÑA NO ES CORRECTA
        }
    }   
    // THEME
    if (!empty($data["theme"])) {
        if ($userData["theme"] !== $data["theme"]) {
            $theme = (int)$data["theme"];
        }
    }
    // LANGUAGE
    if (!empty($data["language"])) {
        if ($userData["language"] !== $data["language"]) {
            $language = (int)$data["language"];
        }
    }
    // Comprobamos qué modificar
    if (empty($username) && empty($email) && empty($role) && empty($password) && empty($theme) && empty($language)) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Nada se ha modificado"]); // Damos Feedback
        exit;
    }

    try { 
        mysqli_begin_transaction($conexion); // Iniciamos la transacción

        if (!empty($username) || !empty($email) || !empty($role) || !empty($password)) { // Si alguno tiene datos lo actualizamos
            $sql = 'UPDATE users SET ';
            $sets = [];
            $values = [];
            
            if ($username !== ""){
                $sets[] = 'name = ?';
                $values[] = $username;
            } 

            if ($email !== "") {
                $sets[] = 'email = ?';
                $values[] = $email;
            }

            if ($role !== "") {
                $sets[] = 'role = ?';
                $values[] = $role;
            }

            if ($password !== "") {
                $sets[] = 'password = ?';
                $values[] = $password;
            }
            $sql .= implode(", ",$sets);
            $sql .= ' WHERE id = ?';

            ejecutarQuery($sql, [...$values,$_SESSION["user_id"]]);
        }

        if ($theme !== "" || $language !== ""){ // Si alguno de los users_data se modificó, lo modificaremos
            $sql = 'UPDATE users_data SET ';
            $sets = [];
            $values = [];
            
            if ($theme !== "") {
                $sets[] = 'theme = ?';
                $values[] = $theme;
            }  
            
            if ($language !== "") {
                $sets[] = 'language = ?';
                $values[] = $language;
            }
            
            $sql .= implode(", ",$sets);
            $sql .= ' WHERE id = ?';
            ejecutarQuery($sql, [...$values, $_SESSION["user_id"]]); 
        } 

        mysqli_commit($conexion); // Llevamos a cabo la transacción
        http_response_code(200); // OK
        echo json_encode(["message" => "Datos actualizados con éxito"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    } catch (Exception $ex) {
        mysqli_rollback($conexion); // Si algo sale mal anulamos transacción
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Hubo un problema interno al actualizar el tema de la aplicación o el lenguaje"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }          
} else {
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
    exit;
}
?>