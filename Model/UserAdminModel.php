<?php
// Model/UserAdminModel.php
// Funciones para administración de cuentas de usuarios (listado y cambio de perfil).

/*
    Esta capa de modelo encapsula las operaciones relacionadas con la
    administración de usuarios para el panel de control. Todas las
    operaciones utilizan procedimientos almacenados para evitar
    consultas directas a la base de datos.

    - getAllUsers(): devuelve el listado de todas las cuentas con su
      información básica y el nombre del perfil.
    - changeUserRole($id, $profile): actualiza el perfil (rol) de un
      usuario específico.

    Estos procedimientos son definidos en Respaldo.sql:
      * sp_Usuarios_Listar
      * sp_Usuario_CambiarPerfil
*/

include_once __DIR__ . '/ConexionModel.php';

/**
 * Registra un error para depuración.
 */
function useradmin_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    }
}

/**
 * Obtiene todas las cuentas de usuarios (sin filtrar por activo).
 * Cada elemento incluye el id (ConsecutivoUsuario), identificación,
 * nombre, correo electrónico, ConsecutivoPerfil y el nombre del perfil.
 *
 * @return array
 */
function getAllUsers() {
    $out = [];
    try {
        $conn = OpenConnection();
        $sql  = "CALL sp_Usuarios_Listar()";
        $res  = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $row['ConsecutivoUsuario'] = (int)$row['ConsecutivoUsuario'];
            $row['ConsecutivoPerfil']  = (int)$row['ConsecutivoPerfil'];
            $out[] = $row;
        }
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
    } catch (Exception $e) {
        useradmin_log_error($e);
    }
    return $out;
}

/**
 * Actualiza el perfil (rol) de un usuario.
 *
 * @param int $userId ConsecutivoUsuario
 * @param int $profileId ConsecutivoPerfil a asignar (1=admin, 2=usuario)
 * @return bool
 */
function changeUserRole($userId, $profileId) {
    $userId   = intval($userId);
    $profileId = intval($profileId);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Usuario_CambiarPerfil(?, ?)");
        $stmt->bind_param("ii", $userId, $profileId);
        $ok = $stmt->execute();
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
        return $ok;
    } catch (Exception $e) {
        useradmin_log_error($e);
        return false;
    }
}

?>