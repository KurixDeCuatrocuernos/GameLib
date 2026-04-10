<?php
    require_once __DIR__."/../commonFunctions.php";

    iniciarSesionSiNoActiva(); // Recuperamos la sesión
    $_SESSION = []; // Vaciamos el contenido de la sesión
    session_destroy(); // Destruimos la sesión
    
    // $message = "Sesión cerrada"; 
    header('Location: ../../pages/usersPage.php'); // Redirigimos al login o al inicio
    exit;
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