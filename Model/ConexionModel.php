<?php

    function OpenConnection()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = "127.0.0.1";
        $user = "root";
        $pass = "";                       
        $db   = "ProyectoAmbienteWeb";    
        $port = 3307;                        //cambiar si lo tienen en distinto puerto :)

        $conn = mysqli_connect($host, $user, $pass, $db, $port);

        if (!$conn) {
            throw new mysqli_sql_exception("Error de conexión: " . mysqli_connect_error());
        }

        return $conn;
    }

    function CloseConnection($context)
    {
        if ($context) {
            mysqli_close($context);
        }
    }

    function SaveError($error)
    {
        $context = OpenConnection();
        $mensaje = mysqli_real_escape_string($context, $error->getMessage());
        $sentencia = "CALL RegistrarError('$mensaje')";
        $context->query($sentencia);
        CloseConnection($context);
    }

?>
