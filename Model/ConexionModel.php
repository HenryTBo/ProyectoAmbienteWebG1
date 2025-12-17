<?php
// Model/ConexionModel.php
// Conexión MySQL (XAMPP / MySQL 8.0)

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', ''); // ✅ root SIN contraseña
if (!defined('DB_NAME')) define('DB_NAME', 'proyectoambienteweb');

if (!function_exists('OpenConnection')) {
    function OpenConnection(): mysqli
    {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3306);

        if ($conn->connect_error) {
            throw new Exception("Error de conexión MySQL: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    }
}

if (!function_exists('CloseConnection')) {
    function CloseConnection($context): void
    {
        if ($context instanceof mysqli) {
            $context->close();
        }
    }
}

/* ==========================
   Aliases de compatibilidad
   ========================== */

if (!function_exists('getConnection')) {
    function getConnection(): mysqli
    {
        return OpenConnection();
    }
}

if (!function_exists('closeConnection')) {
    function closeConnection($context): void
    {
        CloseConnection($context);
    }
}

if (!function_exists('SaveError')) {
    function SaveError($error): void
    {
        try {
            $context = OpenConnection();
            $msg = ($error instanceof Throwable) ? $error->getMessage() : (string)$error;
            $mensaje = mysqli_real_escape_string($context, $msg);

            // Solo si existe el SP
            $context->query("CALL RegistrarError('$mensaje')");
            CloseConnection($context);
        } catch (Throwable $t) {
            // No romper flujo por logging
        }
    }
}
