<?php
// Model/OrderModel.php
require_once __DIR__ . '/ConexionModel.php';

function listDrivers(): array {
    $conn = getConnection();
    $res = $conn->query("CALL sp_Conductores_Listar()");
    if (!$res) throw new Exception("Error listDrivers: " . $conn->error);

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;

    $res->free();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    $conn->close();
    return $data;
}

function createOrder(int $consecutivoUsuario, array $cartItems, string $entregaTipo, string $direccion): int {
    if ($consecutivoUsuario <= 0) throw new Exception("Usuario inválido para crear pedido.");
    if (empty($cartItems)) throw new Exception("El carrito está vacío.");

    $entregaTipo = trim($entregaTipo ?: 'Tienda');
    if ($entregaTipo !== 'Tienda' && $entregaTipo !== 'Domicilio') $entregaTipo = 'Tienda';

    $direccion = trim($direccion);
    if ($entregaTipo === 'Domicilio' && $direccion === '') {
        throw new Exception("La dirección es obligatoria si la entrega es a domicilio.");
    }

    // Calcular total
    $total = 0.0;
    foreach ($cartItems as $it) {
        $total += (float)$it['subtotal'];
    }

    $conn = getConnection();
    $conn->begin_transaction();

    try {
        // 1) Crear pedido
        // sp_Pedidos_Crear(pConsecutivoUsuario INT, pEstado VARCHAR(50), pTotal DECIMAL(12,2))
        $stmt = $conn->prepare("CALL sp_Pedidos_Crear(?, ?, ?)");
        if (!$stmt) throw new Exception("Prepare sp_Pedidos_Crear failed: " . $conn->error);

        $estado = 'Pendiente';
        $stmt->bind_param("isd", $consecutivoUsuario, $estado, $total);
        $stmt->execute();

        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $pedidoId = (int)($row['id'] ?? 0);

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }

        if ($pedidoId <= 0) throw new Exception("No se pudo obtener ID del pedido.");

        // 2) Actualizar entrega
        // sp_Pedido_ActualizarEntrega(pIdPedido INT, pEntregaTipo VARCHAR(20), pDireccion VARCHAR(255))
        $stmt2 = $conn->prepare("CALL sp_Pedido_ActualizarEntrega(?, ?, ?)");
        if (!$stmt2) throw new Exception("Prepare sp_Pedido_ActualizarEntrega failed: " . $conn->error);

        $stmt2->bind_param("iss", $pedidoId, $entregaTipo, $direccion);
        $stmt2->execute();
        $stmt2->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }

        // 3) Insertar detalle
        // sp_PedidoDetalle_Agregar(pIdPedido INT, pIdProducto INT, pCantidad INT, pPrecio DECIMAL(12,2))
        $stmt3 = $conn->prepare("CALL sp_PedidoDetalle_Agregar(?, ?, ?, ?)");
        if (!$stmt3) throw new Exception("Prepare sp_PedidoDetalle_Agregar failed: " . $conn->error);

        foreach ($cartItems as $it) {
            $idProducto = (int)$it['id'];
            $cantidad = (int)$it['cantidad'];
            $precio = (float)$it['precio'];

            $stmt3->bind_param("iiid", $pedidoId, $idProducto, $cantidad, $precio);
            $stmt3->execute();
            while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        }

        $stmt3->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }

        $conn->commit();
        $conn->close();
        return $pedidoId;

    } catch (Throwable $e) {
        $conn->rollback();
        $conn->close();
        throw $e;
    }
}

function listOrdersAdmin(): array {
    $conn = getConnection();
    $res = $conn->query("CALL sp_Pedidos_Listar()");
    if (!$res) throw new Exception("Error listOrdersAdmin: " . $conn->error);

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;

    $res->free();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    $conn->close();
    return $data;
}

function getOrderDetails(int $pedidoId): array {
    $conn = getConnection();

    // Header
    $stmt = $conn->prepare("CALL sp_Pedido_ObtenerPorId(?)");
    if (!$stmt) throw new Exception("Prepare sp_Pedido_ObtenerPorId failed: " . $conn->error);

    $stmt->bind_param("i", $pedidoId);
    $stmt->execute();
    $res = $stmt->get_result();
    $header = $res ? $res->fetch_assoc() : null;

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }

    if (!$header) {
        $conn->close();
        throw new Exception("Pedido no encontrado.");
    }

    // Detail
    $stmt2 = $conn->prepare("CALL sp_Pedido_Detalle(?)");
    if (!$stmt2) throw new Exception("Prepare sp_Pedido_Detalle failed: " . $conn->error);

    $stmt2->bind_param("i", $pedidoId);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    $items = [];
    while ($row = $res2->fetch_assoc()) $items[] = $row;

    $stmt2->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }

    $conn->close();

    return [
        'header' => $header,
        'items' => $items
    ];
}

function assignDriver(int $pedidoId, int $driverId): bool {
    $conn = getConnection();

    // sp_Pedido_AsignarConductor(pIdPedido INT, pIdConductor INT)
    $stmt = $conn->prepare("CALL sp_Pedido_AsignarConductor(?, ?)");
    if (!$stmt) throw new Exception("Prepare sp_Pedido_AsignarConductor failed: " . $conn->error);

    $stmt->bind_param("ii", $pedidoId, $driverId);
    $ok = $stmt->execute();

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    $conn->close();

    return (bool)$ok;
}

function updateOrderStatus(int $pedidoId, string $estado): bool {
    $estado = trim($estado);
    if ($estado === '') throw new Exception("Estado inválido.");

    $conn = getConnection();

    // sp_Pedido_ActualizarEstado(pIdPedido INT, pEstado VARCHAR(50))
    $stmt = $conn->prepare("CALL sp_Pedido_ActualizarEstado(?, ?)");
    if (!$stmt) throw new Exception("Prepare sp_Pedido_ActualizarEstado failed: " . $conn->error);

    $stmt->bind_param("is", $pedidoId, $estado);
    $ok = $stmt->execute();

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
    $conn->close();

    return (bool)$ok;
}
