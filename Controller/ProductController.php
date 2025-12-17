<?php

header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../Model/ProductModel.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

/**
 * FIX PHP < 8: str_starts_with no existe
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

/**
 * Retorna la base del proyecto, ejemplo:
 * /ProyectoAmbienteWebCliente/ProyectoAmbienteWebG1
 */
function projectBaseUrl(): string
{
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // .../Controller
    $base = preg_replace('#/Controller$#', '', $dir);
    return rtrim($base, '/');
}

/**
 * Convierte "imagenes/xxx.jpg" -> "/Proyecto.../imagenes/xxx.jpg"
 * Mantiene intactas URLs externas (http/https) y rutas absolutas (/...)
 */
function normalizeImagePath(?string $img): string
{
    $img = trim((string)$img);
    if ($img === '') return '';

    if (preg_match('#^https?://#i', $img)) return $img;   // externa
    if (str_starts_with($img, '/')) return $img;          // absoluta

    return projectBaseUrl() . '/' . ltrim($img, '/');     // relativa -> absoluta desde raíz del proyecto
}

try {

    switch ($action) {

        case 'list':
            $products = getAllProducts();

            if (is_array($products)) {
                foreach ($products as &$p) {
                    if (is_array($p)) {
                        $p['imagen'] = normalizeImagePath($p['imagen'] ?? '');
                    }
                }
                unset($p);
            }

            echo json_encode(['success' => true, 'data' => $products ?? []]);
            break;

        case 'view':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $p = getProductById($id);
            if (is_array($p)) {
                $p['imagen'] = normalizeImagePath($p['imagen'] ?? '');
            }

            echo json_encode(['success' => true, 'data' => $p]);
            break;

        case 'search':
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            if ($q === '') {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }

            $res = searchProducts($q);
            if (is_array($res)) {
                foreach ($res as &$p) {
                    if (is_array($p)) {
                        $p['imagen'] = normalizeImagePath($p['imagen'] ?? '');
                    }
                }
                unset($p);
            }

            echo json_encode(['success' => true, 'data' => $res ?? []]);
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Usar POST");
            }

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

            if (isset($_FILES['imagenFile']) && is_array($_FILES['imagenFile']) && $_FILES['imagenFile']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['imagenFile']['tmp_name'];
                $origName = $_FILES['imagenFile']['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];

                if (in_array($ext, $allowed, true)) {
                    $newName = uniqid('prod_', true) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/imagenes/';

                    if (!is_dir($destDir)) mkdir($destDir, 0775, true);

                    if (move_uploaded_file($tmpName, $destDir . $newName)) {
                        $data['imagen'] = 'imagenes/' . $newName;
                    }
                }
            }

            if (trim($data['nombre']) === '') throw new Exception("Nombre requerido");

            $id = createProduct($data);
            if ($id === false) throw new Exception("No se pudo crear el producto");

            echo json_encode([
                'success' => true,
                'id' => $id,
                'imagen' => normalizeImagePath($data['imagen'] ?? '')
            ]);
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Usar POST");
            }

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

            if (isset($_FILES['imagenFile']) && is_array($_FILES['imagenFile']) && $_FILES['imagenFile']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['imagenFile']['tmp_name'];
                $origName = $_FILES['imagenFile']['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];

                if (in_array($ext, $allowed, true)) {
                    $newName = uniqid('prod_', true) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/imagenes/';

                    if (!is_dir($destDir)) mkdir($destDir, 0775, true);

                    if (move_uploaded_file($tmpName, $destDir . $newName)) {
                        $data['imagen'] = 'imagenes/' . $newName;
                    }
                }
            }

            $ok = updateProduct($id, $data);
            if (!$ok) throw new Exception("No se pudo actualizar");

            echo json_encode(['success' => true, 'imagen' => normalizeImagePath($data['imagen'] ?? '')]);
            break;

        case 'delete':
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            // AHORA deleteProduct devuelve array con mensaje
            $res = deleteProduct($id);

            if (!is_array($res) || empty($res['success'])) {
                throw new Exception($res['message'] ?? "No se pudo eliminar");
            }

            echo json_encode([
                'success' => true,
                'mode' => $res['mode'] ?? 'none',
                'message' => $res['message'] ?? 'OK'
            ]);
            break;

        default:
            throw new Exception("Acción no soportada");
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
