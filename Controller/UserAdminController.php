<?php
// Controller/UserAdminController.php
// Controlador para administración de cuentas: listado de usuarios y cambio de perfil.

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../Model/UserAdminModel.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permiso de administrador
function useradmin_check_admin() {
    if (!isset($_SESSION['ConsecutivoPerfil']) || $_SESSION['ConsecutivoPerfil'] != '1') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
        exit;
    }
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {

    switch ($action) {

        /* --------------------------------------------------------
           LISTAR USUARIOS
        ---------------------------------------------------------*/
        case 'list':
            useradmin_check_admin();
            $usuarios = getAllUsers();
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;

        /* --------------------------------------------------------
           CAMBIAR PERFIL DE USUARIO
        ---------------------------------------------------------*/
        case 'updateRole':
            useradmin_check_admin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Usar POST");

            $userId   = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $perfilId = isset($_POST['perfil']) ? intval($_POST['perfil']) : 0;
            if ($userId <= 0 || $perfilId <= 0) throw new Exception("Datos inválidos");

            // No permitir que un usuario se cambie a sí mismo de perfil (opcional, pero recomendado)
            if (isset($_SESSION['ConsecutivoUsuario']) && $_SESSION['ConsecutivoUsuario'] == $userId) {
                throw new Exception("No puedes modificar tu propio rol");
            }

            $ok = changeUserRole($userId, $perfilId);
            if (!$ok) throw new Exception("No se pudo actualizar el rol");

            echo json_encode(['success' => true]);
            break;

        /* --------------------------------------------------------
           ACCIÓN NO SOPORTADA
        ---------------------------------------------------------*/
        default:
            throw new Exception("Acción no soportada");
    }

} catch (Exception $e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) {}
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}