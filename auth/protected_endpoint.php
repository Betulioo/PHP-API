<?php
require '../vendor/autoload.php';
require '../config.php';
require '../auth/middleware.php';

jwtMiddleware();

echo json_encode(["message" => "Access granted", "user" => $_SERVER['user']]);
