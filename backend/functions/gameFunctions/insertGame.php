<?php
    /**
     * Esta Función inserta un juego a partir de los datos de IGDB API en la base de datos
     * Se requiere el id de IGDB, el título del juego, la fecha de lanzamiento y el JSON con los datos de la imagen
     */
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/gameFunctions.php';

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        $input = file_get_contents('php://input'); // Recogemos los datos con fetch
        $data = json_decode($input, true); // decodificamos los datos de json
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        if(!empty($data)) {
            if(
                !empty($data["igdb_id"]) && 
                !empty($data["cover"]) && 
                !empty($data["name"]) && 
                !empty($data["release_date"])
            ){   
                if (!ctype_digit((string)$data['igdb_id'])){
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "El id de Igdb no es válido"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
                $igdbId = (int)$data['igdb_id']; // id de igdb

                if (!is_string($data['name']) || $data['name'] === '') {
                    http_response_code(400);
                    echo json_encode(["message" => "El nombre está vacío o es inválido"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
                $name = trim($data["name"]); // título

                $fecha = DateTime::createFromFormat('Y-m-d', $data["release_date"]);
                if (!$fecha || $fecha->format('Y-m-d') !== $data["release_date"]) {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "La fecha no es convertible a tipo DATE"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
                $fecha = $fecha->format('Y-m-d'); // Formato fecha 

                if (empty($data['cover']) || !is_string($data['cover'])) {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "No hay cover o no es un cover válido"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }

                $cover = trim($data['cover']);

                if (!filter_var($cover, FILTER_VALIDATE_URL)) {
                    http_response_code(400);
                    echo json_encode(["message" => "El cover no es una URL válida"]);
                    exit;
                }

                // Aquí los datos están comprobados 
                
                try {
                    $sql = 'INSERT INTO games(igdb_id, cover, name, release_date) VALUES (?,?,?,?)';
                    ejecutarQuery($sql, [$igdbId, $cover, $name, $fecha]);
                    http_response_code(200); // OK
                    echo json_encode(["message" => "Juego guardado con éxito"]); // La redirección variará
                    exit;
                } catch (Exception $ex) {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Hubo un error al almacenar el juego en la base de datos"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Alguno de los parámetros requeridos está vacío"]); // Deberíamos redirigir al formulario y dar feedback
                exit;
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "El POST está vacío"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }
    } else {
        http_response_code(405); // Not Allowed
        echo json_encode(["message" => "No hay POST"]); // Deberíamos redirigir de vuelta al index
        exit;
    }
?>