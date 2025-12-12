<?php
// Controller/OrderController.php
// Controlador para operaciones de pedidos (clientes y administradores).

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../Model/OrderModel.php';
include_once __DIR__ . '/../Model/ProductModel.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario tiene rol de administrador. Si no, emite respuesta 403 y termina.
 */
function order_check_admin() {
    if (!isset($_SESSION['ConsecutivoPerfil']) || $_SESSION['ConsecutivoPerfil'] != '1') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
        exit;
    }
}

/**
 * Verifica si el usuario está autenticado. Si no, emite respuesta 401 y termina.
 */
function order_check_auth() {
    if (!isset($_SESSION['ConsecutivoUsuario'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {

    switch ($action) {

        /* --------------------------------------------------------
           LISTAR PEDIDOS (ADMIN)
           Devuelve todos los pedidos con información del usuario.
        ---------------------------------------------------------*/
        case 'list':
            order_check_admin();
            $orders = getAllOrders();
            echo json_encode(['success' => true, 'data' => $orders]);
            break;

        /* --------------------------------------------------------
           LISTAR PEDIDOS DEL USUARIO ACTUAL
        ---------------------------------------------------------*/
        case 'my':
        case 'user':
            order_check_auth();
            $userId = intval($_SESSION['ConsecutivoUsuario']);
            $orders = getOrdersByUser($userId);
            echo json_encode(['success' => true, 'data' => $orders]);
            break;

        /* --------------------------------------------------------
           VER PEDIDO CON DETALLES
        ---------------------------------------------------------*/
        case 'view':
            // Solo autenticados pueden ver detalles. Admin puede ver cualquiera; usuario sólo si es suyo.
            order_check_auth();
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception('ID inválido');
            $pedido = getOrderById($id);
            if (!$pedido) throw new Exception('Pedido no encontrado');
            // Si no es admin, verificar que el pedido pertenezca al usuario
            if ($_SESSION['ConsecutivoPerfil'] != '1' && intval($pedido['ConsecutivoUsuario']) !== intval($_SESSION['ConsecutivoUsuario'])) {
                throw new Exception('No tienes permiso para ver este pedido');
            }
            $detalle = getOrderDetails($id);
            echo json_encode(['success' => true, 'data' => ['pedido' => $pedido, 'detalle' => $detalle]]);
            break;

        /* --------------------------------------------------------
           CREAR NUEVO PEDIDO (CLIENTE)
           Espera datos en formato JSON o FormData con campos:
           - items: JSON string de array de objetos { id_producto, cantidad }
        ---------------------------------------------------------*/
        case 'create':
            order_check_auth();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception('Usar POST');
            // Obtener items: intentar leer JSON en raw body o POST['items']
            $raw = file_get_contents('php://input');
            $items = [];
            if (!empty($_POST['items'])) {
                $items = json_decode($_POST['items'], true);
            } elseif ($raw) {
                $body = json_decode($raw, true);
                if (isset($body['items'])) {
                    $items = $body['items'];
                }
            }
            if (!$items || !is_array($items)) throw new Exception('Items inválidos');
            // Recuperar precios de los productos para evitar manipulaciones de cliente
            $allProducts = getAllProducts();
            $priceMap = [];
            foreach ($allProducts as $p) {
                $priceMap[$p['id']] = $p['precio'];
            }
            $orderItems = [];
            foreach ($items as $it) {
                $pid = intval($it['id_producto']);
                $cant= intval($it['cantidad']);
                if ($pid <= 0 || $cant <= 0) continue;
                $precioUnit = isset($priceMap[$pid]) ? $priceMap[$pid] : 0;
                $orderItems[] = ['id_producto' => $pid, 'cantidad' => $cant, 'precio' => $precioUnit];
            }
            if (empty($orderItems)) throw new Exception('No hay artículos válidos en el pedido');
            $userId = intval($_SESSION['ConsecutivoUsuario']);
            $pedidoId = createOrder($userId, $orderItems);
            if ($pedidoId === false) throw new Exception('No se pudo crear el pedido');
            echo json_encode(['success' => true, 'id' => $pedidoId]);
            break;

        /* --------------------------------------------------------
           ACTUALIZAR ESTADO DE PEDIDO (ADMIN)
        ---------------------------------------------------------*/
        case 'updateStatus':
            order_check_admin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception('Usar POST');
            $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $status = isset($_POST['estado']) ? trim($_POST['estado']) : '';
            if ($id <= 0 || $status === '') throw new Exception('Datos inválidos');
            $ok = updateOrderStatus($id, $status);
            if (!$ok) throw new Exception('No se pudo actualizar el estado');
            echo json_encode(['success' => true]);
            break;

        /* --------------------------------------------------------
           ACCIÓN NO SOPORTADA
        ---------------------------------------------------------*/
        default:
            throw new Exception('Acción no soportada');
    }

} catch (Exception $e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) {}
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}