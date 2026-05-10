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