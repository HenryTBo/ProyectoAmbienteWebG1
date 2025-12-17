<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';
require_once __DIR__ . '/../../Model/ConexionModel.php';

// Seguridad: solo admin
$perfil = $_SESSION['ConsecutivoPerfil'] ?? ($_SESSION['User']['ConsecutivoPerfil'] ?? 2);
if ((int)$perfil !== 1) {
    header("Location: Principal.php");
    exit;
}

$nombre = $_SESSION['Nombre'] ?? ($_SESSION['User']['Nombre'] ?? 'Administrador');

/**
 * Ejecuta un SP que retorna una sola fila con columna "total".
 * Si falla (SP no existe, etc.), devuelve 0 y no rompe el panel.
 */
function sp_count_total($spName) {
    $cn = OpenConnection();
    $total = 0;

    try {
        $result = $cn->query("CALL {$spName}()");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = (int)($row['total'] ?? 0);
            $result->free();
        }
        while ($cn->more_results() && $cn->next_result()) {}
    } catch (Exception $e) {
        if (function_exists('SaveError')) { try { SaveError($e); } catch (Exception $ex) {} }
        $total = 0;
    }

    CloseConnection($cn);
    return $total;
}

// Contadores (requieren que existan estos SP; si no existen, el panel no se cae, solo muestra 0)
$productosActivos = sp_count_total('sp_Productos_ContarActivos');
$usuariosTotal    = sp_count_total('sp_Usuarios_Contar');
$empleadosActivos = sp_count_total('sp_Empleados_ContarActivos');
$pedidosTotal     = sp_count_total('sp_Pedidos_Contar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php showCss(); ?>
  <style>
    .dash-card{
      border:0;
      border-radius:18px;
      box-shadow: 0 14px 35px rgba(0,0,0,.10);
    }
    .dash-card .label{ color:#6c757d; font-weight:700; }
    .dash-card .value{ font-size:34px; font-weight:900; color:#b10d0d; }
    .mod-card{
      border:0;
      border-radius:18px;
      box-shadow: 0 14px 35px rgba(0,0,0,.10);
      overflow:hidden;
    }
    .mod-card h5{ font-weight:900; color:#0c2c3c; }
    .btn-soft{
      border-radius:14px !important;
      font-weight:800 !important;
    }
  </style>
</head>

<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
  <?php showSideBar(); ?>

  <div id="layoutSidenav_content">
    <main class="container-fluid px-4 mt-4">

      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <h2 class="fw-bold mb-1">Panel Administrador</h2>
          <div class="text-muted">Bienvenido, <?php echo htmlspecialchars($nombre); ?></div>
        </div>

        <a href="productos.php" class="btn btn-danger btn-soft">
          <i class="fas fa-box me-1"></i> Ir a Productos
        </a>
      </div>

      <!-- Resumen -->
      <div class="row mt-4">
        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card dash-card">
            <div class="card-body">
              <div class="label">Productos Registrados</div>
              <div class="value"><?php echo (int)$productosActivos; ?></div>
              <div class="text-muted">Activos en el inventario</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card dash-card">
            <div class="card-body">
              <div class="label">Cuentas</div>
              <div class="value"><?php echo (int)$usuariosTotal; ?></div>
              <div class="text-muted">Usuarios registrados</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card dash-card">
            <div class="card-body">
              <div class="label">Empleados</div>
              <div class="value"><?php echo (int)$empleadosActivos; ?></div>
              <div class="text-muted">Activos en planilla</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card dash-card">
            <div class="card-body">
              <div class="label">Pedidos</div>
              <div class="value"><?php echo (int)$pedidosTotal; ?></div>
              <div class="text-muted">Pedidos totales</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Módulos -->
      <div class="row mt-2">
        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card mod-card">
            <div class="card-body">
              <h5>Gestión de Productos</h5>
              <p class="text-muted mb-3">Administra todo el inventario, equipos, licores y más.</p>
              <a href="productos.php" class="btn btn-outline-danger btn-soft">Ir al módulo</a>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card mod-card">
            <div class="card-body">
              <h5>Gestión de Cuentas</h5>
              <p class="text-muted mb-3">Administrá usuarios y perfiles del sistema.</p>
              <a href="usuarios.php" class="btn btn-outline-danger btn-soft">Ir al módulo</a>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card mod-card">
            <div class="card-body">
              <h5>Gestión de Empleados</h5>
              <p class="text-muted mb-3">Gestioná la planilla de colaboradores.</p>
              <a href="empleados.php" class="btn btn-outline-danger btn-soft">Ir al módulo</a>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
          <div class="card mod-card">
            <div class="card-body">
              <h5>Gestión de Pedidos</h5>
              <p class="text-muted mb-3">Revisá, asigná conductor y actualizá estados.</p>
              <a href="gestionPedidos.php" class="btn btn-outline-danger btn-soft">Ir al módulo</a>
            </div>
          </div>
        </div>
      </div>

    </main>

    <?php showFooter(); ?>
  </div>
</div>

<?php showJs(); ?>
</body>
</html>
