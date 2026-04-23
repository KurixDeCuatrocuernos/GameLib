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
 * Esta función devuelve el theme-name a partir del id de usuario
 */
function getThemeNameByUserId ($userId) {
    $sql = 'SELECT th.name FROM users_data ud JOIN themes th ON ud.theme = th.id WHERE ud.id = ?';
    $result = ejecutarQuery($sql, [$userId]);
    return $result[0]["name"] ?? null;
}
/**
 * Esta función devuelve el language-name a partir del id de usuario
 */
function getLanguageNameByUserId ($userId) {
    $sql = 'SELECT l.name FROM users_data ud JOIN languages l ON ud.theme = l.id WHERE ud.id = ?';
    $result = ejecutarQuery($sql, [$userId]);
    return $result[0]["name"] ?? null;
}
/**
 * Esta función devuelve toda la información de la tabla themes
 */
function getAllThemes () {
    $sql = 'SELECT * FROM themes';
    $result = ejecutarQuery($sql, []);
    return $result;
} 
/**
 * Esta función devuelve toda la información de la tabla languages
 */
function getAllLanguages () {
    $sql = 'SELECT * FROM languages';
    $result = ejecutarQuery($sql, []);
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