<?php
// Model/EmployeeModel.php
// Manejo de empleados (planilla) mediante procedimientos almacenados.

/*
    Este modelo se encarga de todas las operaciones CRUD relacionadas
    con la tabla `empleados`. Para cumplir con la política de no usar
    consultas directas a la base de datos desde el código PHP, todas
    las operaciones se delegan a los procedimientos almacenados
    definidos en Respaldo.sql (sp_Empleados_ListarActivos, sp_Empleado_ObtenerPorId,
    sp_Empleado_Crear, sp_Empleado_Actualizar y sp_Empleado_Eliminar).

    Cada función abre la conexión utilizando OpenConnection() y la
    cierra mediante CloseConnection(), definidos en ConexionModel.php.
*/

include_once __DIR__ . '/ConexionModel.php';

/**
 * Registra un error en tberror usando SaveError si está disponible.
 * Se utiliza para atrapar excepciones de MySQL o de PHP.
 */
function employee_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    }
}

/**
 * Obtiene la lista de todos los empleados activos.
 *
 * @return array Lista de empleados activos.
 */
function getAllEmployees() {
    $out = [];
    try {
        $conn = OpenConnection();
        $sql  = "CALL sp_Empleados_ListarActivos()";
        $res  = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            // Convertir tipos
            $row['id']     = (int)$row['id'];
            $row['salario'] = (float)$row['salario'];
            $row['activo']  = (int)$row['activo'];
            $out[] = $row;
        }
        // Consumir cualquier resultado extra generado por el SP
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
    } catch (Exception $e) {
        employee_log_error($e);
    }
    return $out;
}

/**
 * Obtiene un empleado por su ID.
 *
 * @param int $id
 * @return array|null
 */
function getEmployeeById($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Empleado_ObtenerPorId(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res  = $stmt->get_result();
        $row  = $res->fetch_assoc();
        $stmt->close();
        // Consumir resultados adicionales
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
        if ($row) {
            $row['id']     = (int)$row['id'];
            $row['salario'] = (float)$row['salario'];
            $row['activo']  = (int)$row['activo'];
        }
        return $row ?: null;
    } catch (Exception $e) {
        employee_log_error($e);
        return null;
    }
}

/**
 * Crea un nuevo empleado. Devuelve el ID generado o false.
 *
 * @param array $data Arreglo con claves: nombre, puesto, salario, activo.
 * @return int|false
 */
function createEmployee($data) {
    try {
        $conn   = OpenConnection();
        $activo = isset($data['activo']) ? intval($data['activo']) : 1;
        $stmt   = $conn->prepare("CALL sp_Empleado_Crear(?, ?, ?, ?)");
        $stmt->bind_param(
            "ssdi",
            $data['nombre'],
            $data['puesto'],
            $data['salario'],
            $activo
        );
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
        return ($row && isset($row['id'])) ? intval($row['id']) : false;
    } catch (Exception $e) {
        employee_log_error($e);
        return false;
    }
}

/**
 * Actualiza un empleado existente.
 *
 * @param int $id ID del empleado
 * @param array $data Arreglo con claves: nombre, puesto, salario, activo
 * @return bool
 */
function updateEmployee($id, $data) {
    $id = intval($id);
    try {
        $conn   = OpenConnection();
        $activo = isset($data['activo']) ? intval($data['activo']) : 1;
        $stmt = $conn->prepare("CALL sp_Empleado_Actualizar(?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issdi",
            $id,
            $data['nombre'],
            $data['puesto'],
            $data['salario'],
            $activo
        );
        $ok = $stmt->execute();
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
        return $ok;
    } catch (Exception $e) {
        employee_log_error($e);
        return false;
    }
}

/**
 * Elimina (marca como inactivo) un empleado.
 *
 * @param int $id
 * @return bool
 */
function deleteEmployee($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Empleado_Eliminar(?)");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* consume rest */ }
        CloseConnection($conn);
        return $ok;
    } catch (Exception $e) {
        employee_log_error($e);
        return false;
    }
}

?>