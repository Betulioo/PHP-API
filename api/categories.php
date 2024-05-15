<?php
require_once "../config.php";
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    echo "<br>";
}
##Definimos el nombre de la tabla que se va a manejar. Esto se hace para facilitar el uso del nombre de la tabla en las consultas SQL, haciendo que el codigo sea mas legible y facil de mantener.
$tableName = "categories";
##Definimos las columnas de la tabla que se va a manejar en las operaciones CRUD.Tener las columnas en una variable separada permite iterar sobre ellas facilmente al construir las consultas SQL.
$tableColumns = ["category_name"];

##Obtiene el metodo HTTP de la solicitud(GET,POST,PUT,DELETE).Esto es crucial para saber la operacion que se va a realizar.
$method = $_SERVER['REQUEST_METHOD'];
##Inicializamos un array vacio para almacenar la respuesta que se devolvera al cliente.Esto asegura que siempre haya una respuesta estructurada que se pueda enviar de vuelta
$response = [];
##Utilizamos el metodo switch para manejar la peticion HTTP.
switch ($method) {
    case 'GET': ## En caso de que el metodo HTTP sea GET ejecutamos el siguiente codigo.

        ##Vamos a tener dos opciones dentro del caso 'GET' uno es si viene con id y el otro si no, si viene con id deberia regresarnos solamente un elemento. De lo contrario deberia regresarnos todos.
        ##Primero obtenemos una sola categoria
        if (isset($_GET['id'])) { ##si (if) viene con id.
            $id = $_GET['id']; ##guardamos en una variable el id.
            $sql = "SELECT * FROM $tableName WHERE id = $id"; ##guardamos en una variable la consulta SQL. Agregandole el id que recogimos anteriormente para obtener una sola categoria.
            $result = $conn->query($sql); ##ejecutamos la consulta.
            $row = $result->fetch_assoc(); ##Obtiene el resultado como un array asociativo.
            $response = ["DATA" => $row, "METHOD" => "GET", "SUCCES" => true]; ##Prepara la respuesta con los datos obtenidos y la operacion que se realizo.
        } else { ##si (if) no viene con id.

            ##Ahora Obtenemos todas las categorias

            $sql = "SELECT * FROM $tableName"; ##guardamos en una variable la consulta SQL para todas las categorias.
            $result = $conn->query($sql); ##ejecutamos la consulta.
            $rows = []; ##Inicializamos un array vacio para almacenar los datos de las categorias.

            ##Usamos un bucle while para recorrer el resultado de la consulta y almacenarlo en el array $rows.
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $response = ["DATA" => $rows, "METHOD" => "GET", "SUCCESS" => true]; ##Prepara la respuesta con los datos obtenidos y la operacion que se realizo.
        }
        echo (json_encode($response)); ##Enviamos la respuesta.

        break; ##salimos del switch.

    case 'PUT':
        ##Obtenemos el id
        $id = $_GET['id'];
        ##Obtenemos el body que viene de la peticion de postman o la aplicacion cliente.
        $data = json_decode(file_get_contents("php://input"), true); #Obtiene y decodifica los datos del cuerpo de la solicitud. Esto permite recibir datos en formato JSON.
        ##Ahora vamos a construir la consulta SQL con los valores obtenidos de la peticion para actualizar la categoria.

        $values = []; ##Inicializamos un array vacio para almacenar los valores.
        foreach ($tableColumns as $column) { ##Iteramos sobre el array $tableColumns que es la lista de columnas de la tabla.Que en este caso es ['category_name']. Entonces $column será ['category_name'].
            echo ($column);
            $valueExists = isset($data[$column]); #Verificamos si en el array $data (que es el body de la peticion) existe la clave $column (que es el nombre de la columna).
            if ($valueExists) { #Si $valueExist es true, ejecuta el siguiente bloque de código.
                $values[] = "$column = '$data[$column]'"; #Añade la columna y su valor al array $values.
            }
        }

        ##Ya con los valores construidos y guardados en $column y $value, construimos la consulta SQL para actualizar la categoria.
        $sql = "UPDATE $tableName SET " . implode(", ", $values) . " WHERE id = $id"; #Concatenamos los valores de $values separados por comas y los guardamos en $sql. Ahora $sql tendra la consulta SQL para actualizar la categoria.

        ##Ahora ejecuto el query y lo almaceno en una variable esto deberia guardarme true o false
        $queryResult = $conn->query($sql);
        if ($queryResult) { ##Si la consulta devuelve true, ejecuta el siguiente bloque de codigo.
            ##Verificamos si el numero de filas actualizadas es mayor a 0. Esto indica que la categoria se actualizo correctamente.
            if ($conn->affected_rows == 1) { ##Si el numero de filas actualizadas es 1, ejecuta el siguiente bloque de codigo. affected_rows si devuelve 1 quiere decir que se actualizo correctamente. Si devuelve 0 significa que no se actualizo.

                ##Construimos la consulta SQL para obtener la categoria actualizada.
                $updatedRecordQuery = "SELECT * FROM $tableName WHERE id = $id";

                ##ejecutamos la consulta.
                $updatedRecord = $conn->query($updatedRecordQuery);

                ##construimos un array asociativo con los datos de la categoria actualizada.
                $response = ["METHOD" => "PUT", "SUCCESS" => true, "DATA" => $updatedRecord->fetch_assoc()];
            } else {
                ##Si la consulta no devolvio true, ejecuta el siguiente bloque de codigo.
                $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => "$tableName not found"];
            }
        } else {
            // si el query no se ejecuta correctamente regreso un error.
            $response = ["METHOD" => "PUT", "SUCCESS" => false, "ERROR" => $conn->error];
        }
        echo json_encode($response);
        break;
    case 'POST':
        ##leemos y decodificamos los datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true); ##Obtiene y decodifica los datos del cuerpo de la peticion. Esto permite recibir datos en formato JSON. file_get_contents('php://input'): Lee el cuerpo de la solicitud HTTP. Esto es necesario para obtener los datos enviados en el cuerpo de una solicitud POST.
        # json_decode(..., true): Decodifica la cadena JSON recibida en un array asociativo de PHP. Esto convierte los datos JSON enviados en la solicitud en un formato que PHP puede manejar fácilmente.

        ##Ahora inicializamos los arrays para las columnas y valores

        // para almacenar columnas
        $columns = [];
        // para almacenar valores
        $values = [];
        ##Ahora iteramos sobre columnas definidas y recogemos los valores de la peticion
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
            // construyo mi respuesta
            $response = ["METHOD" => "POST", "SUCCESS" => true, "DATA" => $newRecord->fetch_assoc()];
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

            // Primero eliminamos todos los registros en product_invoice relacionados con los productos de esta categoría
            $deleteProductInvoicesSql = "DELETE pi FROM product_invoice pi JOIN products p ON pi.product_id = p.id WHERE p.category_id = $id";
            if ($conn->query($deleteProductInvoicesSql) === TRUE) {
                // Luego eliminamos todos los productos relacionados con esta categoría
                $deleteProductsSql = "DELETE FROM products WHERE category_id = $id";
                if ($conn->query($deleteProductsSql) === TRUE) {
                    // Ahora eliminamos la categoría
                    $sql = "DELETE FROM $tableName WHERE id = $id;";
                    $result = $conn->query($sql);

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
                    // si hay un error en la eliminación de los productos, regreso un error. 
                    $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => $conn->error];
                }
            } else {
                // si hay un error en la eliminación de los registros de product_invoice, regreso un error. 
                $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => $conn->error];
            }
        } else {
            // si el parametro id no existe
            // regreso un error 
            $response = ["METHOD" => "DELETE", "SUCCESS" => false, "ERROR" => "THE PARAMETER ID IS REQUIRED"];
        }
        echo json_encode($response);
        break;
    default:
        $result = ["METHOD" => $method, "SUCCESS" => false, "ERROR" => "Method not supported"];
        echo (json_encode($result));
        break;
}


##Cerramos la conexion con la base de datos
$conn->close();
