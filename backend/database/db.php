<?php
    // Conexión con la Base de Datos de MariaDB de Docker
    $host = "host.docker.internal";
    $user = "root"; // Mejor no usar root en producción
    $pass = "12345";
    $db = "gamelib";

    global $conexion;

    $conexion = mysqli_connect($host, $user, $pass, $db);

    if(!$conexion) {
        die("Error al conectar con la Base de Datos");
    }

    mysqli_set_charset($conexion,'utf8mb4');

?>