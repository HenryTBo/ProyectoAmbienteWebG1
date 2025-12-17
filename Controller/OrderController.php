<?php
// Controller/OrderController.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/CartModel.php';
require_once __DIR__ . '/../Model/OrderModel.php';
require_once __DIR__ . '/../Model/ConexionModel.php';

// ------------------------
// Helpers de sesión/roles
// ------------------------
function currentUserId(): int {
    return (int)($_SESSION["ConsecutivoUsuario"] ?? ($_SESSION["User"]["ConsecutivoUsuario"] ?? 0));
}

function currentPerfil(): string {
    return (string)($_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2"));
}

function requireLogin(): int {
    $uid = currentUserId();
    if ($uid <= 0) throw new Exception("Debe iniciar sesión.");
    return $uid;
}

function requireAdmin(): void {
    if (currentPerfil() !== "1") throw new Exception("Acceso denegado (solo administrador).");
}

// ------------------------
// DB helpers (TU PROYECTO usa OpenConnection/CloseConnection)
// ------------------------
function db_open() {
    if (function_exists('OpenConnection')) return OpenConnection();
    if (function_exists('getConnection')) return getConnection(); // fallback
    throw new Exception("No existe OpenConnection() en ConexionModel.php");
}

function db_close($conn): void {
    if (!$conn) return;

    try {
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    } catch (Throwable $e) { /* ignore */ }

    try {
        if (function_exists('CloseConnection')) {
            CloseConnection($conn);
        } else {
            $conn->close();
        }
    } catch (Throwable $e) { /* ignore */ }
}

// ✅ Helper: listar pedidos por usuario (SP sp_Pedidos_Usuario_Listar)
function listOrdersByUser(int $userId): array {
    if ($userId <= 0) return [];

    $conn = db_open();
    $stmt = $conn->prepare("CALL sp_Pedidos_Usuario_Listar(?)");
    if (!$stmt) {
        $err = $conn->error;
        db_close($conn);
        throw new Exception("Prepare sp_Pedidos_Usuario_Listar failed: " . $err);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $res = $stmt->get_result();
    $data = [];
    while ($res && ($row = $res->fetch_assoc())) $data[] = $row;

    $stmt->close();
    db_close($conn);

    return $data;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'listAdmin';

try {
    switch ($action) {

        // =========================
        // CLIENTE: LISTAR MIS PEDIDOS
        // =========================
        case 'my':
        case 'list': {
            $userId = requireLogin();
            $orders = listOrdersByUser($userId);
            echo json_encode(['success' => true, 'data' => $orders]);
            break;
        }

        // =========================
        // CLIENTE: FINALIZAR PEDIDO
        // =========================
        case 'create': {
            $userId = requireLogin();

            // ✅ Acepta ambos: entrega_tipo (nuevo) y entrega (compat)
            $entrega = $_POST['entrega_tipo'] ?? ($_POST['entrega'] ?? 'Tienda');
            $direccion = $_POST['direccion'] ?? '';

            $cart = cart_totals();
            $items = $cart['items'] ?? [];
            if (empty($items)) throw new Exception("No hay productos en el carrito.");

            $pedidoId = createOrder($userId, $items, $entrega, $direccion);

            // Limpiar carrito
            cart_clear();

            echo json_encode(['success' => true, 'pedido_id' => $pedidoId]);
            break;
        }

        // =========================
        // ADMIN: LISTAR PEDIDOS
        // =========================
        case 'listAdmin':
        case 'all': {
            requireAdmin();

            $orders = listOrdersAdmin();
            $drivers = function_exists('listDrivers') ? listDrivers() : [];

            echo json_encode([
                'success' => true,
                'data' => $orders,
                'orders' => $orders,
                'drivers' => $drivers
            ]);
            break;
        }

        // =========================
        // ADMIN: ASIGNAR CHOFER
        // =========================
        case 'assignDriver': {
            requireAdmin();

            $pedidoId = (int)($_POST['pedido_id'] ?? 0);
            $driverId = (int)($_POST['driver_id'] ?? 0);
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");
            if ($driverId <= 0) throw new Exception("driver_id inválido.");

            $ok = assignDriver($pedidoId, $driverId);
            echo json_encode(['success' => (bool)$ok]);
            break;
        }

        // =========================
        // ADMIN: ACTUALIZAR ESTADO
        // =========================
        case 'updateStatus':
        case 'setStatus': {
            requireAdmin();

            $pedidoId = (int)($_POST['pedido_id'] ?? $_POST['id'] ?? 0);
            $estado = (string)($_POST['estado'] ?? '');
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");
            if (trim($estado) === '') throw new Exception("estado requerido.");

            $ok = updateOrderStatus($pedidoId, $estado);
            echo json_encode(['success' => (bool)$ok]);
            break;
        }

        // =========================
        // ADMIN/CLIENTE: DETALLE
        // =========================
        case 'details': {
            $pedidoId = (int)($_GET['pedido_id'] ?? $_GET['id'] ?? 0);
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");

            $detail = getOrderDetails($pedidoId);

            $payload = [
                'order' => $detail['header'] ?? null,
                'items' => $detail['items'] ?? []
            ];

            echo json_encode([
                'success' => true,
                'data' => $payload,
                'order' => $payload['order'],
                'items' => $payload['items']
            ]);
            break;
        }

        default:
            throw new Exception("Acción no soportada en OrderController: $action");
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
