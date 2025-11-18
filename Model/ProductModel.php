<?php
// Model/ProductModel.php
// Funciones para manejar productos (usa OpenConnection() de ConexionModel.php)

if (!file_exists(__DIR__ . '/ConexionModel.php')) {
    // opcional: lanzar o dejar que el controller se encargue
}

include_once __DIR__ . '/ConexionModel.php';

function product_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    }
}

/**
 * Obtener todos los productos activos
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
        return $row ?: null;
    } catch (Exception $e) {
        product_log_error($e);
        return null;
    }
}

/**
 * Crear producto
 * $data: assoc array con keys: nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo (0/1), activo (opc)
 * Retorna id insertado o false
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
 * $data: campos a actualizar (mismos keys que createProduct)
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
 * Eliminar producto (soft delete: activo = 0)
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
        while ($row = $res->fetch_assoc()) $out[] = $row;
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
        while ($row = $res->fetch_assoc()) $out[] = $row;
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
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

?>
