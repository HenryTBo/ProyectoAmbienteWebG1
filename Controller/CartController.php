<?php
// Controller/CartController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/CartModel.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {

    switch ($action) {

        case 'addAjax': {
            $id = intval($_POST['product_id'] ?? $_POST['idProducto'] ?? 0);
            $qty = intval($_POST['qty'] ?? $_POST['cantidad'] ?? 1);

            if ($id <= 0) {
                throw new Exception('Producto inválido');
            }

            cart_add($id, $qty);

            echo json_encode([
                'success' => true,
                'count' => cart_count()
            ]);
            break;
        }

        case 'list': {
            echo json_encode([
                'success' => true,
                'data' => cart_totals()
            ]);
            break;
        }

        case 'count': {
            echo json_encode([
                'count' => cart_count()
            ]);
            break;
        }

        case 'setQty': {
            $id = intval($_POST['id'] ?? 0);
            $qty = intval($_POST['qty'] ?? 1);

            cart_set_qty($id, $qty);
            echo json_encode(['success' => true]);
            break;
        }

        case 'remove': {
            $id = intval($_POST['id'] ?? 0);
            cart_remove($id);
            echo json_encode(['success' => true]);
            break;
        }

        case 'clear': {
            cart_clear();
            echo json_encode(['success' => true]);
            break;
        }

        /* DEBUG — puedes borrarlo luego */
        case 'raw': {
            echo json_encode([
                'success' => true,
                'session_id' => session_id(),
                'cart' => $_SESSION['cart'] ?? null
            ]);
            break;
        }

        default:
            throw new Exception('Acción no válida');
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
