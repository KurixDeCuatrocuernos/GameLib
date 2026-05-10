<?php
/**
 * Endpoint para que el usuario pueda borrar su propia cuenta si lo desea
 */

require_once __DIR__.'/../../database/db.php';
require_once __DIR__.'/../commonFunctions.php';

// Implementamos CORS para las consultas del frontend
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Al ser borrado usamos delete
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== 'DELETE') { // Deberíamos usar DELETE en lugar de POST
    http_response_code(405); // Not Allowed
    echo json_encode(["message" => "Method not allowed, use DELETE"]); // Deberíamos redirigir de vuelta al index
    exit;
}

iniciarSesionSiNoActiva(); // Iniciamos sesión para acceder a $_SESSION

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "ID requerido"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

$id = $_SESSION['user_id'];

if (!is_numeric($id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "ID inválido"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

$id = (int)$id;

$sql = 'DELETE FROM users WHERE id = ?';
try{
    $result = ejecutarQuery($sql, [$id]); // En teoría la base de datos borrará los datos en cascada
    // Comprobamos que se haya eliminado el usuario
    if ($result === 0) {
        http_response_code(404);
        echo json_encode(["message" => "No se pudo eliminar el usuario"]);
        exit;
    }
    $_SESSION = []; // Vaciamos la sesión tras el borrado
    session_destroy(); // Destruímos la sesión

    http_response_code(200); // OK
    echo json_encode(["message" => "Usuario con id: $id borrado con éxito"]); // Deberíamos redirigir al formulario y dar feedback
    exit;
} catch (Exception $ex) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error Interno, hubo un error al borrar el usuario: ".$ex->getMessage()]); // Deberíamos redirigir al formulario y dar feedback
    exit;
}

?>