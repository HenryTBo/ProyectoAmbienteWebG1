<?php
// Model/CartModel.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/ConexionModel.php';

/* =====================
   CARRITO EN SESIÓN
===================== */
function cart_init(): void {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_add(int $productId, int $qty = 1): void {
    cart_init();
    if ($productId <= 0) return;

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += max(1, $qty);
}

function cart_set_qty(int $productId, int $qty): void {
    cart_init();
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function cart_remove(int $productId): void {
    cart_init();
    unset($_SESSION['cart'][$productId]);
}

function cart_clear(): void {
    $_SESSION['cart'] = [];
}

function cart_count(): int {
    cart_init();
    return array_sum($_SESSION['cart']);
}

/* =====================
   PRODUCTO POR ID
===================== */
function product_get_by_id(int $id): ?array {
    $conn = getConnection();
    $res = $conn->query("CALL sp_Producto_ObtenerPorId($id)");

    if (!$res) return null;

    $row = $res->fetch_assoc();
    $res->free();

    while ($conn->more_results() && $conn->next_result()) {}
    $conn->close();

    return $row ?: null;
}

/* =====================
   ITEMS Y TOTALES
===================== */
function cart_get_items(): array {
    cart_init();
    $items = [];

    foreach ($_SESSION['cart'] as $id => $qty) {
        $p = product_get_by_id((int)$id);
        if (!$p) continue;

        $precio = (float)$p['precio'];

        $items[] = [
            'id' => (int)$p['id'],
            'nombre' => $p['nombre'],
            'precio' => $precio,
            'cantidad' => (int)$qty,
            'subtotal' => $precio * $qty,
            'imagen' => $p['imagen']
        ];
    }

    return $items;
}

function cart_totals(): array {
    $items = cart_get_items();
    $subtotal = 0;

    foreach ($items as $i) {
        $subtotal += $i['subtotal'];
    }

    return [
        'subtotal' => $subtotal,
        'envio' => 0,
        'total' => $subtotal,
        'items' => $items
    ];
}
