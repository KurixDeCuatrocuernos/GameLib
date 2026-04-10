<?php
/**
 * Esta es la función para dar de alta a un usuario
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"]=="POST") { // Si hay POST continuamos
    
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($password)) {
    
        $sql = 'SELECT id FROM users WHERE name = ? OR email = ?';
        $result = ejecutarQuery($sql, [$username, $email]);
        if(count($result) > 0) {
            $message = "Ese usuario ya existe"; // Deberíamos redirigir al formulario y dar feedback
        } else {
            $sql = 'INSERT INTO users(name, email, password, role) VALUES (?,?,?,?)';
            $password = password_hash($password, PASSWORD_BCRYPT);
            $result = ejecutarQuery($sql,[$username, $email, $password, 1]);
            if (!$result) {
                $message = "Hubo un problema al crear la cuenta"; // Deberíamos dar Feedback del error interno
            } else {
                $message = "Cuenta creada con éxito"; // Deberíamos realizar el proceso de login
            }
        }   
    } else {
        $message = "Alguno de los parámetros está vacío"; // Deberíamos redirigir al formulario y dar feedback
    }
} else {
    $message = "No hay POST"; // Deberíamos redirigir de vuelta al index
}

?>
<!-- Esta parte es temporal -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1><?php
        echo $message
    ?></h1>
</body>
</html>