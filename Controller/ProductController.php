<?php


header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../Model/ProductModel.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {
    switch ($action) {
        case 'list':
            $products = getAllProducts();
            echo json_encode(['success'=>true, 'data'=>$products]);
            break;

        case 'view':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id<=0) throw new Exception("ID inválido");
            $p = getProductById($id);
            echo json_encode(['success'=>true, 'data'=>$p]);
            break;

        case 'search':
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            if ($q === '') {
                echo json_encode(['success'=>true, 'data'=>[]]);
                break;
            }
            $res = searchProducts($q);
            echo json_encode(['success'=>true, 'data'=>$res]);
            break;

        case 'create':
            // Se espera método POST con campos
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Usar POST");
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'categoria' => $_POST['categoria'] ?? '',
                'precio' => isset($_POST['precio']) ? floatval($_POST['precio']) : 0,
                'stock' => isset($_POST['stock']) ? intval($_POST['stock']) : 0,
                'unidad' => $_POST['unidad'] ?? '',
                'proveedor' => $_POST['proveedor'] ?? '',
                'imagen' => $_POST['imagen'] ?? '',
                'es_equipo' => isset($_POST['es_equipo']) ? intval($_POST['es_equipo']) : 0,
                'activo' => isset($_POST['activo']) ? intval($_POST['activo']) : 1
            ];
            if (trim($data['nombre']) === '') throw new Exception("Nombre requerido");
            $id = createProduct($data);
            if ($id === false) throw new Exception("Error al crear producto");
            echo json_encode(['success'=>true, 'id'=>$id]);
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Usar POST");
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id<=0) throw new Exception("ID inválido");
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'categoria' => $_POST['categoria'] ?? '',
                'precio' => isset($_POST['precio']) ? floatval($_POST['precio']) : 0,
                'stock' => isset($_POST['stock']) ? intval($_POST['stock']) : 0,
                'unidad' => $_POST['unidad'] ?? '',
                'proveedor' => $_POST['proveedor'] ?? '',
                'imagen' => $_POST['imagen'] ?? '',
                'es_equipo' => isset($_POST['es_equipo']) ? intval($_POST['es_equipo']) : 0,
                'activo' => isset($_POST['activo']) ? intval($_POST['activo']) : 1
            ];
            $ok = updateProduct($id, $data);
            if (!$ok) throw new Exception("No se pudo actualizar");
            echo json_encode(['success'=>true]);
            break;

        case 'delete':
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            if ($id<=0) throw new Exception("ID inválido");
            $ok = deleteProduct($id);
            if (!$ok) throw new Exception("No se pudo eliminar");
            echo json_encode(['success'=>true]);
            break;

        default:
            throw new Exception("Acción no soportada");
    }
} catch (Exception $e) {
    // Intentar registrar el error
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) { /* ignore */ }
    }
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=> $e->getMessage()]);
}


// guia para mi comandos de uso aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
// Controller/ProductController.php
// API / controlador sencillo para productos
// Rutas soportadas (GET/POST):
// ?action=list            -> lista todos los productos activos (GET)
// ?action=view&id=XX      -> detalle de producto (GET)
// ?action=search&q=term   -> buscar (GET)
// ?action=create          -> crear producto (POST) campos en body/form-data
// ?action=update&id=XX    -> actualizar producto (POST)
// ?action=delete&id=XX    -> eliminar (soft delete) (POST or GET)
// Responde JSON
// guia para mi comandos de uso aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa