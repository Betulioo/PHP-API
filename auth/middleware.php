<?php
require '../vendor/autoload.php';
require '../config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function jwtMiddleware()
{
    $config = require '../config.php';
    $key = $config['jwt_secret_key'];
    $algorithm = 'HS256';

    $allHeaders = getallheaders();

    if (!isset($allHeaders['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Token not provided", "debug" => $allHeaders]);
        exit;
    }

    $authHeader = $allHeaders['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key($key, $algorithm));
        $_SERVER['user'] = $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token", "debug" => $e->getMessage()]);
        exit;
    }
}
