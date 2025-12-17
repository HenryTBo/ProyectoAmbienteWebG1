<?php
// Controller/InicioController.php
// Autenticación / registro / recuperación / cierre de sesión

include_once __DIR__ . '/MiscelaneoController.php';
include_once __DIR__ . '/../Model/InicioModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Unifica la sesión del usuario.
 * - Mantiene llaves legacy (ConsecutivoUsuario, Nombre, ConsecutivoPerfil, NombrePerfil)
 * - Crea la llave moderna $_SESSION['User'] (la usan Carrito / Pedidos / Admin)
 */
function SetUserSessionFromRow(array $row): void
{
    $_SESSION["ConsecutivoUsuario"] = $row["ConsecutivoUsuario"] ?? null;
    $_SESSION["Nombre"]             = $row["Nombre"] ?? null;
    $_SESSION["ConsecutivoPerfil"]  = $row["ConsecutivoPerfil"] ?? null;
    $_SESSION["NombrePerfil"]       = $row["NombrePerfil"] ?? null;

    $_SESSION["User"] = [
        "ConsecutivoUsuario" => isset($row["ConsecutivoUsuario"]) ? (int)$row["ConsecutivoUsuario"] : 0,
        "Nombre"             => (string)($row["Nombre"] ?? ''),
        "ConsecutivoPerfil"  => isset($row["ConsecutivoPerfil"]) ? (int)$row["ConsecutivoPerfil"] : 0,
        "NombrePerfil"       => (string)($row["NombrePerfil"] ?? ''),
        "CorreoElectronico"  => (string)($row["CorreoElectronico"] ?? '')
    ];
}

/* ============================================================
   CREAR CUENTA
============================================================ */
if (isset($_POST["btnCrearCuenta"])) {
    $identificacion    = $_POST["Identificacion"] ?? '';
    $nombre            = $_POST["Nombre"] ?? '';
    $correoElectronico = $_POST["CorreoElectronico"] ?? '';
    $contrasenna       = $_POST["Contrasenna"] ?? '';

    $resultado = CrearCuentaModel($identificacion, $nombre, $correoElectronico, $contrasenna);

    if ($resultado) {
        header("Location: ../../View/Inicio/InicioSesion.php");
        exit;
    }

    $_POST["Mensaje"] = "No se ha podido crear la cuenta solicitada";
}

/* ============================================================
   INICIAR SESIÓN
============================================================ */
if (isset($_POST["btnIniciarSesion"])) {
    $correoElectronico = $_POST["CorreoElectronico"] ?? '';
    $contrasenna       = $_POST["Contrasenna"] ?? '';

    $resultado = ValidarCuentaModel($correoElectronico, $contrasenna);

    if ($resultado) {
        // Evita fijación de sesión
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }

        SetUserSessionFromRow($resultado);

        // Admin directo al panel
        $perfil = (int)($_SESSION["User"]["ConsecutivoPerfil"] ?? 0);
        if ($perfil === 1) {
            header("Location: ../../View/Inicio/PrincipalAdmin.php");
        } else {
            header("Location: ../../View/Inicio/Principal.php");
        }
        exit;
    }

    $_POST["Mensaje"] = "No se ha podido validar la cuenta ingresada";
}

/* ============================================================
   RECUPERAR ACCESO
============================================================ */
if (isset($_POST["btnRecuperarAcceso"])) {
    $correoElectronico = $_POST["CorreoElectronico"] ?? '';

    $resultado = ValidarCorreoModel($correoElectronico);

    if ($resultado) {
        $ContrasennaGenerada = GenerarContrasenna();

        // CORRECCIÓN: esta función se agrega en InicioModel.php
        $resultadoActualizar = ActualizarContrasennaModel($resultado["ConsecutivoUsuario"], $ContrasennaGenerada);

        if ($resultadoActualizar) {
            $mensaje = "<html><body>
                Estimado(a) " . ($resultado["Nombre"] ?? '') . "<br><br>
                Se ha generado la siguiente contraseña de acceso: <b>" . $ContrasennaGenerada . "</b><br>
                Procure realizar el cambio de su contraseña una vez que ingrese al sistema.<br><br>
                Muchas gracias.
                </body></html>";

            EnviarCorreo('Recuperar Acceso', $mensaje, $resultado["CorreoElectronico"]);

            // CORRECCIÓN: el archivo correcto es InicioSesion.php
            header("Location: ../../View/Inicio/InicioSesion.php");
            exit;
        }
    }

    $_POST["Mensaje"] = "No se ha podido recuperar el acceso";
}

/* ============================================================
   SALIR
============================================================ */
if (isset($_POST["btnSalir"])) {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    header("Location: ../../View/Inicio/InicioSesion.php");
    exit;
}
?>
