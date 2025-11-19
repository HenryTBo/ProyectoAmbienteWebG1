<?php
// scripts/map_images.php
// Ejecutar desde navegador: http://localhost/ProyectoAmbienteWebG1/scripts/map_images.php
// Asegúrate de que ConexionModel.php funciona y que tienes permisos para UPDATE.

include_once __DIR__ . '/../Model/ConexionModel.php';

try {
    $conn = OpenConnection();

    $res = $conn->query("SELECT id, nombre, imagen FROM productos");
    $updated = 0;
    $checked = 0;
    while ($r = $res->fetch_assoc()) {
        $checked++;
        $id = intval($r['id']);
        $safe = preg_replace('/[^a-z0-9_\\.]/i', '_', strtolower($r['nombre']));
        $candidateJpg = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.jpg';
        $candidatePng = '/ProyectoAmbienteWebG1/public/images/' . $safe . '.png';
        $chosen = '';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidateJpg)) {
            $chosen = $candidateJpg;
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . $candidatePng)) {
            $chosen = $candidatePng;
        }
        if ($chosen) {
            $stmt = $conn->prepare("UPDATE productos SET imagen = ? WHERE id = ?");
            $stmt->bind_param('si', $chosen, $id);
            $stmt->execute();
            if ($stmt->affected_rows) $updated++;
            $stmt->close();
        }
    }
    CloseConnection($conn);
    echo "Revisados: $checked. Imágenes asignadas: $updated.";
} catch (Exception $e) {
    if (function_exists('SaveError')) { try { SaveError($e); } catch(Exception $ex) {} }
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
