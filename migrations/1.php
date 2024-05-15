<?php
##Incluimos el archivo que contiene las credenciales de la base de datos.
#para poder utilizar DB_SERVER, DB_USER, DB_PASSWORD y DB_NAME
require_once "../config.php";
### Ahora hacemos la conexion con la base de datos, usando mysqli() que nos permite crear una nueva instancia de la clase mysqli.
### mysqli() es la forma que tenemos en php de conectar a una base de datos de MySQL.
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);   ### Hacemos la conexion con la base de datos y la guardamos en la variable $conn que va a recibir como parametro las  variables de entorno DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME tienen que ir en ese orden
### ahora con la instancia de clase llamada $conn podemos hacer consultas a la base de datos

### hacemos una verificacion de la conexion a la base de datos
### $conn->connect_error esta propiedad contiene el mensaje de error si la conexion falla
if ($conn->connect_error) {  ### si (if) la conexion falla eso quiere decir que la variable $conn->connect_error contiene el mensaje de error que en caso contrario estaria vacia, si (if) tiene el mensaje de error ejecuta el siguiente codigo. 
    die("Connection failed: " . $conn->connect_error); ### con die() le decimos que muestre el error y que se detenga.
    echo "<br>";
} else { ### De lo contrario (else) la variable $conn->connect_error no contiene el mensaje de error por lo tanto esta vacia, ejecuta el siguiente codigo.
    echo "Connected successfully";
    echo "<br>";
}
### Guardamos en una variable llamada $createCategories la consulta SQL para crear una tabla llamada 'categories'.
$createCategories = "
CREATE TABLE IF NOT EXISTS categories ( 
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_name VARCHAR(128),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

### Guardamos en una variable llamada $createProducts la consulta SQL para crear una tabla llamada 'products'.
$createProducts = "
CREATE TABLE IF NOT EXISTS products ( 
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_name VARCHAR(128),
    product_desc VARCHAR(256), product_price DECIMAL(10,2), category_id INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);";
### Guardamos en una variable llamada $createInvoices la consulta SQL para crear una tabla llamada 'invoices'.
$createInvoices = "
    CREATE TABLE IF NOT EXISTS invoices (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_nif VARCHAR(8), total_amount DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
### Guardamos en una variable llamada $createProductInvoice la consulta SQL para crear una tabla llamada 'product_invoice'.
$createProductInvoice = "
    CREATE TABLE IF NOT EXISTS product_invoice (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT(6) UNSIGNED, 
        invoice_id INT(6) UNSIGNED,
        quantity INT(6) UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (invoice_id) REFERENCES invoices(id)
    );
";

### Ejecutaremos la consulta usando $conn que es la conexion a la base de datos y con el metodo query() enviaremos la variable $createCategories que contiene la cadena con la consulta SQL para crear una tabla llamada 'categories'.
### Manejamos la respuesta de la consulta usando un if y else

if ($conn->query($createCategories) === TRUE) {  # si (if) la consulta se ejecuta con exito en la base de datos, es decir me regresa TRUE, ejecuta el siguiente codigo.
    echo "Created Table: Categories";
    echo "<br>";
} else { # De lo contrario (else) la consulta no se ejecuta con exito en la base de datos, es decir me regresa FALSE, ejecuta el siguiente codigo.
    echo "ERROR: cannot create table: Categories";
    echo "<br>";
    echo $conn->error;
    echo "<br>";
}

// Hacemos lo mismo con el resto de las tablas
if ($conn->query($createProducts) == TRUE) {
    echo "Created Table: Products";
    echo "<br>";
} else {
    echo "ERROR: cannot create table: Products";
    echo "<br>";
    echo $conn->error;
    echo "<br>";
}

// create invocies table
if ($conn->query($createInvoices) == TRUE) {
    echo "Created Table: invoices";
    echo "<br>";
} else {
    echo "ERROR: cannot create table: invoices";
    echo "<br>";
    echo $conn->error;
    echo "<br>";
}

// create product_invoice table
if ($conn->query($createProductInvoice) == TRUE) {
    echo "Created Table: product_invoice";
    echo "<br>";
} else {
    echo "ERROR: cannot create table: product_invoice";
    echo "<br>";
    echo $conn->error;
    echo "<br>";
}

// Close connection
$conn->close();
