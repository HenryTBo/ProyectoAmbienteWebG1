<?php
// Controller/EmployeeController.php
// Controlador para las operaciones de empleados (planilla).

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../Model/EmployeeModel.php';

// Iniciar sesión para verificar perfil
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario sea administrador (ConsecutivoPerfil == 1).
function employee_check_admin() {
    if (!isset($_SESSION['ConsecutivoPerfil']) || $_SESSION['ConsecutivoPerfil'] != '1') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
        exit;
    }
}

// Obtener la acción solicitada
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {

    switch ($action) {

        /* --------------------------------------------------------
           LISTAR EMPLEADOS
        ---------------------------------------------------------*/
        case 'list':
            employee_check_admin();
            $employees = getAllEmployees();
            echo json_encode(['success' => true, 'data' => $employees]);
            break;

        /* --------------------------------------------------------
           VER DETALLE
        ---------------------------------------------------------*/
        case 'view':
            employee_check_admin();
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $emp = getEmployeeById($id);
            echo json_encode(['success' => true, 'data' => $emp]);
            break;

        /* --------------------------------------------------------
           CREAR EMPLEADO
        ---------------------------------------------------------*/
        case 'create':
            employee_check_admin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Usar POST");

            $data = [
                'nombre'  => $_POST['nombre'] ?? '',
                'puesto'  => $_POST['puesto'] ?? '',
                'salario' => isset($_POST['salario']) ? floatval($_POST['salario']) : 0,
                'activo'  => isset($_POST['activo']) ? intval($_POST['activo']) : 1
            ];
            if (trim($data['nombre']) === '') throw new Exception("Nombre requerido");
            if (trim($data['puesto']) === '') throw new Exception("Puesto requerido");
            if ($data['salario'] <= 0) throw new Exception("Salario debe ser mayor que cero");

            $id = createEmployee($data);
            if ($id === false) throw new Exception("No se pudo crear el empleado");

            echo json_encode(['success' => true, 'id' => $id]);
            break;

        /* --------------------------------------------------------
           ACTUALIZAR EMPLEADO
        ---------------------------------------------------------*/
        case 'update':
            employee_check_admin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Usar POST");

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $data = [
                'nombre'  => $_POST['nombre'] ?? '',
                'puesto'  => $_POST['puesto'] ?? '',
                'salario' => isset($_POST['salario']) ? floatval($_POST['salario']) : 0,
                'activo'  => isset($_POST['activo']) ? intval($_POST['activo']) : 1
            ];
            if (trim($data['nombre']) === '') throw new Exception("Nombre requerido");
            if (trim($data['puesto']) === '') throw new Exception("Puesto requerido");
            if ($data['salario'] <= 0) throw new Exception("Salario debe ser mayor que cero");

            $ok = updateEmployee($id, $data);
            if (!$ok) throw new Exception("No se pudo actualizar");

            echo json_encode(['success' => true]);
            break;

        /* --------------------------------------------------------
           ELIMINAR EMPLEADO
        ---------------------------------------------------------*/
        case 'delete':
            employee_check_admin();
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $ok = deleteEmployee($id);
            if (!$ok) throw new Exception("No se pudo eliminar");

            echo json_encode(['success' => true]);
            break;

        /* --------------------------------------------------------
           ACCIÓN NO SOPORTADA
        ---------------------------------------------------------*/
        default:
            throw new Exception("Acción no soportada");
    }

} catch (Exception $e) {
    // Registrar error si es posible
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) {}
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}