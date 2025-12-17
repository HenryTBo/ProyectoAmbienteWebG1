<?php
// Alias de compatibilidad: si alguna vista aún apunta a productoDetalle.php,
// redirigimos a la nueva vista verProducto.php.
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
header("Location: verProducto.php?id=" . $id);
exit;
