<?php
// Controller/OrderController.php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../Model/CartModel.php';
require_once __DIR__ . '/../Model/OrderModel.php';
require_once __DIR__ . '/../Model/ConexionModel.php'; // por si tu OrderModel lo requiere indirectamente

// ✅ Helper: listar pedidos por usuario (usa SP sp_Pedidos_Usuario_Listar)
function listOrdersByUser(int $userId): array {
    if ($userId <= 0) return [];
    $conn = getConnection();

    $stmt = $conn->prepare("CALL sp_Pedidos_Usuario_Listar(?)");
    if (!$stmt) throw new Exception("Prepare sp_Pedidos_Usuario_Listar failed: " . $conn->error);

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    $conn->close();

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
            $userId = (int)($_SESSION["ConsecutivoUsuario"] ?? ($_SESSION["User"]["ConsecutivoUsuario"] ?? 0));
            if ($userId <= 0) throw new Exception("Debe iniciar sesión.");

            $orders = listOrdersByUser($userId);
            echo json_encode(['success' => true, 'data' => $orders]);
            break;
        }

        // =========================
        // CLIENTE: FINALIZAR PEDIDO
        // =========================
        case 'create': {
            $userId = (int)($_SESSION["ConsecutivoUsuario"] ?? ($_SESSION["User"]["ConsecutivoUsuario"] ?? 0));
            if ($userId <= 0) throw new Exception("Debe iniciar sesión.");

            $entrega = $_POST['entrega_tipo'] ?? 'Tienda';
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
        case 'listAdmin': {
            $orders = listOrdersAdmin();
            $drivers = listDrivers();
            echo json_encode(['success' => true, 'orders' => $orders, 'drivers' => $drivers]);
            break;
        }

        // =========================
        // ADMIN: ASIGNAR CHOFER
        // =========================
        case 'assignDriver': {
            $pedidoId = (int)($_POST['pedido_id'] ?? 0);
            $driverId = (int)($_POST['driver_id'] ?? 0);
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");
            if ($driverId <= 0) throw new Exception("driver_id inválido.");

            $ok = assignDriver($pedidoId, $driverId);
            echo json_encode(['success' => $ok]);
            break;
        }

        // =========================
        // ADMIN: ACTUALIZAR ESTADO
        // =========================
        case 'updateStatus': {
            $pedidoId = (int)($_POST['pedido_id'] ?? 0);
            $estado = (string)($_POST['estado'] ?? '');
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");

            $ok = updateOrderStatus($pedidoId, $estado);
            echo json_encode(['success' => $ok]);
            break;
        }

        // =========================
        // ADMIN/CLIENTE: DETALLE
        // Compatibilidad: acepta ?pedido_id= o ?id=
        // =========================
        case 'details': {
            $pedidoId = (int)($_GET['pedido_id'] ?? $_GET['id'] ?? 0);
            if ($pedidoId <= 0) throw new Exception("pedido_id inválido.");

            $detail = getOrderDetails($pedidoId);

            // detail = ['header'=>..., 'items'=>...]
            $payload = [
                'order' => $detail['header'] ?? null,
                'items' => $detail['items'] ?? []
            ];

            echo json_encode([
                'success' => true,
                'data' => $payload,
                // compat: también por fuera (por si alguna vista lo usa)
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
