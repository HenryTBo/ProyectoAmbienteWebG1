<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../Model/ProductModel.php';

/** Solo admin para mutaciones */
function isAdmin(): bool {
    $perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
    return ((string)$perfil === "1");
}
function requireAdmin(): void {
    if (!isAdmin()) {
        throw new Exception("Acceso denegado (solo administrador).");
    }
}

$action = $_REQUEST['action'] ?? 'list';

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
 * Convierte rutas relativas en rutas absolutas del proyecto.
 * - Mantiene intactas URLs externas (http/https) y rutas absolutas (/...)
 */
function normalizeImagePath(?string $img): string
{
    $img = trim((string)$img);
    if ($img === '') return '';

    if (preg_match('#^https?://#i', $img)) return $img;   // externa
    if (str_starts_with($img, '/')) return $img;          // absoluta

    return projectBaseUrl() . '/' . ltrim($img, '/');     // relativa -> absoluta desde raíz del proyecto
}

/**
 * Manejo seguro de upload. Devuelve ruta relativa tipo "imagenes/xxx.jpg" o ''.
 */
function handleImageUpload(string $fieldName = 'imagenFile'): string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) return '';
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return '';

    $tmpName  = $_FILES[$fieldName]['tmp_name'];
    $origName = $_FILES[$fieldName]['name'];

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed, true)) {
        throw new Exception("Formato de imagen no permitido. Use: jpg, jpeg, png, gif, webp.");
    }

    $newName = uniqid('prod_', true) . '.' . $ext;
    $destDir = dirname(__DIR__) . '/imagenes/';

    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0775, true)) {
            throw new Exception("No se pudo crear la carpeta /imagenes");
        }
    }

    if (!move_uploaded_file($tmpName, $destDir . $newName)) {
        throw new Exception("No se pudo guardar la imagen en el servidor.");
    }

    return 'imagenes/' . $newName;
}

try {

    switch ($action) {

        case 'list': {
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
        }

        case 'view': {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $p = getProductById($id);
            if (is_array($p)) {
                $p['imagen'] = normalizeImagePath($p['imagen'] ?? '');
            }

            echo json_encode(['success' => true, 'data' => $p]);
            break;
        }

        case 'search': {
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
        }

        case 'create': {
            requireAdmin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Usar POST");

            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'precio' => isset($_POST['precio']) ? floatval($_POST['precio']) : 0,
                'stock' => isset($_POST['stock']) ? intval($_POST['stock']) : 0,
                'unidad' => trim($_POST['unidad'] ?? ''),
                'proveedor' => trim($_POST['proveedor'] ?? ''),
                'imagen' => trim($_POST['imagen'] ?? ''),
                'es_equipo' => isset($_POST['es_equipo']) ? intval($_POST['es_equipo']) : 0,
                'activo' => 1
            ];

            if ($data['nombre'] === '') throw new Exception("Nombre requerido");
            if ($data['categoria'] === '') throw new Exception("Categoría requerida");

            // Upload reemplaza imagen si viene archivo
            $uploaded = handleImageUpload('imagenFile');
            if ($uploaded !== '') {
                $data['imagen'] = $uploaded;
            }

            $id = createProduct($data);
            if ($id === false) throw new Exception("No se pudo crear el producto (revisar SP/BD).");

            echo json_encode([
                'success' => true,
                'id' => $id,
                'imagen' => normalizeImagePath($data['imagen'] ?? '')
            ]);
            break;
        }

        case 'update': {
            requireAdmin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Usar POST");

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'precio' => isset($_POST['precio']) ? floatval($_POST['precio']) : 0,
                'stock' => isset($_POST['stock']) ? intval($_POST['stock']) : 0,
                'unidad' => trim($_POST['unidad'] ?? ''),
                'proveedor' => trim($_POST['proveedor'] ?? ''),
                'imagen' => trim($_POST['imagen'] ?? ''),
                'es_equipo' => isset($_POST['es_equipo']) ? intval($_POST['es_equipo']) : 0,
                'activo' => isset($_POST['activo']) ? intval($_POST['activo']) : 1
            ];

            if ($data['nombre'] === '') throw new Exception("Nombre requerido");
            if ($data['categoria'] === '') throw new Exception("Categoría requerida");

            // Upload reemplaza imagen si viene archivo
            $uploaded = handleImageUpload('imagenFile');
            if ($uploaded !== '') {
                $data['imagen'] = $uploaded;
            }

            $ok = updateProduct($id, $data);
            if (!$ok) throw new Exception("No se pudo actualizar el producto (revisar SP/BD).");

            echo json_encode([
                'success' => true,
                'imagen' => normalizeImagePath($data['imagen'] ?? '')
            ]);
            break;
        }

        case 'delete': {
            requireAdmin();

            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            if ($id <= 0) throw new Exception("ID inválido");

            $ok = deleteProduct($id);
            if (!$ok) throw new Exception("No se pudo eliminar el producto (revisar SP/BD).");

            echo json_encode(['success' => true]);
            break;
        }

        default:
            throw new Exception("Acción no soportada: {$action}");
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
