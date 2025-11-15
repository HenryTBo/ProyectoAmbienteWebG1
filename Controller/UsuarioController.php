<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/Model/UsuarioModel.php';

    if(session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }

    function ConsultarUsuario()
    {
        $consecutivo = $_SESSION["ConsecutivoUsuario"];
        return ConsultarUsuarioModel($consecutivo);
    }

    
    if(isset($_POST["btnActualizarPerfil"]))
    {
        $consecutivo = $_SESSION["ConsecutivoUsuario"];
        $identificacion = $_POST["Identificacion"];
        $nombre = $_POST["Nombre"];
        $correoElectronico = $_POST["CorreoElectronico"];

        $resultado = ActualizarPerfilModel($consecutivo, $identificacion,$nombre,$correoElectronico);

        if($resultado)
        {
            $_SESSION["Nombre"] = $nombre;
            $_POST["Mensaje"] = "La información se actualizó correctamente";
        }
        else
        {
            $_POST["Mensaje"] = "La información no se actualizó correctamente";
        }        
    }

     if(isset($_POST["btnActualizarSeguridad"]))
    {
        $consecutivo = $_SESSION["ConsecutivoUsuario"];
        $contrasenna = $_POST["Contrasenna"];

        $resultado = ActualizarContrasenaModel($consecutivo, $contrasenna);

        if($resultado)
        {
            $_POST["Mensaje"] = "La información se actualizó correctamente";
        }
        else
        {
            $_POST["Mensaje"] = "La información no se actualizó correctamente";
        }        
    }   
?>