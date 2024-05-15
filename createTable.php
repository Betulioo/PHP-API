<?php
require_once "config.php";
### Declaramos nuestra base de datos, lo que hacemos es guardar en una variable las credenciales de nuestra base de datos para luego usarlas en la conexión, ya que para conectarnos a la base de datos el workbeanch o el dbeaver nos pide estas credenciales.

$servername = "127.0.0.1:3306";       #### Guardamos en una variable llamada $servername la direccion del servidor
$dbname = "betodb2";              #### Guardamos en una variable llamada $dbname el Nombre de la base de datos 
$user = "root";                  #### Guardamos en una variable llamada $user el Nombre del usuario
$password = "admin";             #### Guardamos en una variable lamada $password la Contraseña

### Ahora hacemos la conexion con la base de datos, usando mysqli() que nos permite crear una nueva instancia de la clase mysqli.
### mysqli() es la forma que tenemos en php de conectar a una base de datos de MySQL.
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);   ### Hacemos la conexion con la base de datos y la guardamos en la variable $conn que va a recibir como parametro las  variables de entorno DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME tienen que ir en ese orden
### ahora con la instancia de clase llamada $conn podemos hacer consultas a la base de datos


### hacemos una verificacion de la conexion a la base de datos
### $conn->connect_error esta propiedad contiene el mensaje de error si la conexion falla
if ($conn->connect_error) {  ### si (if) la conexion falla eso quiere decir que la variable $conn->connect_error contiene el mensaje de error que en caso contrario estaria vacia, si (if) tiene el mensaje de error ejecuta el siguiente codigo. 
    die("Connection failed: " . $conn->connect_error); ### con die() le decimos que muestre el error y que se detenga.
} else { ### De lo contrario (else) la variable $conn->connect_error no contiene el mensaje de error por lo tanto esta vacia, ejecuta el siguiente codigo.
    echo "Connected successfully";
}

### Guardamos en una variable llamada $sql que contiene una cadena con la consulta SQL para crear una tabla llamada 'productos'.
$sql = "CREATE TABLE productos ( #Iniciamos la creacion de la tabla llamada 'productos'
        id INT AUTO_INCREMENT PRIMARY KEY, #Define una columna llamada 'id' de tipo entero, que se incrementa automaticamente con cada nuevo registro y la establece como clave primaria
        `name` VARCHAR(255) NOT NULL, #Define una columna llamada 'name' de tipo cadena de texto, que no puede ser null
       `desc` TEXT NOT NULL, #Define una columna llamada 'desc' de tipo texto, que no puede ser null
        price DECIMAL(10, 2) NOT NULL, #Define una columna llamada 'price' de tipo decimal, con 10 digitos y 2 decimales, que no puede ser null
        unitCount INT NOT NULL, #Define una columna 'unitCount' de tipo entero que no puede ser null
        isAvailable BOOLEAN NOT NULL, #Define una columna 'isAvailable' de tipo booleano que no puede ser null
        inStock INT NOT NULL #Define una columna 'inStock' de tipo entero que no puede ser null
    );";

### Ejecutaremos la consulta usando $conn que es la conexion a la base de datos y con el metodo query() enviaremos la variable $sql que contiene la cadena con la consulta SQL para crear una tabla llamada 'productos'.
### Manejamos la respuesta de la consulta usando un if y else
if ($conn->query($sql) === TRUE) {  # si (if) la consulta se ejecuta con exito en la base de datos, es decir me regresa TRUE, ejecuta el siguiente codigo.
    echo "Table 'productos' created successfully";
} else { # De lo contrario (else) la consulta no se ejecuta con exito en la base de datos, es decir me regresa FALSE, ejecuta el siguiente codigo.
    echo "Error creating table: " . $conn->error;
}

$conn->close(); ### Cerramos la conexion a la base de datos. Esto es una buena practica ya que evita la sobrecarga de recursos.
