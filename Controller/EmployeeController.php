<?php
// Controller/EmployeeController.php
// Controlador para CRUD de empleados usando SOLO procedimientos almacenados (MySQL).

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/ConexionModel.php';

/* -----------------------------
   Helpers
------------------------------*/
function json_ok($payload = []) {
    echo json_encode(array_merge(['success' => true], $payload), JSON_UNESCAPED_UNICODE);
    exit;
}

function json_fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function require_admin() {
    $perfil = isset($_SESSION['ConsecutivoPerfil']) ? (int)$_SESSION['ConsecutivoPerfil'] : 0;
    if ($perfil !== 1) {
        json_fail('Acceso no autorizado', 403);
    }
}

function consume_all_results($conn) {
    // Evita "Commands out of sync" cuando se usan CALLs
    while ($conn->more_results()) {
        $conn->next_result();
        $extra = $conn->store_result();
        if ($extra instanceof mysqli_result) {
            $extra->free();
        }
    }
}

/* -----------------------------
   Router
------------------------------*/
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {

    switch ($action) {

        /* --------------------------------------------------------
           LISTAR EMPLEADOS ACTIVOS
           SP: sp_Empleados_ListarActivos()
        ---------------------------------------------------------*/
        case 'list':
            require_admin();

            $conn = OpenConnection();

            $res = $conn->query("CALL sp_Empleados_ListarActivos()");
            $employees = [];

            while ($row = $res->fetch_assoc()) {
                $employees[] = [
                    'id'     => (int)$row['id'],
                    'nombre' => (string)$row['nombre'],
                    'puesto' => (string)$row['puesto'],
                    'salario'=> (float)$row['salario'],
                    'activo' => (int)$row['activo'],
                ];
            }

            $res->free();
            consume_all_results($conn);
            CloseConnection($conn);

            json_ok(['data' => $employees]);
            break;

        /* --------------------------------------------------------
           VER EMPLEADO POR ID
           SP: sp_Empleado_ObtenerPorId(pId)
        ---------------------------------------------------------*/
        case 'view':
            require_admin();

            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_fail("ID inválido");

            $conn = OpenConnection();

            $stmt = $conn->prepare("CALL sp_Empleado_ObtenerPorId(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;

            if ($res instanceof mysqli_result) $res->free();
            $stmt->close();

            consume_all_results($conn);
            CloseConnection($conn);

            json_ok(['data' => $row ? [
                'id'     => (int)$row['id'],
                'nombre' => (string)$row['nombre'],
                'puesto' => (string)$row['puesto'],
                'salario'=> (float)$row['salario'],
                'activo' => (int)$row['activo'],
            ] : null]);

            break;

        /* --------------------------------------------------------
           CREAR EMPLEADO
           SP: sp_Empleado_Crear(pNombre, pPuesto, pSalario, pActivo)
        ---------------------------------------------------------*/
        case 'create':
            require_admin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_fail("Usar POST");
            }

            $nombre  = trim($_POST['nombre'] ?? '');
            $puesto  = trim($_POST['puesto'] ?? '');
            $salario = isset($_POST['salario']) ? (float)$_POST['salario'] : 0;
            $activo  = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

            if ($nombre === '') json_fail("Nombre requerido");
            if ($puesto === '') json_fail("Puesto requerido");
            if ($salario <= 0) json_fail("Salario debe ser mayor que cero");

            $conn = OpenConnection();

            $stmt = $conn->prepare("CALL sp_Empleado_Crear(?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $nombre, $puesto, $salario, $activo);
            $stmt->execute();

            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;

            if ($res instanceof mysqli_result) $res->free();
            $stmt->close();

            consume_all_results($conn);
            CloseConnection($conn);

            if (!$row || !isset($row['id'])) {
                json_fail("No se pudo crear el empleado");
            }

            json_ok(['id' => (int)$row['id']]);
            break;

        /* --------------------------------------------------------
           ACTUALIZAR EMPLEADO
           SP: sp_Empleado_Actualizar(pId, pNombre, pPuesto, pSalario, pActivo)
        ---------------------------------------------------------*/
        case 'update':
            require_admin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_fail("Usar POST");
            }

            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_fail("ID inválido");

            $nombre  = trim($_POST['nombre'] ?? '');
            $puesto  = trim($_POST['puesto'] ?? '');
            $salario = isset($_POST['salario']) ? (float)$_POST['salario'] : 0;
            $activo  = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

            if ($nombre === '') json_fail("Nombre requerido");
            if ($puesto === '') json_fail("Puesto requerido");
            if ($salario <= 0) json_fail("Salario debe ser mayor que cero");

            $conn = OpenConnection();

            $stmt = $conn->prepare("CALL sp_Empleado_Actualizar(?, ?, ?, ?, ?)");
            $stmt->bind_param("issdi", $id, $nombre, $puesto, $salario, $activo);
            $stmt->execute();
            $stmt->close();

            consume_all_results($conn);
            CloseConnection($conn);

            json_ok();
            break;

        /* --------------------------------------------------------
           ELIMINAR (SOFT DELETE)
           SP: sp_Empleado_Eliminar(pId)
        ---------------------------------------------------------*/
        case 'delete':
            require_admin();

            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
            if ($id <= 0) json_fail("ID inválido");

            $conn = OpenConnection();

            $stmt = $conn->prepare("CALL sp_Empleado_Eliminar(?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            consume_all_results($conn);
            CloseConnection($conn);

            json_ok();
            break;

        default:
            json_fail("Acción no soportada");
    }

} catch (mysqli_sql_exception $e) {
    // Esto te va a mostrar el error REAL de MySQL (SP faltante, permisos, etc.)
    if (function_exists('SaveError')) { try { SaveError($e); } catch (Exception $x) {} }
    json_fail("Error BD: " . $e->getMessage(), 500);

} catch (Exception $e) {
    if (function_exists('SaveError')) { try { SaveError($e); } catch (Exception $x) {} }
    json_fail($e->getMessage(), 400);
}
