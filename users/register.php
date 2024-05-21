<?php
require '../vendor/autoload.php';
require '../config.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener los datos de la solicitud
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$password = $data['password'];
$role = $data['role'];
// Validar datos
if (!empty($username) && !empty($password)) {
    // Codificar la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO users (username, password,role) VALUES (?, ?,?)");
    $stmt->bind_param('sss', $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo json_encode(["message" => "User created successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Invalid input"]);
}

$conn->close();
