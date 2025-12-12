<?php

header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../Model/ProductModel.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

try {

    switch ($action) {

        /* --------------------------------------------------------
           LISTAR PRODUCTOS
        ---------------------------------------------------------*/
        case 'list':
            $products = getAllProducts();
            echo json_encode(['success' => true, 'data' => $products]);
            break;


        /* --------------------------------------------------------
           VER DETALLE
        ---------------------------------------------------------*/
        case 'view':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $p = getProductById($id);
            echo json_encode(['success' => true, 'data' => $p]);
            break;


        /* --------------------------------------------------------
           BUSCAR
        ---------------------------------------------------------*/
        case 'search':
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            if ($q === '') {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }

            $res = searchProducts($q);
            echo json_encode(['success' => true, 'data' => $res]);
            break;


        /* --------------------------------------------------------
           CREAR PRODUCTO
        ---------------------------------------------------------*/
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Usar POST");

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
                'activo' => 1
            ];

            // Si se subió una imagen de archivo, guardarla en la carpeta imagenes y utilizarla como imagen
            if (isset($_FILES['imagenFile']) && is_array($_FILES['imagenFile']) && $_FILES['imagenFile']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['imagenFile']['tmp_name'];
                $origName = $_FILES['imagenFile']['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $newName = uniqid('prod_', true) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/imagenes/';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0775, true);
                    }
                    if (move_uploaded_file($tmpName, $destDir . $newName)) {
                        $data['imagen'] = 'imagenes/' . $newName;
                    }
                }
            }

            if (trim($data['nombre']) === '')
                throw new Exception("Nombre requerido");

            $id = createProduct($data);
            if ($id === false) throw new Exception("No se pudo crear el producto");

            echo json_encode(['success' => true, 'id' => $id]);
            break;


        /* --------------------------------------------------------
           ACTUALIZAR PRODUCTO
        ---------------------------------------------------------*/
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Usar POST");

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

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

            // Si se cargó una nueva imagen de archivo, subirla y reemplazar la existente
            if (isset($_FILES['imagenFile']) && is_array($_FILES['imagenFile']) && $_FILES['imagenFile']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['imagenFile']['tmp_name'];
                $origName = $_FILES['imagenFile']['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $newName = uniqid('prod_', true) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/imagenes/';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0775, true);
                    }
                    if (move_uploaded_file($tmpName, $destDir . $newName)) {
                        $data['imagen'] = 'imagenes/' . $newName;
                    }
                }
            }

            $ok = updateProduct($id, $data);
            if (!$ok) throw new Exception("No se pudo actualizar");

            echo json_encode(['success' => true]);
            break;


        /* --------------------------------------------------------
           ELIMINAR PRODUCTO (SOFT DELETE)
        ---------------------------------------------------------*/
        case 'delete':
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $ok = deleteProduct($id);
            if (!$ok) throw new Exception("No se pudo eliminar");

            echo json_encode(['success' => true]);
            break;


        /* --------------------------------------------------------
           ACCIÓN NO SOPORTADA
        ---------------------------------------------------------*/
        default:
            throw new Exception("Acción no soportada");
    }

} catch (Exception $e) {

    // Registrar error si existe la función SaveError()
    if (function_exists('SaveError')) {
        try { SaveError($e); } catch (Exception $ex) {}
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

