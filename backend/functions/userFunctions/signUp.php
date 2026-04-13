<?php
/**
 * Esta es la función para dar de alta a un usuario
 */
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';

    if ($_SERVER["REQUEST_METHOD"]==="POST") { // Si hay POST continuamos
        
        $input = file_get_contents("php://input"); // Recogemos los datos en JSON
        $data = json_decode($input, true); // Decodificamos los datos

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        $username = trim($data["username"] ?? "");
        $email = trim($data["email"] ?? "");
        $password = trim($data["password"] ?? "");

        if (!empty($username) && !empty($email) && !empty($password)) {

            $sql = 'SELECT id FROM users WHERE name = ? OR email = ?';
            $result = ejecutarQuery($sql, [$username, $email]);
            if(count($result) > 0) {
                http_response_code(409); // Conflict
                echo json_encode(["message" => "Ese usuario ya existe"]); // Deberíamos redirigir al formulario y dar feedback
                exit;
            } else {
                // Creamos el usuario mediante una transacción
                mysqli_begin_transaction($conexion);
                try {
                    $sql = 'INSERT INTO users(name, email, password, role) VALUES (?,?,?,?)';
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $userId = ejecutarQuery($sql,[$username, $email, $passwordHash, 1]);
                    // Creamos los datos en users_data
                    $sql = 'INSERT INTO users_data(id, theme, language) VALUES (?, ?, ?)';
                    ejecutarQuery($sql, [$userId, 1, 1]); // Valores por defecto
                    // Si todo ha ido bien realizamos la transacción
                    mysqli_commit($conexion);
                    // Devolvemos confirmación
                    http_response_code(201); // Created
                    echo json_encode(["message" => "Se ha creado al usuario con éxito"]);
                } catch (Exception $e) {
                    mysqli_rollback($conexion);
                    http_response_code(500); // Internal server error
                    echo json_encode(["message" => "Error al crear la cuenta"]); // Deberíamos dar Feedback del error interno
                }
                mysqli_close($conexion);
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