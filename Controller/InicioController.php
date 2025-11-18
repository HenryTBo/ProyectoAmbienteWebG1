<?php
    include_once __DIR__ . '/MiscelaneoController.php';        
    include_once __DIR__ . '/../Model/InicioModel.php';     
    
        //para qué es el "DIR" es una constante que lo que hace es devolverse a la ruta exacta en donde tengas el archivo actual :)


    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
       
    if (isset($_POST["btnCrearCuenta"])) {
        $identificacion    = $_POST["Identificacion"];
        $nombre            = $_POST["Nombre"];
        $correoElectronico = $_POST["CorreoElectronico"];
        $contrasenna       = $_POST["Contrasenna"];

        $resultado = CrearCuentaModel($identificacion, $nombre, $correoElectronico, $contrasenna);

        if ($resultado) {
            header("Location: ../../View/Inicio/InicioSesion.php");
            exit;
        }

        $_POST["Mensaje"] = "No se ha podido crear la cuenta solicitada";
    }

    if (isset($_POST["btnIniciarSesion"])) {
        $correoElectronico = $_POST["CorreoElectronico"];
        $contrasenna       = $_POST["Contrasenna"];

        $resultado = ValidarCuentaModel($correoElectronico, $contrasenna);

        if ($resultado) {
            $_SESSION["ConsecutivoUsuario"] = $resultado["ConsecutivoUsuario"];
            $_SESSION["Nombre"]             = $resultado["Nombre"];
            $_SESSION["ConsecutivoPerfil"]  = $resultado["ConsecutivoPerfil"];
            $_SESSION["NombrePerfil"]       = $resultado["NombrePerfil"];

            header("Location: ../../View/Inicio/Principal.php");
            exit;
        }

        $_POST["Mensaje"] = "No se ha podido validar la cuenta ingresada";
    }

    if (isset($_POST["btnRecuperarAcceso"])) {
        $correoElectronico = $_POST["CorreoElectronico"];
 
        $resultado = ValidarCorreoModel($correoElectronico);

        if ($resultado) {
            $ContrasennaGenerada = GenerarContrasenna();

            $resultadoActualizar = ActualizarContrasennaModel($resultado["ConsecutivoUsuario"], $ContrasennaGenerada);
            
            if ($resultadoActualizar) {
                $mensaje = "<html><body>
                Estimado(a) " . $resultado["Nombre"] . "<br><br>
                Se ha generado la siguiente contraseña de acceso: <b>" . $ContrasennaGenerada . "</b><br>
                Procure realizar el cambio de su contraseña una vez que ingrese al sistema.<br><br>
                Muchas gracias.
                </body></html>";

                EnviarCorreo('Recuperar Acceso', $mensaje, $resultado["CorreoElectronico"]);

                header("Location: ../../View/Inicio/IniciarSesion.php");
                exit;
            }
        }

        $_POST["Mensaje"] = "No se ha podido recuperar el acceso";
    }

    if (isset($_POST["btnSalir"])) {
        session_destroy();
        header("Location: ../../View/Inicio/InicioSesion.php");
        exit;
    }
?>
