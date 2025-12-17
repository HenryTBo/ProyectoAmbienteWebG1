<?php
// Model/ConexionModel.php
// Conexión MySQL (XAMPP) + compatibilidad OpenConnection/getConnection

// Ajustá si tu configuración es distinta
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'dannyJP_2021'); // si tu root no tiene clave, pon ''
if (!defined('DB_NAME')) define('DB_NAME', 'proyectoambienteweb');

if (!function_exists('OpenConnection')) {
    function OpenConnection(): mysqli
    {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

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
        } elseif (!empty($context)) {
            @mysqli_close($context);
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

            // Solo intenta registrar si existe el SP (si no, no rompe)
            $context->query("CALL RegistrarError('$mensaje')");
            CloseConnection($context);
        } catch (Throwable $t) {
            // No romper flujo por logging
        }
    }
}
