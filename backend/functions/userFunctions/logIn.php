<?php
/**
 * Esta es la función para iniciar sesión
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

// Salimos si ya hay sesión activa: REDIRIGIR AL FRONT
salirSiHaySesion("../../pages/usersPage.php");

if ($_SERVER["REQUEST_METHOD"] === 'POST') { // Si hay POST continuamos

    $input = file_get_contents('php://input'); // Recogemos los datos en JSON
    $data = json_decode($input, true); // Decodificamos los datos
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        http_response_code(400); // Bad request
        echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
    
    $username = trim($data["username"] ?? ""); // Recogemos el usuario
    $password = trim($data["password"] ?? ""); // Recogemos la contraseña

    if (empty($password) || empty($username)) { // Si la contraseña está vacía salimos 
        http_response_code(400); // Empty Data
        echo json_encode(["message" => "Username/email o contraseña requeridos"]); // Deberíamos redirigir al formulario y dar feedback
        exit;
    }
            
    $sql = 'SELECT * FROM users WHERE ';

    if (filter_var($username, FILTER_VALIDATE_EMAIL)) { // comprobamos que sea un email
        $sql .='email = ?'; // Si es email buscamos email
    } else {
        $sql .= 'name = ?'; // Si es username buscamos por username
    }

    $result = ejecutarQuery($sql, [$username]); // ejecutamos la consulta

    if (!$result || !password_verify($password,$result[0]["password"])) { // Si no hay datos, no se ha encontrado tal usuario, y si las contraseñas no coinciden no se inicia sesión
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Credenciales incorrectas"]); // Redirigimos al formulario y damos feedback GENÉRICO
        exit;
    }
    // Si estamos aquí, todo ha ido bien y se inicia la sesión
    $user = $result[0];
    iniciarSesionSiNoActiva();
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["name"];
    $_SESSION["role"] = $user["role"];
    
    http_response_code(200); // OK
    echo json_encode(["message" => "Se ha iniciado sesión con éxito"]); // Redirigimos al formulario y damos feedback GENÉRICO                    
    exit;

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
    exit;
}
?>