<?php
require_once "../config.php";
try {
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $tableName = "invoices";
    $tableColumns = ["customer_nif", "total_amount"];

    $method = $_SERVER['REQUEST_METHOD'];
    $response = [];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                if (filter_var($id, FILTER_VALIDATE_INT) != false) {

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
            echo (json_encode($response));
            break;
        case 'PUT':

            if (isset($_GET['id'])) {
                $id = $_GET['id'];

                // Validar y sanitizar el id
                if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
                    $data = json_decode(file_get_contents('php://input'), true);

                    // Validar y sanitizar los datos recibidos
                    $validData = true;
                    foreach ($tableColumns as $column) {
                        if (isset($data[$column])) {
                            // Ejemplo de sanitización usando htmlspecialchars()
                            $data[$column] = htmlspecialchars($data[$column], ENT_QUOTES, 'UTF-8');
                        } else {
                            $validData = false;
                            $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "Missing data for column: $column"];
                            break;
                        }
                    }

                    if ($validData) {
                        $values = [];
                        foreach ($tableColumns as $column) {
                            $values[] = "$column = ?";
                        }
                        $sql = "UPDATE $tableName SET " . implode(", ", $values) . " WHERE id = ?";
                        $stmt = $conn->prepare($sql);

                        // Preparar los parámetros dinámicamente
                        $types = str_repeat('s', count($tableColumns)) . 'i';
                        $params = array_merge(array_values($data), [$id]);
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();

                        // Manejar productos relacionados si existen
                        if (isset($data['products']) && is_array($data['products'])) {
                            foreach ($data['products'] as $product) {
                                if (
                                    isset($product['product_id'], $product['quantity']) &&
                                    filter_var($product['product_id'], FILTER_VALIDATE_INT) !== false &&
                                    filter_var($product['quantity'], FILTER_VALIDATE_INT) !== false
                                ) {

                                    $productId = $product['product_id'];
                                    $productQuantity = $product['quantity'];
                                    $updateProductQuery = "UPDATE product_invoice SET quantity = ? WHERE product_id = ? AND invoice_id = ?";
                                    $stmt = $conn->prepare($updateProductQuery);
                                    $stmt->bind_param('iii', $productQuantity, $productId, $id);
                                    $stmt->execute();
                                } else {
                                    $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "Invalid product data"];
                                    echo (json_encode($response));
                                    exit;
                                }
                            }
                        }
                        $response = ["METHOD" => "PUT", "SUCCESS" => true];
                        $stmt->close();
                    }
                } else {
                    $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "Invalid ID parameter"];
                }
            } else {
                $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "ID parameter is required"];
            }
            echo (json_encode($response));
            break;


        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $columns = [];
            $values = [];
            foreach ($tableColumns as $column) {
                if (isset($data[$column])) {
                    $columns[] = $column;
                    $values[] = htmlspecialchars($data[$column], ENT_QUOTES, 'UTF-8');
                }
            }
            $sql = "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES (" . str_repeat('?,', count($columns) - 1) . "?);";
            $stmt = $conn->prepare($sql);
            $types = str_repeat('s', count($columns));
            $stmt->bind_param($types, ...$values);
            $createResult = $stmt->execute();
            if ($createResult) {
                $newRecordId = $stmt->insert_id;
                $getRecordQuery = "SELECT * FROM $tableName where id = ?";
                $stmt = $conn->prepare($getRecordQuery);
                $stmt->bind_param('i', $newRecordId);
                $stmt->execute();
                $result = $stmt->get_result();
                $nuevaFacturaEncabezado = $result->fetch_assoc();

                $invoiceProducts = $data['products'];
                $nuevaFacturaEncabezado['products'] = [];
                if ($invoiceProducts) {
                    foreach ($invoiceProducts as $product) {
                        $productId = $product['product_id'];
                        $productQuantity = $product['quantity'];
                        $insertProductQuery = "INSERT INTO product_invoice (product_id,invoice_id, quantity) VALUES (?,?,?)";
                        $stmt = $conn->prepare($insertProductQuery);
                        $stmt->bind_param('iii', $productId, $newRecordId, $productQuantity);
                        $stmt->execute();
                    }
                    $getProductInvoiceQuery = "SELECT * FROM product_invoice WHERE invoice_id = ?";
                    $stmt = $conn->prepare($getProductInvoiceQuery);
                    $stmt->bind_param('i', $newRecordId);
                    $stmt->execute();
                    $productInvoiceResult = $stmt->get_result();
                    while ($product = $productInvoiceResult->fetch_assoc()) {
                        $nuevaFacturaEncabezado['products'][] = $product;
                    }
                }



                $response = ["METHOD" => "POST", "SUCCESS" => true, "DATA" => $nuevaFacturaEncabezado];
            } else {
                $response = ["METHOD" => "POST", "SUCCESS" => false, "ERROR" => $conn->error];
            }
            echo (json_encode($response));
            break;
        case 'DELETE':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $sql = "DELETE FROM $tableName where id = $id;";
                $result = $conn->query($sql);
                if ($result === TRUE) {
                    if ($conn->affected_rows == 1) {
                        $response = ["METHOD" => "DELETE", "SUCCESS" => true, "MESSAGE" => "$tableName deleted"];
                    } else {
                        $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "$tableName not found"];
                    }
                } else {
                    $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => $conn->error];
                }
            } else {
                $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "THE PARAMETER ID IS REQURIED"];
            }
            echo (json_encode($response));
            break;
        default:
            $result = ["METHOD" => $method, "SUCCESS" => false, "ERROR" => "Method not supported"];
            echo (json_encode($result));
            break;
    }

    //  CRUD METHODS

    // Cerrar la conexión
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["SUCCESS" => false, "ERROR" => $e->getMessage()]);
}
