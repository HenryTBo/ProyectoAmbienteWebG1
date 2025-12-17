<?php
// Model/InicioModel.php
// Acceso a datos para login/registro/recuperación (solo SP)

include_once __DIR__ . '/ConexionModel.php';

function CrearCuentaModel($identificacion, $nombre, $correoElectronico, $contrasenna)
{
    try {
        $context = OpenConnection();

        $stmt = $context->prepare("CALL CrearCuenta(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $identificacion, $nombre, $correoElectronico, $contrasenna);
        $ok = $stmt->execute();
        $stmt->close();

        while ($context->more_results() && $context->next_result()) {}

        CloseConnection($context);
        return $ok;
    } catch (Exception $error) {
        SaveError($error);
        return false;
    }
}

function ValidarCuentaModel($correoElectronico, $contrasenna)
{
    try {
        $context = OpenConnection();

        $stmt = $context->prepare("CALL ValidarCuenta(?, ?)");
        $stmt->bind_param("ss", $correoElectronico, $contrasenna);
        $stmt->execute();

        $datos = null;
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $datos = $row;
            }
        }

        $stmt->close();
        while ($context->more_results() && $context->next_result()) {}

        CloseConnection($context);
        return $datos;
    } catch (Exception $error) {
        SaveError($error);
        return null;
    }
}

function ValidarCorreoModel($correoElectronico)
{
    try {
        $context = OpenConnection();

        // NOTA: este SP se agrega en el script SQL de corrección (ValidarCorreo)
        $stmt = $context->prepare("CALL ValidarCorreo(?)");
        $stmt->bind_param("s", $correoElectronico);
        $stmt->execute();

        $datos = null;
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $datos = $row;
            }
        }

        $stmt->close();
        while ($context->more_results() && $context->next_result()) {}

        CloseConnection($context);
        return $datos;
    } catch (Exception $error) {
        SaveError($error);
        return null;
    }
}

/**
 * CORRECCIÓN: esta función era llamada por InicioController.php pero NO existía.
 * Actualiza la contraseña generada en recuperación de acceso.
 */
function ActualizarContrasennaModel($consecutivo, $contrasenna)
{
    try {
        $context = OpenConnection();

        $stmt = $context->prepare("CALL ActualizarContrasenna(?, ?)");
        $cid = (int)$consecutivo;
        $stmt->bind_param("is", $cid, $contrasenna);
        $ok = $stmt->execute();
        $stmt->close();

        while ($context->more_results() && $context->next_result()) {}

        CloseConnection($context);
        return $ok;
    } catch (Exception $error) {
        SaveError($error);
        return false;
    }
}
?>
