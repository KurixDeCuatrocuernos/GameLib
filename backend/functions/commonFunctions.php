<?php
/**
 * Este documento es para almacenar funciones genéricas relacionadas con la base de datos
 */

require_once __DIR__.'/../database/db.php';

/**
 * Si la sesión no está activa la inicia 
 */
function iniciarSesionSiNoActiva() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
/**
 * Si la sesión ya está iniciada hace rdirige al inicio
 */
function salirSiHaySesion ($ruta) {
    iniciarSesionSiNoActiva();
    if(isset($_SESSION["user_id"])) {
        // REDIRIGIR AL INDEX
        header('Location: '.$ruta);
        exit;
    }
}
/**
 * Si la sesión no está iniciada redirige al login
 */
function salirSiNoHaySesion ($ruta) {
    iniciarSesionSiNoActiva(); 
    if(!isset($_SESSION["user_id"])) {
        header('Location: '.$ruta);
        exit;
    }
}

/**
 * Función para ejecutar una consulta sql a partir de la consulta y los valores que usemos en la consulta
 */
function ejecutarQuery(string $sql, array $valores = []) {
    global $conexion; // Usa la variable global de conexión ya creada

    $stmt = mysqli_prepare($conexion, $sql); // Prepara la consulta SQL para evitar inyección
    if (!$stmt) { // Si falla la preparación
        throw new Exception("Error en prepare: " . mysqli_error($conexion)); // Lanza excepción con el error
    }

    if ($valores) { // Si hay valores para enlazar a la consulta
        $tipos = obtenerTipos($valores); // Genera la cadena de tipos automáticamente
        if (!mysqli_stmt_bind_param($stmt, $tipos, ...$valores)) { // Asocia los valores a la consulta preparada
            $err = mysqli_stmt_error($stmt); // Guarda el error
            mysqli_stmt_close($stmt); // Cierra el statement antes de salir
            throw new Exception("Error en bind_param: " . $err); // Lanza excepción
        }
    }

    if (!mysqli_stmt_execute($stmt)) { // Ejecuta la consulta preparada
        $err = mysqli_stmt_error($stmt); // Guarda el error si falla
        mysqli_stmt_close($stmt); // Cierra el statement
        throw new Exception("Error en execute: " . $err); // Lanza excepción
    }

    $primera = strtoupper(strtok(ltrim($sql), " \t\r\n")); // Obtiene la primera palabra de la consulta
    $esResultset = ( // Determina si la consulta devuelve un conjunto de resultados
        $primera === 'SELECT' ||
        $primera === 'WITH' ||
        $primera === 'SHOW' ||
        $primera === 'DESCRIBE' ||
        $primera === 'EXPLAIN'
    );

    if ($esResultset) { // Si la consulta devuelve filas
        $resultado = mysqli_stmt_get_result($stmt); // Obtiene el resultado como objeto mysqli_result
        if ($resultado === false) { // Si falla (por ejemplo, sin mysqlnd)
            $err = mysqli_stmt_error($stmt); // Guarda el error
            mysqli_stmt_close($stmt); // Cierra el statement
            throw new Exception("get_result no disponible o falló: " . $err); // Lanza excepción
        }
        $datos = mysqli_fetch_all($resultado, MYSQLI_ASSOC); // Obtiene todas las filas como array asociativo
        mysqli_stmt_close($stmt); // Cierra el statement
        return $datos; // Devuelve las filas
    }

    if ($primera === 'INSERT') { // Si es una inserción
        $id = mysqli_insert_id($conexion); // Obtiene el último ID autoincrement generado
        mysqli_stmt_close($stmt); // Cierra el statement
        return $id; // Devuelve el ID
    }

    $filas = mysqli_stmt_affected_rows($stmt); // Obtiene número de filas afectadas (UPDATE, DELETE, etc.)
    mysqli_stmt_close($stmt); // Cierra el statement
    return $filas; // Devuelve filas afectadas
}

/**
 * Función auxiliar para generar los tipos de cada valor proporcionado en ejecutarQuery()
 */
function obtenerTipos(array $valores): string
{
    $tipos = ''; // Inicializa la cadena de tipos

    foreach ($valores as $v) { // Recorre cada valor recibido
        if (is_int($v) || is_bool($v)) { // Si es entero o booleano
            $tipos .= 'i'; // Tipo entero
        } elseif (is_float($v)) { // Si es decimal
            $tipos .= 'd'; // Tipo double
        } elseif (is_null($v)) { // Si es null
            $tipos .= 's'; // Se pasa como string; MySQL lo interpretará como NULL
        } else { // Cualquier otro tipo (normalmente string)
            $tipos .= 's'; // Tipo string
        }
    }

    return $tipos; // Devuelve la cadena final de tipos
}

/**
 * Convierte un array a una tabla de HTML
 */
function arrayToTable(array $datos, array $columnasOpcionales = []): string
{
    if (empty($datos)) {
        return '<table><tr><td>No hay datos</td></tr></table>';
    }

    // Detectar columnas automáticamente si no se pasan
    if (empty($columnasOpcionales)) {
        $columnas = array_keys($datos[0]);
        $cabeceras = array_combine($columnas, $columnas);
    } else {
        $cabeceras = $columnasOpcionales;
        $columnas = array_keys($columnasOpcionales);
    }

    $html = '<table border="1">';
    $html .= '<thead><tr>';

    foreach ($cabeceras as $clave => $titulo) {
        $html .= '<th>' . htmlspecialchars($titulo) . '</th>';
    }

    $html .= '</tr></thead><tbody>';

    foreach ($datos as $fila) {
        $html .= '<tr>';
        foreach ($columnas as $col) {
            $valor = $fila[$col] ?? '';
            $html .= '<td>' . htmlspecialchars((string)$valor) . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}

?>