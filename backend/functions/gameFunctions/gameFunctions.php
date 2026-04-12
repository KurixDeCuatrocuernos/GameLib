<?php
    function isDate($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    function isJson($string) {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }  
?>