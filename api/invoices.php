<?php
require_once "../config.php";

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableName = "invoices";
$tableColumns = ["customer_nif", "total_amount"];

//  IDENTIFY METHOD
$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            // GET SINGLE CATEGORY
            $sql = "SELECT * FROM $tableName WHERE id = $id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $response = ["DATA" => $row, "METHOD" => "GET", "SUCCESS" => true];
        } else {
            // GET ALL CATEGORIES
            $sql = "SELECT * FROM $tableName";
            $result = $conn->query($sql);
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $response = ["DATA" => $rows, "METHOD" => "GET", "SUCCESS" => true];
        }
        echo (json_encode($response));
        break;
    case 'PUT':
        // obtengo el id 
        $id = $_GET['id'];
        // obtengo el valor del body del request
        $data = json_decode(file_get_contents('php://input'), true);
        // obtengo el valor de cada una de mis columnas
        $values = [];
        foreach ($tableColumns as $column) {
            $valueExists = isset($data[$column]);
            if ($valueExists) {
                $values[] = "$column = '$data[$column]'";
            }
        }
        // construyo mi query SQL en base a las columnas con valor.
        $sql = "UPDATE $tableName SET " . implode(", ", $values) . " WHERE id = $id";
        // ejecturo el query y lo almaceno en una variable
        $queryResult = $conn->query($sql);
        // si el query se ejecuta correctamente
        if ($queryResult ===  TRUE) {
            // pregunto si el numero de filas afectadas/actualizadas es mayor 0
            if ($conn->affected_rows == 1) {
                // si es mayor a cero
                // construyo un query para obtener la fila actualizada
                $updatedRecordQuery = "SELECT * FROM $tableName WHERE id = $id";
                // ejecturo el query y lo almaceno en una varaible
                $updatedRecord = $conn->query($updatedRecordQuery);
                // contruyo un array asociativo con la informacion.
                $response = ["METHOD" => "PUT", "SUCCESS" => true, "DATA" => $updatedRecord->fetch_assoc()];
            } else {
                // si no actualiza ninguna fila, es porque no encontro la categoria
                $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "$tableName not found"];
            }
        } else {
            // si el query no se ejecuta correctamente regreso un error.
            $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => $conn->error];
        }
        echo json_encode($response);
        break;
    case 'POST':
        // almaceno el valor del body de mi request en una variable
        $data = json_decode(file_get_contents('php://input'), true);
        // para almacenar columnas
        $columns = [];
        // para almacenar valores
        $values = [];
        foreach ($tableColumns as $column) {
            $valueExists = isset($data[$column]);
            if ($valueExists) {
                $columns[] = $column;
                $values[] = "'$data[$column]'";
            }
        }
        // construyo mi query en base a las columnas y valores obtenidos
        $sql = "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
        // ejecuto el query y almaceno el resultado en una variable
        $createResult = $conn->query($sql);
        if ($createResult === TRUE) {
            // se creo exitosamente
            // obtengo el ID del nuevo registro (recien creado)
            $newRecordId = $conn->insert_id;
            // construyo un query para obtener el nuevo registro y regresarlo en mi respuesta. 
            $getRecordQuery = "SELECT * FROM $tableName where id = $newRecordId";
            // ejectuo el query y almaceno el resultado en una variable.
            $newRecord = $conn->query($getRecordQuery);
            $nuevaFacturaEncabezado = $newRecord->fetch_assoc();
            // CODIGO EXTRA
            $invoiceProducts = $data['products'];
            //coinstruir un query por cada producto haciendo un loop foreach similar al de la linea 83, con los productos.
            // print_r($invoiceProducts);
            foreach ($invoiceProducts as $product) {
                $productId = $product['product_id'];
                $productQuantity = $product['quantity'];
                // insertar esos productos en la tabla invoice_products
                $insertProductQuery = "INSERT INTO product_invoice (product_id,invoice_id, quantity) VALUES ($productId,$newRecordId, $productQuantity)";
                $conn->query($insertProductQuery);
            }

            $getProductsQuery = "SELECT product_id, quantity FROM product_invoice WHERE invoice_id = $newRecordId";
            $productsResult = $conn->query($getProductsQuery);
            $products = [];
            while ($product = $productsResult->fetch_assoc()) {
                // Obtener los detalles de cada producto y agregarlos al array de productos
                $productId = $product['product_id'];
                $quantity = $product['quantity'];
                // Consulta para obtener los detalles del producto
                $getProductDetailsQuery = "SELECT * FROM products WHERE id = $productId";
                $productDetailsResult = $conn->query($getProductDetailsQuery);
                $productDetails = $productDetailsResult->fetch_assoc();
                // Agregar los detalles del producto al array de productos
                $productDetails['quantity'] = $quantity;
                $products[] = $productDetails;
            }
            // agregar el resultado de cada producto a la respuesta
            $nuevaFacturaEncabezado['products'] = [$products];
            // TERMINA CODIGO EXTRA



            // construyo mi respuesta
            $response = ["METHOD" => "POST", "SUCCESS" => true, "DATA" => $nuevaFacturaEncabezado];
        } else {
            // no se creo existosamente
            $response = ["METHOD" => "POST", "SUCCESS" => false, "ERROR" => $conn->error];
        }
        echo (json_encode($response));
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            // si el parametro id existe
            // lo almaceno en una variable
            $id = $_GET['id'];
            // echo($id);
            // contruyo el SQL para eliminar la categoria bajo el id obtenido a traves de los parametros
            $sql = "DELETE FROM $tableName where id = $id;";
            // ejecuto el query y almaceno la respuesta.
            $result = $conn->query($sql);
            // echo (json_encode($result));
            // si el resultado es TRUE
            if ($result === TRUE) {
                // verifico que el numero de filas afectadas sea 1
                if ($conn->affected_rows == 1) {
                    // si es 1, regreso un mensaje de exito confirmando la eliminacion del registro
                    $response = ["METHOD" => "DELETE", "SUCCESS" => true, "MESSAGE" => "$tableName deleted"];
                } else {
                    // si no es 1, regreso un mensaje de error diciendo que no se encontro la categoria
                    $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "$tableName not found"];
                }
            } else {
                // si hay un error en la ejecucion del query, regreso un error. 
                $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => $conn->error];
            }
        } else {
            // si el parametro id no existe
            // regreso un error 
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
