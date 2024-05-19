# Product Manager API

## Descripción

Product Manager API es una aplicación PHP que proporciona una interfaz de programación de aplicaciones (API) para gestionar usuarios, categorías, productos e invoices. La API utiliza JWT para autenticación y autorización.

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer
- Servidor web (Apache, Nginx, etc.)

## Instalación

1. Clonar el repositorio:

   ```sh
   git clone <url-del-repositorio>
   cd productManager
   ```

2. Instalar las dependencias:

   ```sh
   composer install
   ```

3. Configurar la base de datos:

   - Crear una base de datos MySQL y ejecutar el script SQL para crear las tablas necesarias.
   - Configurar las variables de entorno en el archivo `.env` en la raíz del proyecto.

4. Configurar el servidor web para apuntar al directorio del proyecto.

## Endpoints

### Autenticación

- **POST /auth/auth.php**
  - Descripción: Autenticar un usuario y obtener un token JWT.
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "example",
      "password": "password123"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "token": "jwt_token"
    }
    ```

### Usuarios

- **GET /users/users.php**

  - Descripción: Obtener una lista de todos los usuarios.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": [...],
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **GET /users/users.php?id={id}**

  - Descripción: Obtener un usuario por ID.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": {...},
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **POST /users/users.php**

  - Descripción: Crear un nuevo usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "newuser",
      "password": "newpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "POST",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **PUT /users/users.php?id={id}**

  - Descripción: Actualizar un usuario existente.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "updateduser",
      "password": "updatedpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "PUT",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **DELETE /users/users.php?id={id}**
  - Descripción: Eliminar un usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "DELETE",
      "SUCCESS": true,
      "MESSAGE": "users deleted"
    }
    ```

### Otros Endpoints

### categories

- **GET /api/categories.php**

  - Descripción: Obtener una lista de todos los usuarios.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": [...],
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **GET /api/categories.php?id={id}**

  - Descripción: Obtener un usuario por ID.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": {...},
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **POST /api/categories.php**

  - Descripción: Crear un nuevo usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "newuser",
      "password": "newpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "POST",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **PUT /api/categories.php?id={id}**

  - Descripción: Actualizar un usuario existente.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "updateduser",
      "password": "updatedpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "PUT",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **DELETE /api/categories.php?id={id}**
  - Descripción: Eliminar un usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "DELETE",
      "SUCCESS": true,
      "MESSAGE": "users deleted"
    }
    ```

### invoices

- **GET /api/invoices.php**

  - Descripción: Obtener una lista de todos los usuarios.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": [...],
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **GET /api/invoices.php?id={id}**

  - Descripción: Obtener un usuario por ID.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": {...},
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **POST /api/invoices.php**

  - Descripción: Crear un nuevo usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "newuser",
      "password": "newpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "POST",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **PUT /api/invoices.php?id={id}**

  - Descripción: Actualizar un usuario existente.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "updateduser",
      "password": "updatedpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "PUT",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **DELETE /api/invoices.php?id={id}**
  - Descripción: Eliminar un usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "DELETE",
      "SUCCESS": true,
      "MESSAGE": "users deleted"
    }
    ```

### products

- **GET /api/products.php**

  - Descripción: Obtener una lista de todos los usuarios.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": [...],
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **GET /api/products.php?id={id}**

  - Descripción: Obtener un usuario por ID.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "DATA": {...},
      "METHOD": "GET",
      "SUCCESS": true
    }
    ```

- **POST /api/products.php**

  - Descripción: Crear un nuevo usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "newuser",
      "password": "newpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "POST",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **PUT /api/products.php?id={id}**

  - Descripción: Actualizar un usuario existente.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Cuerpo de la solicitud:
    ```json
    {
      "username": "updateduser",
      "password": "updatedpassword"
    }
    ```
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "PUT",
      "SUCCESS": true,
      "DATA": {...}
    }
    ```

- **DELETE /api/products.php?id={id}**
  - Descripción: Eliminar un usuario.
  - Encabezados:
    - `Authorization: Bearer jwt_token`
  - Respuesta exitosa:
    ```json
    {
      "METHOD": "DELETE",
      "SUCCESS": true,
      "MESSAGE": "users deleted"
    }
    ```

## Seguridad

- Utiliza JWT para autenticar y autorizar las solicitudes.
- Las contraseñas se almacenan de manera segura utilizando `password_hash`.

## Licencia
