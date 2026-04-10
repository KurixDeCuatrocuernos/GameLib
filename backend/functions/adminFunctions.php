<?php
    require_once __DIR__.'/../database/db.php';
    require_once __DIR__.'/./userFunctions/userFunctions.php';
    require_once __DIR__.'/../functions/commonFunctions.php';

    iniciarSesionSiNoActiva();

    function getUsersData () {

        if (isset($_SESSION['role'])) { // Revisamos que haya un role en la sesión
            if (checkAdminByUserRole($_SESSION['role'])) { // Comprobamos que el usuario sea un administrador
                $sql = 'SELECT * FROM users';
                    $result = ejecutarQuery($sql, []);
                    return $result;
            } else {
                return []; // El usuario no es admin
            } 
        } else {
            return []; // No hay role guardado en la sesión
        }
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
    <h1><?php
        $message;
    ?></h1>
</body>
</html>