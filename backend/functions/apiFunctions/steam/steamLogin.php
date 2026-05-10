<?php
// Esta función es para que el usuario inicie sesión en su cuenta de Steam de forma segura

require_once __DIR__.'/../../../database/db.php';
require_once __DIR__.'/../../commonFunctions.php';

// Verificar que el usuario está logueado en tu app
salirSiNoHaySesion('http://localhost:5173/login');

$ngrokURL = "https://enroll-owl-chewable.ngrok-free.dev"; // URL de Ngrok

$openid_url = "https://steamcommunity.com/openid/login"; // URL de Steam

$params = [
    "openid.ns" => "http://specs.openid.net/auth/2.0",
    "openid.mode" => "checkid_setup",
    "openid.return_to" => $ngrokURL . "/steam-callback", // Redirección tras el login de Steam
    "openid.realm" => $ngrokURL . "/",
    "openid.identity" => "http://specs.openid.net/auth/2.0/identifier_select",
    "openid.claimed_id" => "http://specs.openid.net/auth/2.0/identifier_select"
];

// Redirigimos a Steam
header("Location: " . $openid_url . "?" . http_build_query($params));
exit;
?>