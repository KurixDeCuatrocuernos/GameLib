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
            $message = "El username y el email estan vacíos"; // Deberíamos redirigir al formulario y dar feedback
        } else {
            
            $sql = 'SELECT * FROM users WHERE ';

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $sql .='email = ?'; // Si es email buscamos email
            } else {
                $sql .= 'name = ?'; // Si es username buscamos por username
            }

            $result = ejecutarQuery($sql, [$username]); // ejecutamos la consulta

            if (!count($result) > 0) {
                $message = "No se encuentra un usuario con ese email o username"; // Redirigimos al formulario y damos feedback GENÉRICO
            } else {
                if (password_verify($password,$result[0]["password"])) {
                    $user = $result[0];
                    iniciarSesionSiNoActiva();
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["name"];
                    $_SESSION["role"] = $user["role"];

                    header("Location: ../../pages/usersPage.php"); // Redirigiremos al inicio y mostraremos que la sesión está activa
                    exit;
                } else {
                    $message = 'Las contraseñas no coinciden'; // Redirigimos al formulario y damos feedback GENÉRICO
                }
            }

        }  
    } else {
        $message = "La contraseña está vacía"; // Deberíamos redirigir al formulario y dar feedback
    }
} else {
    $message = "No hay POST"; // Deberíamos redirigir de vuelta al index
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1><?= $message ?></h1>
</body>
</html>