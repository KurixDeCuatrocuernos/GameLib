<?php
/**
 * Endpoint para importar biblioteca de GOG desde un archivo CSV
 * 
 * Espera un archivo CSV con nombres de juegos en alguna columna
 * POST: multipart/form-data con campo 'csv_file'
 */

require_once __DIR__.'/../../../database/db.php'; // Importamos la conexión con la Bade de datos
require_once __DIR__.'/../../commonFunctions.php'; // Importamos las funciones comunes
require_once __DIR__.'/../../gameFunctions/gameFunctions.php'; // Importamos las junciones de games
require_once __DIR__.'/../igdb/igdbFunctions.php'; // Importamos las funciones de IGDB
require_once __DIR__.'/gogFunctions.php'; // Importamos las funciones de gog

// Habilitamos CORS para REACT:
header("Access-Control-Allow-Origin: http://localhost:5173"); // Dirección de React
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// DESACTIVADO EN DESARROLLO, PERO RECOMENDABLE EN PRODUCCIÓN PARA PREVENIR ATAQUES DE DDOS
set_time_limit(300); // Establecemos 5 minutos como límite para procesar toda la sincronización


iniciarSesionSiNoActiva();// Iniciar sesión y verificar usuario autenticado

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Forbidden
    echo json_encode([
        "message" => "Usuario no autenticado"
    ]); // // Habría que redirigir al login y dar feedback
    exit;
}

$userId = $_SESSION['user_id']; // Guardamos el id del usuario

$maxFileSize = 5 * 1024 * 1024; // Tamaño máximo de archivo: 5 MB 
$allowedMimeTypes = ['text/csv']; // Tipos MIME permitidos CSV y TXT
$maxRows = 10000; // Extensión máxima permitida 

if (!isset($_FILES['csv_file'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "No se recibió ningún archivo" 
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Verificamos que el archivo se ha subido correctamente
if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => "El archivo excede el tamaño máximo permitido",
        UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo del formulario",
        UPLOAD_ERR_PARTIAL => "El archivo se subió parcialmente",
        UPLOAD_ERR_NO_FILE => "No se subió ningún archivo",
        UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal",
        UPLOAD_ERR_CANT_WRITE => "Error al escribir el archivo en disco",
        UPLOAD_ERR_EXTENSION => "Extensión de PHP detuvo la subida"
    ];
    
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => $errors[$_FILES['csv_file']['error']] ?? "Error desconocido al subir el archivo"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Verificamos la extensión del archivo
$fileExt = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
if (!in_array($fileExt, ['csv', 'txt'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "Formato no válido. Solo se permiten archivos CSV o TXT"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Verificamos el tamaño del archivo
if ($_FILES['csv_file']['size'] > $maxFileSize) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "El archivo es demasiado grande"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Verificamos el tipo del archivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['csv_file']['tmp_name']);
// finfo_close($finfo); // finfo_close no necesario en PHP 8.1+ (deprecado desde 8.5)

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "El archivo tiene un tipo no válido"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Abrimos el archivo en modo lectura
$fileHandle = fopen($_FILES['csv_file']['tmp_name'], 'r');
if ($fileHandle === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "message" => "No se pudo leer el archivo"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Detectamos el delimitador (coma, punto y coma, o tabulador)
$firstLine = fgets($fileHandle);
rewind($fileHandle);

$delimiters = [',', ';', "\t"];
$detectedDelimiter = ',';

foreach ($delimiters as $delimiter) {
    if (strpos($firstLine, $delimiter) !== false) {
        $detectedDelimiter = $delimiter;
        break;
    }
}

// Leemos todas las filas del CSV
$csvContent = [];
$rowCount = 0;
while (($row = fgetcsv($fileHandle, 0, $detectedDelimiter)) !== false && $rowCount < $maxRows) {
    
    // Limpiamos cada campo (trim y eliminar BOM si existe)
    $row = array_map(function($field) {
        $field = preg_replace('/^\xEF\xBB\xBF/', '', $field); // Eliminamos BOM si existe
        return trim($field);
    }, $row);
    
    // Filtramos filas vacías
    if (empty(array_filter($row, function($cell) { return $cell !== ''; }))) {
        continue;
    }
    
    $csvContent[] = $row;
    $rowCount++;
}

// Verificamos que el número de filas no sea demasiado grande
if ($rowCount >= $maxRows) {
    fclose($fileHandle); // Cerramos el archivo antes de salir
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "El archivo es demasiado grande"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Verificamos que el archivo no contenga caracteres sospechosos
if (!isCsvSafe($csvContent)) {
    fclose($fileHandle); // Cerramos el archivo antes de salir
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "El CSV contiene fórmulas potencialmente peligrosas"]);
    exit; // Habría que redirigir al formulario y dar feedback
}

fclose($fileHandle); // Cerramos el archivo

// Verificamos que haya algún dato 
if (empty($csvContent)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "message" => "El archivo está vacío o no tiene datos válidos"
    ]); // Habría que redirigir al formulario y dar feedback
    exit;
}

// Intentaremos detectar la columna que contiene los nombres de los juegos
// Asumimos que la primera fila puede ser el encabezado o ya son datos (dos casuísticas)
$headers = null;
$dataStartRow = 0;

// Posibles nombres de columna para juegos
$gameColumnKeywords = ['title', 'game', 'name', 'product', 'nombre', 'juego', 'título', 'gamename'];

// Verificamos que la primera fila parezca un encabezado (que contenga alguna de nuestras palabras clave)
$firstRow = $csvContent[0];
$looksLikeHeader = false;
foreach ($firstRow as $cell) {
    $cellLower = strtolower(trim($cell));
    if (in_array($cellLower, $gameColumnKeywords) || 
        strpos($cellLower, 'game') !== false || 
        strpos($cellLower, 'title') !== false) {
        $looksLikeHeader = true;
        break;
    }
}

// Si se parece al encabezado guardamos la columna
if ($looksLikeHeader) {
    $headers = $firstRow;
    $dataStartRow = 1;
}

// Encontramos el índice de la columna de juegos
$gameColumnIndex = 0; // Por defecto, primera columna
if ($headers !== null) {
    foreach ($headers as $index => $columnName) {
        $columnLower = strtolower(trim($columnName));
        foreach ($gameColumnKeywords as $keyword) {
            if (strpos($columnLower, $keyword) !== false) {
                $gameColumnIndex = $index;
                break 2;
            }
        }
    }
}

// Detectamos la columna de plataforma
$platformColumnIndex = null;
$platformKeywords = ['platformlist', 'platform', 'store', 'source', 'launcher', 'provider'];

if ($headers !== null) {
    foreach ($headers as $index => $columnName) {
        $columnLower = strtolower(trim($columnName));
        foreach ($platformKeywords as $keyword) {
            if (strpos($columnLower, $keyword) !== false) {
                $platformColumnIndex = $index;
                break 2;
            }
        }
    }
}

global $conexion;
mysqli_begin_transaction($conexion); // Iniciamos una transacción para garantizar integridad

try {
    // Procesamos los juegos
    $inserted = 0; // Contador de juegos insertados
    $failed = 0; // Contador de Juegos fallidos
    $failedGames = []; // Lista de nombres de juegos fallidos
    $alreadyExists = 0; // Contador de Juegos que ya existen
    $excluded = 0; // Contador de juegos excluidos porque la plataforma no está soportada
    $excludedGames = []; // Lista de nombres de juegos excluidos
    
    foreach ($csvContent as $rowIndex => $row) {
        // Nos saltamos las filas de encabezado
        if ($rowIndex < $dataStartRow) {
            continue;
        }
        
        // Nos aseguramos de que la columna existe
        if (!isset($row[$gameColumnIndex])) {
            $failed++;
            $failedGames[] = "Fila " . ($rowIndex + 1) . ": Columna no válida";
            continue;
        }

        $gameName = trim($row[$gameColumnIndex]); // Recogemos el título del juego


        // Detectar plataforma para este juego
        $platform = null; // valor por defecto
        
        if ($platformColumnIndex !== null && isset($row[$platformColumnIndex])) {
            $platform = extractPlatformFromList($row[$platformColumnIndex]);
        }

        // Si la plataforma no es Steam, Epic o GOG, excluimos el juego y omitimos la fila
        if ($platform === null) {
            $excluded++;
            $excludedGames[] = "Fila " . ($rowIndex + 1) . ": " . $gameName . " (" . ($row[$platformColumnIndex] ?? 'plataforma desconocida') . ")";
            continue;
        }

        // Obtenemos o creamos el provider para esa plataforma
        $providerId = getOrCreateProvider($userId, $platform);
        
        if (empty($gameName)) {
            $failed++;
            $failedGames[] = "Fila " . ($rowIndex + 1) . ": Nombre vacío";
            continue;
        }
        
        $game = getGameByName($gameName); // Buscamos el juego en la base de datos

        // Si no existe, buscamos el juego en IGDB
        if (!$game) {
            try {
                $igdbResult = searchGameByName($gameName); // Buscamos por título
                // Si lo encontramos lo guardamos
                if (!empty($igdbResult['data'])) {
                    $igdbGame = $igdbResult['data'];
                    
                    $igdbId = $igdbGame['id'];
                    $name = $igdbGame['name'];
                    
                    // Revisamos el cover del juego
                    $cover = null;
                    if (isset($igdbGame['cover']) && is_array($igdbGame['cover'])) {
                        // Si cover es un array con 'url'
                        $cover = $igdbGame['cover']['url'] ?? null;
                    } elseif (is_string($igdbGame['cover'])) {
                        // Si cover es directamente un string (ID)
                        $cover = $igdbGame['cover'];
                    }                    
                    // Si tenemos una URL, nos aseguramos de que sea completa
                    if ($cover && !str_starts_with($cover, 'http')) {
                        $cover = "https:" . $cover;
                    }
                    // Si no hay cover, usamos una imagen por defecto
                    if (empty($cover)) {
                        $cover = "https://placehold.co/300x450?text=No+Cover";
                    }
                    
                    // Verificamos la fecha
                    $releaseDate = !empty($igdbGame['first_release_date'])
                        ? date('Y-m-d', $igdbGame['first_release_date'])
                        : date('Y-m-d');
                    
                    insertGame($igdbId, $cover, $name, $releaseDate); // Guardamos el juego en la base de datos
                    $game = getGameByName($name); // Recogemos el juego de nuestra base de datos
                }
            } catch (Exception $e) { // Si hay un error lo mostramos
                http_response_code(500); // Internal Server Error
                echo json_encode([
                    "message" => "Error buscando '$gameName' en IGDB: " . $e->getMessage()
                ]);
                exit;
            }
        }
        
        // Si encontramos el juego, lo insertamos en users_games
        if ($game) {
            $sql = 'INSERT INTO users_games (game_id, user_id, user_provider_id)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE game_id = game_id';
            
            $affected = ejecutarQuery($sql, [$game['id'], $userId, $providerId]);
            
            // Si affected es 0, significa que ya existía (ON DUPLICATE KEY actualizó el dato)
            if ($affected === 0) {
                $alreadyExists++;
            } else {
                $inserted++;
            }
        } else {
            $failed++;
            $failedGames[] = $gameName;
        }
    }

    mysqli_commit($conexion); // Si todo ha ido bien confirmamos la transacción

    http_response_code(200); // OK
    echo json_encode([
        "message" => "Importación de GOG completada",
        "inserted" => $inserted,
        "already_exists" => $alreadyExists,
        "failed" => $failed,
        "excluded" => $excluded,
        "total_processed" => $inserted + $alreadyExists + $failed,
        "failed_games" => $failedGames,
        "excluded_games" => $excludedGames
    ]); // Habría que volver a la biblioteca mostrando los nuevos juegos insertados (filtro de GOG o algo así)
} catch (Exception $ex) {
    mysqli_rollback($conexion); // Si algo ha ido mal revertimos la transacción
    error_log("Error en la importación de GOG: ".$ex->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "message" => "Error durante la importación. No se ha añadido ningún juego.",
    ]); // Habría que volver al formulario y dar feedback
}


exit; // Fin de la sincronización

?>