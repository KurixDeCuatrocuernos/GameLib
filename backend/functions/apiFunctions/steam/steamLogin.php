<?php
// Esta función es para que el usuario inicie sesión en su cuenta de Steam de forma segura
// OJO, hay que informar adecuadamente al usuario de que vamos a usar y guardar esa información

    require_once __DIR__.'/../../../database/db.php';
    require_once __DIR__.'/../../commonFunctions.php';

    // salirSiNoHaySesion('frontend/login'); // Redirigimos al Login

    $openid_url = "https://steamcommunity.com/openid/login";

    $params = [
        "openid.ns" => "http://specs.openid.net/auth/2.0",
        "openid.mode" => "checkid_setup",
        "openid.return_to" => "http://localhost/auth/steam/return.php",
        "openid.realm" => "http://localhost/",
        "openid.identity" => "http://specs.openid.net/auth/2.0/identifier_select",
        "openid.claimed_id" => "http://specs.openid.net/auth/2.0/identifier_select"
    ];

    header("Location: " . $openid_url . "?" . http_build_query($params));
    exit;

?>