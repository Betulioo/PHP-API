<?php
##Creamos una funcion que recibe como parametro $filePath que sera la ruta al archivo .env que contiene las variables de entorno.
function loadEnvironmentVariables($filePath)
{
    if (!file_exists($filePath)) { ##si (if) el archivo .env no existe, ejecuta el siguiente codigo
        throw new Exception("Enviorenment file not found:" . $filePath); # lanza (throw) una nueva excepcion que contiene el mensaje de error.

    }
    #leemos el archivo .env y lo guardamos en una variable $lines
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); #Lee el archivo '.env' linea por linea eliminando los saltos de linea.


    ##Como $lines es un array, podemos iterar sobre el linea por linea para obtener el valor de cada variable de entorno.
    foreach ($lines as $line) { ##para cada (foreach) linea $line en $lines (el array de lineas), guarda cada linea en la variable $line
        if (strpos(trim($line), '#') === 0) { ##si (if) la linea empieza por '#' (comentario), ejecuta el siguiente codigo
            continue; ##continua (continue) la ejecucion del foreach
        }



        ##Creamos un array a partir de cada linea de texto que contiene las variables de entorno. Y para esto utilizamos la funcion list($name,$value) que nos permite separar la linea de texto en dos partes y guardar esas dos partes en dos variables separadas.
        list($name, $value) = explode('=', $line, 2); #Como la linea de texto que recibimos es la variable de entorno (DB_USER=root) viene separada por un signo de igual "=" usamos la funcion explode() para separar la linea de texto en un array de dos elementos ([DB_USER, root]). Y con list($name, $value) guardamos en $name el primer elemento del array y en $value el segundo elemento del array.
        $name = trim($name); #Luego con trim($name) lo que hacemos es quitar los espacios en blanco al rededor del nombre
        $value = trim($value); #Luego con trim($name) lo que hacemos es quitar los espacios en blanco al rededor del valor

        ##Ahora que tenemos el nombre y el valor de la variable de entorno, podemos usar la funcion putenv() para establecer la variable de entorno en el sistema.
        ##Pero primero verificamos si la variable de entorno ya existe en el sistema. Si la variable de entorno ya existe, no la establecemos de nuevo.
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) { ##si (if) la variable de entorno no existe en el sistema, ejecuta el siguiente codigo
            ##Establecemos la variable de entorno en el sistema. con putenv() guardamos la variable de entorno en el sistema.
            putenv(sprintf("%s=%s", $name, $value)); #sprintf() es una funcion que crea una cadena de texto en base a un formato "%s=%s", donde %s es el nombre de la variable de entorno y %s es el valor de la variable de entorno que seran $name y $value.

            $_ENV[$name] = $value; #Guardamos la variable de entorno en el sistema.
            $_SERVER[$name] = $value; #Guardamos la variable de entorno en el sistema.
        }
    }
}

### Ejecutamos la funcion loadEnvironmentVariables() con el parametro __DIR__ . '/.env' que es la ruta al archivo .env que contiene las variables de entorno. que vendria siendo $filePath.

loadEnvironmentVariables(__DIR__ . '/.env');
### Definimos una constante global usando define() que toma como argumentos el nombre de la constante y el valor de la constante. Para obtener el valor de la constante, utilizaremos la variable de entorno correspondiente. que cogeremos con la funcion getenv().
define('DB_SERVER', getenv('DB_SERVER'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
