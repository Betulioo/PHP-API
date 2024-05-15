<?php
require_once "../config.php";

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    echo "<br>";
} else {
    echo "Connected successfully";
    echo "<br>";
}


###Declaramos una funcion que va a importar los datos de los archivos CSV a la base de datos, y va a recibir como parametro el nombre del archivo, el nombre de la tabla y las columnas.
function importCsvData($fileName, $tableName, $columns)
{
    global $conn; ##Declara que dentro de esta función queremos usar la variable $conn definida en el ámbito global. Esto permite que la función importCsvData utilice la conexión a la base de datos establecida fuera de la función.

    ##Abrimos el archivo CSV

    ### Comprobamos que el archivo CSV exista y se pueda abrir con fopen(), si no existe o no se puede abrir, se muestra un mensaje de error.
    if (($handle = fopen($fileName, "r")) !== FALSE) { #Si existe y se puede abrir, ejecuta el siguiente bloque de código.
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { #fgetcsv() recoge los datos dentro del archivo abierto $handle y los separa por comas (,) y los devuelve en un array. Si no hay datos, devuelve FALSE. si hay datos, ejecuta el siguiente bloque de código.

            ##Los datos llegan como un array de strings, que por defecto son separados por comas. Con implode() volvemos a convertir el array en una cadena de texto separada por comas.
            $values = implode("','", array_map("escapeData", $data));

            ##Ahora $value es un string con los datos separados por comas. Guardamos la consulta SQL para insertar los datos en la base de datos en la variable $sql.
            $sql = "INSERT INTO $tableName ($columns) VALUES ('$values');";
            echo "<br>" . $sql . "<br>";

            ##Ejecutamos la consulta SQL en la base de datos con la conexion $conn->query($sql).
            if (!$conn->query($sql)) {
                echo "Error importing data into $tableName: " . $conn->error . "<br>";
            }
        }
        fclose($handle); ##Cerramos el archivo CSV
        echo "Imported data into $tableName successfully.<br>";
    } else {
        echo "Failed to open $fileName.<br>";
    }
}

###Creamos la funcion escapeData() que sirve para preparar los datos antes de ser insertados en la base de datos.
function escapeData($data)
{
    global $conn;
    return $conn->real_escape_string(trim($data)); ### Escapa cualquier carácter especial en la cadena proporcionada para que pueda ser utilizada de forma segura en una consulta SQL. Esto incluye caracteres como comillas simples ('), comillas dobles ("), barras invertidas (\), entre otros.
}

###Creamos un array con los nombres de los archivos CSV y las tablas a las que corresponden.
$csvFiles = [
    ["fileName" => "./CSV/categories.csv", "tableName" => "categories", "columns" => "id, category_name"],
    ["fileName" => "./CSV/products.csv", "tableName" => "products", "columns" => "id, product_name, product_desc, product_price, category_id"],
    ["fileName" => "./CSV/invoices.csv", "tableName" => "invoices", "columns" => "id, customer_nif, total_amount"],
    ["fileName" => "./CSV/product_invoice.csv", "tableName" => "product_invoice", "columns" => "id, product_id, invoice_id, quantity"]
];

###Recorremos el array $csvFiles y llamamos a la funcion importCsvData() para importar los datos de los archivos CSV a la base de datos.
foreach ($csvFiles as $file) {
    importCsvData($file["fileName"], $file["tableName"], $file["columns"]);
}

###Cerramos la conexión a la base de datos.
$conn->close();
echo "Connection closed successfully";
echo "<br>";
