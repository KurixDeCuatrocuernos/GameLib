<?php
    require_once __DIR__.'/igdbFunctions.php';

    header("Access-Control-Allow-Origin: http://localhost:5173"); // Dirección de React
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header('Content-Type: application/json');

    // Manejar petición OPTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200); // OK
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); // Method not Allowed
        echo json_encode([
            "message" => "Se Esperaba GET y en su lugar se recibió: ".$_SERVER['REQUEST_METHOD'] 
        ]); // Habría que redirigir al formulario y dar feedback
        exit;
    }

    $searchTerm = trim($_GET['search'] ?? '');

    if (empty($searchTerm)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "La consulta está vacía"]);
        exit;
    }

    $response = searchIgdbGameByName($searchTerm,10);

    if (empty($response) || !is_array($response)) {
        http_response_code(200); // Consulta correcta, pero vacía
        echo json_encode([
            "message" => ["No se ha encontrado el juego"]
        ]); // Habría que redirigir al formulario y dar feedback
        exit;
    }

    $gameList = [];
    foreach ($response as $game) {
        $gameList[] = $game; // Devolvemos el juego completo
    }

    http_response_code(200); // Consulta correcta, pero vacía
    echo json_encode([
        "message" => $gameList
    ]); // Habría que redirigir al formulario y dar feedback
    exit;

?>