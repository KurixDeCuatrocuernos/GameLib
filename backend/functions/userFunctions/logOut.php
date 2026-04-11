<?php
    require_once __DIR__."/../commonFunctions.php";

    iniciarSesionSiNoActiva(); // Recuperamos la sesión
    $_SESSION = []; // Vaciamos el contenido de la sesión
    session_destroy(); // Destruimos la sesión
    
    // $message = "Sesión cerrada"; 
    http_response_code(200); // OK
    echo json_encode(["message" => "Sesión cerrada"]); 
    exit;
?>