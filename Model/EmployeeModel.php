<?php
require_once __DIR__ . '/ConexionModel.php';

/**
 * LISTAR EMPLEADOS ACTIVOS
 */
function getAllEmployees()
{
    $conn = getConnection();
    $stmt = $conn->prepare("CALL sp_Empleados_ListarActivos()");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * CREAR EMPLEADO
 */
function createEmployee($nombre, $puesto, $salario, $activo)
{
    $conn = getConnection();
    $stmt = $conn->prepare("CALL sp_Empleado_Crear(:nombre, :puesto, :salario, :activo)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':puesto', $puesto);
    $stmt->bindParam(':salario', $salario);
    $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['id'] ?? null;
}
