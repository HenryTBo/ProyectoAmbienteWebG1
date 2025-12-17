<?php
// Model/ProductModel.php
// Manejo de productos - usa OpenConnection() / CloseConnection() desde ConexionModel.php

include_once __DIR__ . '/ConexionModel.php';

/**
 * Registra un error en tberror usando SaveError si está disponible.
 */
function product_log_error($e) {
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Throwable $ex) { /* ignore */ }
    }
}

/**
 * Normaliza la ruta de imagen para un producto.
 * - Si $img es URL (http/https) -> se deja tal cual.
 * - Si es ruta absoluta (empieza por /) -> se deja tal cual.
 * - Si es ruta relativa (ej: "imagenes/xxx.jpg") -> se deja tal cual (EL CONTROLLER la vuelve absoluta).
 * - Si viene vacío -> placeholder.
 *
 * IMPORTANTE:
 * Antes estabas ignorando rutas relativas válidas y devolviendo placeholder, por eso
 * las imágenes subidas "no se reflejaban".
 */
function normalizeProductImage($img, $nombre) {
    $placeholder = '/ProyectoAmbienteWebG1/public/images/placeholder.png';

    $img = trim((string)$img);

    // URL externa
    if ($img !== '' && (stripos($img, 'http://') === 0 || stripos($img, 'https://') === 0)) {
        return $img;
    }

    // Ruta absoluta web
    if ($img !== '' && strpos($img, '/') === 0) {
        return $img;
    }

    // Ruta relativa (ej: imagenes/prod_xxx.jpg) => NO la mates, devuélvela.
    // El Controller ya la convierte a absoluta con projectBaseUrl().
    if ($img !== '') {
        return $img;
    }

    return $placeholder;
}

/**
 * Helper seguro para preparar SP y lanzar Exception si falla.
 */
function product_prepare(mysqli $conn, string $sql): mysqli_stmt {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: {$conn->error}");
    }
    return $stmt;
}

/**
 * Lista todos los productos activos utilizando procedimiento almacenado.
 * @return array
 */
function getAllProducts() {
    $out = [];
    $conn = null;

    try {
        $conn = OpenConnection();
        $sql = "CALL sp_Productos_ListarActivos()";

        $res = $conn->query($sql);
        if (!$res) {
            throw new Exception("Query failed sp_Productos_ListarActivos: {$conn->error}");
        }

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = (float)($row['precio'] ?? 0);
            $row['stock']     = (int)($row['stock'] ?? 0);
            $row['es_equipo'] = (int)($row['es_equipo'] ?? 0);
            $row['imagen']    = normalizeProductImage($row['imagen'] ?? '', $row['nombre'] ?? '');
            $out[] = $row;
        }

        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
    }

    return $out;
}

/**
 * Obtener producto por id utilizando procedimiento almacenado.
 */
function getProductById($id) {
    $id = intval($id);
    $conn = null;

    try {
        $conn = OpenConnection();
        $stmt = product_prepare($conn, "CALL sp_Producto_ObtenerPorId(?)");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Producto_ObtenerPorId: {$stmt->error}");
        }

        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

        if ($row) {
            $row['precio']    = (float)($row['precio'] ?? 0);
            $row['stock']     = (int)($row['stock'] ?? 0);
            $row['es_equipo'] = (int)($row['es_equipo'] ?? 0);
            $row['imagen']    = normalizeProductImage($row['imagen'] ?? '', $row['nombre'] ?? '');
        }

        return $row ?: null;

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
        return null;
    }
}

/**
 * Crear producto vía procedimiento almacenado. Devuelve el ID generado o false.
 */
function createProduct($data) {
    $conn = null;

    try {
        $conn     = OpenConnection();
        $activo   = isset($data['activo']) ? intval($data['activo']) : 1;
        $es_equipo= isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;

        $stmt = product_prepare($conn, "CALL sp_Producto_Crear(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Producto_Crear: {$stmt->error}");
        }

        // SP hace SELECT LAST_INSERT_ID() AS id
        $id = 0;
        $res = $stmt->get_result();
        if ($res) {
            $row = $res->fetch_assoc();
            $id = (int)($row['id'] ?? 0);
        } else {
            // Fallback por si get_result no está disponible
            $id = (int)$conn->insert_id;
        }

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

        return ($id > 0) ? $id : false;

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
        return false;
    }
}

/**
 * Actualizar producto por id vía procedimiento almacenado.
 */
function updateProduct($id, $data) {
    $id = intval($id);
    $conn = null;

    try {
        $conn      = OpenConnection();
        $es_equipo = isset($data['es_equipo']) ? intval($data['es_equipo']) : 0;
        $activo    = isset($data['activo']) ? intval($data['activo']) : 1;

        $stmt = product_prepare($conn, "CALL sp_Producto_Actualizar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Producto_Actualizar: {$stmt->error}");
        }

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

        return true;

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
        return false;
    }
}

/**
 * Desactivar (soft delete) producto mediante procedimiento almacenado.
 */
function deleteProduct($id) {
    $id = intval($id);
    $conn = null;

    try {
        $conn = OpenConnection();
        $stmt = product_prepare($conn, "CALL sp_Producto_Eliminar(?)");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Producto_Eliminar: {$stmt->error}");
        }

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

        return true;

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
        return false;
    }
}

/**
 * Buscar productos por término (nombre, descripcion, proveedor, categoría) via SP.
 */
function searchProducts($term) {
    $out = [];
    $conn = null;

    try {
        $conn = OpenConnection();
        $t    = '%' . $term . '%';
        $stmt = product_prepare($conn, "CALL sp_Productos_Buscar(?)");
        $stmt->bind_param("s", $t);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Productos_Buscar: {$stmt->error}");
        }

        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $row['precio']    = (float)($row['precio'] ?? 0);
            $row['stock']     = (int)($row['stock'] ?? 0);
            $row['es_equipo'] = (int)($row['es_equipo'] ?? 0);
            $row['imagen']    = normalizeProductImage($row['imagen'] ?? '', $row['nombre'] ?? '');
            $out[] = $row;
        }

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
    }

    return $out;
}

/**
 * Obtener productos por categoría via procedimiento almacenado.
 */
function getProductsByCategory($cat) {
    $out = [];
    $conn = null;

    try {
        $conn = OpenConnection();
        $stmt = product_prepare($conn, "CALL sp_Productos_PorCategoria(?)");
        $stmt->bind_param("s", $cat);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed sp_Productos_PorCategoria: {$stmt->error}");
        }

        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $row['precio']    = (float)($row['precio'] ?? 0);
            $row['stock']     = (int)($row['stock'] ?? 0);
            $row['es_equipo'] = (int)($row['es_equipo'] ?? 0);
            $row['imagen']    = normalizeProductImage($row['imagen'] ?? '', $row['nombre'] ?? '');
            $out[] = $row;
        }

        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
    }

    return $out;
}

/**
 * Obtener únicamente productos que son equipos via SP.
 */
function getEquipmentProducts() {
    $out = [];
    $conn = null;

    try {
        $conn = OpenConnection();
        $res  = $conn->query("CALL sp_Productos_Equipos()");
        if (!$res) {
            throw new Exception("Query failed sp_Productos_Equipos: {$conn->error}");
        }

        while ($row = $res->fetch_assoc()) {
            $row['precio']    = (float)($row['precio'] ?? 0);
            $row['stock']     = (int)($row['stock'] ?? 0);
            $row['es_equipo'] = (int)($row['es_equipo'] ?? 0);
            $row['imagen']    = normalizeProductImage($row['imagen'] ?? '', $row['nombre'] ?? '');
            $out[] = $row;
        }

        while ($conn->more_results() && $conn->next_result()) { /* flush */ }
        CloseConnection($conn);

    } catch (Throwable $e) {
        product_log_error($e);
        if ($conn) {
            try { while ($conn->more_results() && $conn->next_result()) {} } catch (Throwable $t) {}
            try { CloseConnection($conn); } catch (Throwable $t) {}
        }
    }

    return $out;
}
?>
