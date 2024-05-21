<?php
require '../vendor/autoload.php';
require '../config.php';

use Firebase\JWT\JWT;

$config = require '../config.php';
$key = $config['jwt_secret_key'];
$issuer = $config['jwt_issuer'];
$audience = $config['jwt_audience'];
$algorithm = 'HS256';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Obtener los datos de la solicitud
$data = json_decode(file_get_contents("php://input"), true); // Cambiar a true para obtener un array asociativo
$username = $data['username'];
$password = $data['password'];
// $role = $data['role'];
// Verificar usuario y contraseña
$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($userId, $hashedPassword, $role);
$stmt->fetch();
$stmt->close();

if ($userId && password_verify($password, $hashedPassword)) {
    $payload = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => time(),
        "nbf" => time() + 10,
        "exp" => time() + 3600,
        "data" => [
            "userId" => $userId,
            "username" => $username,
            "role" => $role
        ]
    ];

    $jwt = JWT::encode($payload, $key, $algorithm);
    echo json_encode(["token" => $jwt]);
    echo $role;
} else {
    http_response_code(401);
    echo json_encode(["message" => "Invalid username or password"]);
}

$conn->close();
