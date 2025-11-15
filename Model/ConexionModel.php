<?php

    function OpenConnection()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        return mysqli_connect("127.0.0.1:3307", "root", "", "proyectoAmbienteWeb");
    }

    function CloseConnection($context)
    {
        mysqli_close($context);
    }

    function SaveError($error)
    {
        $context = OpenConnection();

        $mensaje = mysqli_real_escape_string($context, $error -> getMessage());

        $sentencia = "CALL RegistrarError('$mensaje')";
        $context -> query($sentencia);

        CloseConnection($context);
    }

?>