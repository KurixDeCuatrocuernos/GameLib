<?php
    /**
     * Esta Función inserta un juego a partir de los datos de IGDB API en la base de datos
     * Se requiere el id de IGDB, el título del juego, la fecha de lanzamiento y el JSON con los datos de la imagen
     */
    require_once __DIR__.'/../../database/db.php';
    require_once __DIR__.'/../commonFunctions.php';
    require_once __DIR__.'/gameFunctions.php';

    if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
        $input = file_get_contents('php://input'); // Recogemos los datos con fetch
        $data = json_decode($input, true); // decodificamos los datos de json
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Los datos enviados no están en formato JSON"]); // Deberíamos redirigir al formulario y dar feedback
            exit;
        }

        if(!empty($data)) {
            if(
                isset($data["igdb_id"]) && 
                isset($data["cover_data"]) && 
                isset($data["name"]) && 
                isset($data["release_date"])
            ){  
                $igdbId = trim($data["igdb_id"]); // id de igdb
                $name = trim($data["name"]); // título
                $fecha = DateTime::createFromFormat('Y-m-d', $data["release_date"]);
                if (!$fecha) {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "la fecha no es convertible a tipo DATE"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
                $fecha = $fecha->format('Y-m-d'); // Formato fecha
                $cover = json_encode($data["cover_data"]); // formato JSON
                // Ya tenemos los datos comprobados y guardados
                
                if ($cover === false) {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "Error al convertir en JSON"]); // Deberíamos redirigir al formulario y dar feedback
                    exit;
                }
                
                try {
                    $sql = 'INSERT INTO games(igdb_id, cover_data, name, release_date) VALUES (?,?,?,?)';
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