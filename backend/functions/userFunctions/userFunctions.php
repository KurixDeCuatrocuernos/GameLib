<?php
    require_once __DIR__. '/../../database/db.php';
    require_once __DIR__ . '/../commonFunctions.php';

/**
 * Esta función sirve para recoger el id de un rol a partir de su name
 */
function getRoleIdByName($roleName) {
    $sql = 'SELECT * FROM roles WHERE name = ?';
    $result = ejecutarQuery($sql,[$roleName]);
    return $result;
}

/**
 * Esta función sirve para comprobar si un role de usuario corresponde a un administrador
 */
function checkAdminByUserRole($userRole) {
    $role = getRoleIdByName("admin");
    return $userRole === $role[0]["id"];
}

/**
 * Esta función sirve para comprobar si un role de usuario corresponde a un usuario normal
 */
function checkUserByUserRole($userRole) {
    $role = getRoleIdByName("user");
    return $userRole === $role[0]["id"];
}


?>