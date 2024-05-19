<?php
require_once "../config.php";

try {
    // Conexión a la base de datos
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Definimos el nombre de la tabla y sus columnas
    $tableName = "categories";
    $tableColumns = ["category_name"];

    // Obtenemos el método HTTP de la solicitud (GET, POST, PUT, DELETE)
    $method = $_SERVER['REQUEST_METHOD'];

    // Inicializamos un array vacío para almacenar la respuesta
    $response = [];

    // Utilizamos un switch para manejar la petición HTTP
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $tableName);
            break;

        case 'PUT':
            handlePutRequest($conn, $tableName, $tableColumns);
            break;

        case 'POST':
            handlePostRequest($conn, $tableName, $tableColumns);
            break;

        case 'DELETE':
            handleDeleteRequest($conn, $tableName);
            break;

        default:
            $response = ["METHOD" => $method, "SUCCESS" => false, "ERROR" => "Method not supported"];
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    // Captura y manejo de excepciones
    echo json_encode(["SUCCESS" => false, "ERROR" => $e->getMessage()]);
} finally {
    // Cierre de la conexión a la base de datos
    $conn->close();
}

// Función para manejar solicitudes GET
function handleGetRequest($conn, $tableName)
{
    $response = [];
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            $stmt = $conn->prepare("SELECT * FROM $tableName WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $response = ["DATA" => $row, "METHOD" => "GET", "SUCCESS" => true];
            $stmt->close();
        } else {
            $response = ["METHOD" => "GET", "SUCCESS" => false, "ERROR" => "The parameter id must be an integer"];
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $response = ["DATA" => $rows, "METHOD" => "GET", "SUCCESS" => true];
        $stmt->close();
    }
    echo json_encode($response);
}

// Función para manejar solicitudes PUT
function handlePutRequest($conn, $tableName, $tableColumns)
{
    $response = [];
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
            $validData = validateAndSanitizeData($data, $tableColumns);

            if ($validData['success']) {
                $sql = "UPDATE $tableName SET " . implode(", ", $validData['values']) . " WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $types = str_repeat('s', count($tableColumns)) . 'i';
                $params = array_merge(array_values($validData['data']), [$id]);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();

                if ($stmt->affected_rows == 1) {
                    $updatedRecordQuery = "SELECT * FROM $tableName WHERE id = ?";
                    $stmt = $conn->prepare($updatedRecordQuery);
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $updatedRecord = $stmt->get_result();
                    $response = ["METHOD" => "PUT", "SUCCESS" => true, "DATA" => $updatedRecord->fetch_assoc()];
                } else {
                    $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "$tableName not found"];
                }
                $stmt->close();
            } else {
                $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => $validData['error']];
            }
        } else {
            $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "Invalid ID parameter"];
        }
    } else {
        $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "ID parameter is required"];
    }
    echo json_encode($response);
}

// Función para manejar solicitudes POST
function handlePostRequest($conn, $tableName, $tableColumns)
{
    $data = json_decode(file_get_contents("php://input"), true);
    $validData = validateAndSanitizeData($data, $tableColumns);

    if ($validData['success']) {
        $sql = "INSERT INTO $tableName (" . implode(", ", $validData['columns']) . ") VALUES (" . str_repeat('?,', count($validData['columns']) - 1) . "?);";
        $stmt = $conn->prepare($sql);
        $types = str_repeat('s', count($validData['columns']));
        $stmt->bind_param($types, ...$validData['values']);
        $createResult = $stmt->execute();

        if ($createResult) {
            $newRecordId = $stmt->insert_id;
            $getRecordQuery = "SELECT * FROM $tableName where id = ?";
            $stmt = $conn->prepare($getRecordQuery);
            $stmt->bind_param('i', $newRecordId);
            $stmt->execute();
            $newRecord = $stmt->get_result();
            $response = ["METHOD" => "POST", "SUCCESS" => true, "DATA" => $newRecord->fetch_assoc()];
        } else {
            $response = ["METHOD" => "POST", "SUCCESS" => false, "ERROR" => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ["METHOD" => "POST", "SUCCESS" => false, "ERROR" => $validData['error']];
    }
    echo json_encode($response);
}

// Función para manejar solicitudes DELETE
function handleDeleteRequest($conn, $tableName)
{
    $response = [];
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            // Primero eliminamos todos los registros en product_invoice relacionados con los productos de esta categoría
            $deleteProductInvoicesSql = "DELETE pi FROM product_invoice pi JOIN products p ON pi.product_id = p.id WHERE p.category_id = ?";
            $stmt = $conn->prepare($deleteProductInvoicesSql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            // Luego eliminamos todos los productos relacionados con esta categoría
            $deleteProductsSql = "DELETE FROM products WHERE category_id = ?";
            $stmt = $conn->prepare($deleteProductsSql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            // Ahora eliminamos la categoría
            $sql = "DELETE FROM $tableName WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();

            if ($stmt->affected_rows == 1) {
                $response = ["METHOD" => "DELETE", "SUCCESS" => true, "MESSAGE" => "$tableName deleted"];
            } else {
                $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "$tableName not found"];
            }
            $stmt->close();
        } else {
            $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "Invalid ID parameter"];
        }
    } else {
        $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "ID parameter is required"];
    }
    echo json_encode($response);
}

// Función para validar y sanitizar datos
function validateAndSanitizeData($data, $tableColumns)
{
    $columns = [];
    $values = [];
    foreach ($tableColumns as $column) {
        if (isset($data[$column])) {
            $columns[] = $column;
            $values[] = htmlspecialchars($data[$column], ENT_QUOTES, 'UTF-8');
        } else {
            return ['success' => false, 'error' => "Missing data for column: $column"];
        }
    }
    return ['success' => true, 'columns' => $columns, 'values' => $values, 'data' => $data];
}
