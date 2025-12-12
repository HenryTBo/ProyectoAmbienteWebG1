<?php
// OrderModel.php
// Versión revisada con mejor manejo de errores para creación de pedidos

require_once __DIR__ . '/ConexionModel.php';

/**
 * Registra errores relacionados con pedidos en la tabla tberror
 */
function order_log_error($e) {
    try {
        if ($e instanceof Throwable) {
            $msg = '[PEDIDOS] ' . $e->getMessage();
        } else {
            $msg = '[PEDIDOS] ' . (string)$e;
        }

        if (function_exists('SaveError')) {
            // SaveError debe llamar internamente al SP RegistrarError
            SaveError($msg);
        }
    } catch (Throwable $e2) {
        // No romper la ejecución si el log falla
    }
}

/**
 * Crea un pedido con sus detalles usando procedimientos almacenados.
 *
 * @param int   $userId  ConsecutivoUsuario de tbusuario
 * @param array $items   Cada item: ['id_producto' => int, 'cantidad' => int, 'precio' => float]
 * @return int           ID del pedido creado
 * @throws Exception     Si algo falla, para que el controlador pueda mostrar el motivo real
 */
function createOrder($userId, $items) {
    // Validaciones básicas
    if (!$items || !is_array($items) || empty($items)) {
        throw new Exception('No hay artículos en el carrito.');
    }

    $userId = (int)$userId;
    if ($userId <= 0) {
        throw new Exception('Usuario no válido al crear el pedido. Vuelva a iniciar sesión.');
    }

    // Calcular total del pedido
    $total = 0.0;
    foreach ($items as $it) {
        $cantidad = isset($it['cantidad']) ? (float)$it['cantidad'] : 0;
        $precio   = isset($it['precio']) ? (float)$it['precio'] : 0;
        $total   += $cantidad * $precio;
    }

    if ($total <= 0) {
        throw new Exception('El total del pedido es 0. Verifique las cantidades.');
    }

    $conn = OpenConnection();

    // Hacer que mysqli lance excepciones en caso de error
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    try {
        $conn->begin_transaction();

        /**
         * 1) Crear la cabecera del pedido con el SP sp_Pedidos_Crear
         */
        $estadoInicial = 'Pendiente';
        $stmtPedido = $conn->prepare('CALL sp_Pedidos_Crear(?, ?, ?)');
        if (!$stmtPedido) {
            throw new Exception('Error al preparar sp_Pedidos_Crear: ' . $conn->error);
        }

        // tipos: i = int, s = string, d = double
        $stmtPedido->bind_param('isd', $userId, $estadoInicial, $total);
        $stmtPedido->execute();

        // Intentar obtener el id devuelto por el SP
        $row = null;
        if (method_exists($stmtPedido, 'get_result')) {
            $result = $stmtPedido->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
            }
        }

        $stmtPedido->close();
        // Limpiar posibles result sets adicionales del CALL
        while ($conn->more_results() && $conn->next_result()) {
            /* limpiar */
        }

        // Si el SP no devolvió el id, usar LAST_INSERT_ID() como respaldo
        if (!$row || !isset($row['id']) || !$row['id']) {
            $res2 = $conn->query('SELECT LAST_INSERT_ID() AS id');
            $row2 = $res2 ? $res2->fetch_assoc() : null;
            if ($row2 && !empty($row2['id'])) {
                $row = $row2;
            }
        }

        if (!$row || !isset($row['id']) || !$row['id']) {
            throw new Exception(
                'sp_Pedidos_Crear no devolvió un ID válido. Último error MySQL: ' . $conn->error
            );
        }

        $pedidoId = (int)$row['id'];

        /**
         * 2) Insertar los detalles del pedido con el SP sp_PedidoDetalle_Agregar
         */
        foreach ($items as $it) {
            $idProd   = isset($it['id_producto']) ? (int)$it['id_producto'] : 0;
            $cantidad = isset($it['cantidad']) ? (int)$it['cantidad'] : 0;
            $precio   = isset($it['precio']) ? (float)$it['precio'] : 0;

            if ($idProd <= 0) {
                throw new Exception('Producto inválido en el pedido.');
            }
            if ($cantidad <= 0) {
                throw new Exception('Cantidad inválida para el producto ' . $idProd);
            }

            $stmtDet = $conn->prepare('CALL sp_PedidoDetalle_Agregar(?, ?, ?, ?)');
            if (!$stmtDet) {
                throw new Exception('Error al preparar sp_PedidoDetalle_Agregar: ' . $conn->error);
            }

            $stmtDet->bind_param('iiid', $pedidoId, $idProd, $cantidad, $precio);
            $stmtDet->execute();
            $stmtDet->close();

            // Limpiar result sets del CALL
            while ($conn->more_results() && $conn->next_result()) {
                /* limpiar */
            }
        }

        // Si todo va bien, confirmamos la transacción
        $conn->commit();
        CloseConnection($conn);

        return $pedidoId;

    } catch (Exception $e) {
        // Deshacer todo y propagar el error real
        try {
            if ($conn) {
                $conn->rollback();
            }
        } catch (Throwable $t) {
            // ignorar
        }

        order_log_error($e);
        CloseConnection($conn);
        throw $e;
    }
}
