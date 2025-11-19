<?php
// Model/ProductModel.php
// Manejo de productos - usa OpenConnection() / CloseConnection() desde ConexionModel.php

if (!file_exists(__DIR__ . '/ConexionModel.php')) {
    // Asegúrate de que ConexionModel.php exista y provea OpenConnection() y CloseConnection()
}
include_once __DIR__ . '/ConexionModel.php';

function product_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    }
}

/**
 * Normaliza la ruta de imagen para un producto.
 * - Si $img es URL (http/https) lo deja.
 * - Si es ruta absoluta (empieza por /) y existe en server, la usa.
 * - Si no existe, intenta buscar en /ProyectoAmbienteWebG1/public/images/<safe>.(jpg|png)
 * - Si nada, devuelve placeholder.
 */
function normalizeProductImage($img, $nombre) {
    $placeholder = '/ProyectoAmbienteWebG1/public/images/placeholder.png';
    // Si viene una URL
    if (!empty($img) && (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)) {
        return $img;
    }
    // Si es ruta absoluta en el sitio (ej: /assets/...)
    if (!empty($img) && strpos($img, '/') === 0) {
        $serverPath = $_SERVER['DOCUMENT_ROOT'] . $img;
        if (file_exists($serverPath)) return $img;
    }

    // Construir nombre seguro a partir del nombre del producto
    $safe = preg_replace('/[^a-z0-9_\\.]/i', '_', strtolower($nombre));
    $candidateJpg = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.jpg';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidateJpg)) return $candidateJpg;
    $candidatePng = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.png';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidatePng)) return $candidatePng;

    // Si el campo $img no estaba vacío pero no existe en disco, devolver placeholder
    return $placeholder;
}

/**
 * Obtener todos los productos activos (normaliza imagen si está vacía)
 * @return array
 */
function getAllProducts() {
    $out = [];
    try {
        $conn = OpenConnection();
        $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
                FROM productos
                WHERE activo = 1
                ORDER BY categoria, nombre";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $row['precio'] = (float)$row['precio'];
            $row['stock'] = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen'] = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Obtener producto por id
 */
function getProductById($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo FROM productos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        CloseConnection($conn);
        if ($row) {
            $row['precio'] = (float)$row['precio'];
            $row['stock'] = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen'] = normalizeProductImage($row['imagen'], $row['nombre']);
        }
        return $row ?: null;
    } catch (Exception $e) {
        product_log_error($e);
        return null;
    }
}

/**
 * Crear producto
 * $data keys: nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
 */
function createProduct($data) {
    try {
        $conn = OpenConnection();
        $activo = isset($data['activo']) ? intval($data['activo']) : 1;
        $es_equipo = isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;
        $sql = "INSERT INTO productos (nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssdisssii",
            $data['nombre'],
            $data['descripcion'],
            $data['categoria'],
            $data['precio'],
            $data['stock'],
            $data['unidad'],
            $data['proveedor'],
            $data['imagen'],
            $es_equipo,
            $activo
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        CloseConnection($conn);
        return $ok ? $id : false;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Actualizar producto por id
 */
function updateProduct($id, $data) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $es_equipo = isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;
        $activo = isset($data['activo']) ? intval($data['activo']) : 1;
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoria = ?, precio = ?, stock = ?, unidad = ?, proveedor = ?, imagen = ?, es_equipo = ?, activo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssdisssiii",
            $data['nombre'],
            $data['descripcion'],
            $data['categoria'],
            $data['precio'],
            $data['stock'],
            $data['unidad'],
            $data['proveedor'],
            $data['imagen'],
            $es_equipo,
            $activo,
            $id
        );
        $ok = $stmt->execute();
        $stmt->close();
        CloseConnection($conn);
        return $ok;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Eliminar producto (soft delete)
 */
function deleteProduct($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        CloseConnection($conn);
        return $ok;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Buscar productos por término (nombre, descripcion, proveedor, categoria)
 */
function searchProducts($term) {
    $out = [];
    try {
        $conn = OpenConnection();
        $t = '%' . $term . '%';
        $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo FROM productos WHERE activo = 1 AND (nombre LIKE ? OR descripcion LIKE ? OR proveedor LIKE ? OR categoria LIKE ?)");
        $stmt->bind_param("ssss", $t, $t, $t, $t);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['precio'] = (float)$row['precio'];
            $row['stock'] = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen'] = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }
        $stmt->close();
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Productos por categoría
 */
function getProductsByCategory($cat) {
    $out = [];
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo FROM productos WHERE activo = 1 AND categoria = ? ORDER BY nombre");
        $stmt->bind_param("s", $cat);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['imagen'] = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }
        $stmt->close();
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Obtener solo equipos
 */
function getEquipmentProducts() {
    $out = [];
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo FROM productos WHERE activo = 1 AND es_equipo = 1 ORDER BY nombre");
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['imagen'] = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }
        $stmt->close();
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}
?>
