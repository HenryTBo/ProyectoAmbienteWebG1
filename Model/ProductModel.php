<?php
// Model/ProductModel.php
// Manejo de productos - usa OpenConnection() / CloseConnection() desde ConexionModel.php

if (!file_exists(__DIR__ . '/ConexionModel.php')) {
    // Asegúrate de que ConexionModel.php exista y provea OpenConnection() y CloseConnection()
}
include_once __DIR__ . '/ConexionModel.php';

/**
 * Registra un error en tberror usando SaveError si está disponible.
 */
function product_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    } else {
        // fallback
        error_log('[ProductModel] ' . $e->getMessage());
    }
}

/**
 * Consume resultados restantes de un SP para liberar el handler.
 */
function product_consume_results($conn) {
    if (!$conn) return;
    try {
        while ($conn->more_results() && $conn->next_result()) { /* consume */ }
    } catch (Throwable $e) { /* ignore */ }
}

/**
 * Normaliza la ruta de imagen para un producto.
 */
function normalizeProductImage($img, $nombre) {
    $placeholder = '/ProyectoAmbienteWebG1/public/images/placeholder.png';
    // URL
    if (!empty($img) && (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)) {
        return $img;
    }
    // Ruta absoluta
    if (!empty($img) && strpos($img, '/') === 0) {
        $serverPath = $_SERVER['DOCUMENT_ROOT'] . $img;
        if (file_exists($serverPath)) return $img;
    }

    // Buscar por nombre
    $safe = preg_replace('/[^a-z0-9_\.]/i', '_', strtolower($nombre));
    $candidateJpg = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.jpg';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidateJpg)) return $candidateJpg;

    $candidatePng = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.png';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidatePng)) return $candidatePng;

    return $placeholder;
}

/**
 * Lista todos los productos activos utilizando procedimiento almacenado.
 */
function getAllProducts() {
    $out = [];
    try {
        $conn = OpenConnection();
        $sql = "CALL sp_Productos_ListarActivos()";
        $res = $conn->query($sql);

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = (float)$row['precio'];
            $row['stock']     = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen']    = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }

        product_consume_results($conn);
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Obtener producto por id utilizando procedimiento almacenado.
 */
function getProductById($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Producto_ObtenerPorId(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        product_consume_results($conn);
        CloseConnection($conn);

        if ($row) {
            $row['precio']    = (float)$row['precio'];
            $row['stock']     = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen']    = normalizeProductImage($row['imagen'], $row['nombre']);
        }
        return $row ?: null;
    } catch (Exception $e) {
        product_log_error($e);
        return null;
    }
}

/**
 * Crear producto vía procedimiento almacenado. Devuelve el ID generado o false.
 */
function createProduct($data) {
    try {
        $conn     = OpenConnection();
        $activo   = isset($data['activo']) ? intval($data['activo']) : 1;
        $es_equipo= isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;

        $stmt = $conn->prepare("CALL sp_Producto_Crear(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        product_consume_results($conn);
        CloseConnection($conn);

        return ($row && isset($row['id'])) ? intval($row['id']) : false;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Actualizar producto por id vía procedimiento almacenado.
 */
function updateProduct($id, $data) {
    $id = intval($id);
    try {
        $conn      = OpenConnection();
        $es_equipo = isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;
        $activo    = isset($data['activo']) ? intval($data['activo']) : 1;

        $stmt = $conn->prepare("CALL sp_Producto_Actualizar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssdisssii",
            $id,
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
        $stmt->close();

        product_consume_results($conn);
        CloseConnection($conn);

        return $ok;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Soft delete directo (fallback cuando hay FK por pedidos).
 */
function softDeleteProduct($id) {
    $id = intval($id);
    try {
        $conn = OpenConnection();

        // Ajusta el nombre de la tabla si en tu BD no se llama "productos"
        $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        product_consume_results($conn);
        CloseConnection($conn);

        return $ok;
    } catch (Exception $e) {
        product_log_error($e);
        return false;
    }
}

/**
 * Desactivar/eliminar producto.
 * - Intenta SP sp_Producto_Eliminar
 * - Si falla (FK/pedidos), hace soft delete: activo=0
 *
 * Retorna un array:
 *  ['success'=>bool, 'mode'=>'hard'|'soft'|'none', 'message'=>string]
 */
function deleteProduct($id) {
    $id = intval($id);
    if ($id <= 0) {
        return ['success' => false, 'mode' => 'none', 'message' => 'ID inválido'];
    }

    // 1) Intento con SP
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Producto_Eliminar(?)");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        product_consume_results($conn);
        CloseConnection($conn);

        if ($ok) {
            return ['success' => true, 'mode' => 'hard', 'message' => 'Producto eliminado correctamente.'];
        }
        // si execute devolvió false, cae a soft delete
    } catch (Exception $e) {
        // log y cae a soft delete
        product_log_error($e);
    }

    // 2) Fallback soft delete
    $soft = softDeleteProduct($id);
    if ($soft) {
        return [
            'success' => true,
            'mode' => 'soft',
            'message' => 'El producto está asociado a pedidos. Se desactivó (activo=0).'
        ];
    }

    return ['success' => false, 'mode' => 'none', 'message' => 'No se pudo eliminar ni desactivar el producto.'];
}

/**
 * Buscar productos por término via SP.
 */
function searchProducts($term) {
    $out = [];
    try {
        $conn = OpenConnection();
        $t    = '%' . $term . '%';
        $stmt = $conn->prepare("CALL sp_Productos_Buscar(?)");
        $stmt->bind_param("s", $t);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = (float)$row['precio'];
            $row['stock']     = (int)$row['stock'];
            $row['es_equipo'] = (int)$row['es_equipo'];
            $row['imagen']    = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }

        $stmt->close();
        product_consume_results($conn);
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Obtener productos por categoría via SP.
 */
function getProductsByCategory($cat) {
    $out = [];
    try {
        $conn = OpenConnection();
        $stmt = $conn->prepare("CALL sp_Productos_PorCategoria(?)");
        $stmt->bind_param("s", $cat);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = isset($row['precio']) ? (float)$row['precio'] : null;
            $row['stock']     = isset($row['stock']) ? (int)$row['stock'] : null;
            $row['es_equipo'] = isset($row['es_equipo']) ? (int)$row['es_equipo'] : null;
            $row['imagen']    = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }

        $stmt->close();
        product_consume_results($conn);
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}

/**
 * Obtener únicamente productos que son equipos via SP.
 */
function getEquipmentProducts() {
    $out = [];
    try {
        $conn = OpenConnection();
        $res  = $conn->query("CALL sp_Productos_Equipos()");

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = isset($row['precio']) ? (float)$row['precio'] : null;
            $row['stock']     = isset($row['stock']) ? (int)$row['stock'] : null;
            $row['es_equipo'] = isset($row['es_equipo']) ? (int)$row['es_equipo'] : null;
            $row['imagen']    = normalizeProductImage($row['imagen'], $row['nombre']);
            $out[] = $row;
        }

        product_consume_results($conn);
        CloseConnection($conn);
    } catch (Exception $e) {
        product_log_error($e);
    }
    return $out;
}
?>
