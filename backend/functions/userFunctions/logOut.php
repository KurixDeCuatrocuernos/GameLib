<?php
require_once __DIR__."/../commonFunctions.php";

// Habilitamos CORS para REACT:
header("Access-Control-Allow-Origin: http://localhost:5173"); // Dirección de React
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Revisamos si la petición es Options (para probar la conexión)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200); // OK
    exit;
}
    
iniciarSesionSiNoActiva(); // Recuperamos la sesión
$_SESSION = []; // Vaciamos el contenido de la sesión
session_destroy(); // Destruimos la sesión

// $message = "Sesión cerrada"; 
http_response_code(200); // OK
echo json_encode(["message" => "Sesión cerrada"]); 
exit;

?>