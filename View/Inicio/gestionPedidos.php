<?php
// View/Inicio/gestionPedidos.php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}

$perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
$esAdmin = ($perfil == "1");
if (!$esAdmin) {
    header("Location: Principal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php showCss(); ?>
  <link rel="stylesheet" href="../../assets/css/pedidos.css">
</head>
<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
  <?php showSideBar(); ?>

  <div id="layoutSidenav_content">
    <main class="container-fluid px-4">
      <div class="jj-header mt-4 mb-3">
        <div>
          <h2 class="jj-title m-0">Gestión de pedidos</h2>
          <div class="jj-subtitle">Administrador — asignar chofer y actualizar estado</div>
        </div>
        <div class="jj-actions">
          <a class="btn btn-outline-secondary" href="PrincipalAdmin.php">Panel</a>
          <button id="btnRefrescar" class="btn btn-primary" type="button">Actualizar</button>
        </div>
      </div>

      <div class="jj-card">
        <div class="jj-card-head">
          <div class="jj-card-head-title">Pedidos</div>
          <input id="searchBox" class="form-control jj-search" placeholder="Buscar por cliente / estado...">
        </div>

        <div class="jj-card-body">
          <div class="table-responsive">
            <table class="table align-middle jj-table" id="ordersTable">
              <thead>
                <tr>
                  <th>Pedido</th>
                  <th>Cliente</th>
                  <th>Fecha</th>
                  <th>Entrega</th>
                  <th>Dirección</th>
                  <th>Chofer</th>
                  <th>Estado</th>
                  <th>Total</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="ordersBody">
                <tr><td colspan="9" class="text-muted">Cargando pedidos...</td></tr>
              </tbody>
            </table>
          </div>
          <div id="msg" class="jj-msg d-none"></div>
        </div>
      </div>
    </main>

    <?php showFooter(); ?>
  </div>
</div>

<?php showJs(); ?>

<!-- Modal Detalle -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content jj-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="detailTitle">Detalle del pedido</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailBody">Cargando...</div>
    </div>
  </div>
</div>

<script>
(function(){
  const fmtCRC = (n) => "₡" + Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  let allOrders = [];
  let allDrivers = [];

  function showMsg(text, ok){
    const el = document.getElementById('msg');
    el.classList.remove('d-none','ok','err');
    el.classList.add(ok ? 'ok' : 'err');
    el.textContent = text;
  }

  function fetchData(){
    fetch('../../Controller/OrderController.php?action=listAdmin')
      .then(r=>r.json())
      .then(json=>{
        if(!json.success) throw new Error(json.message || 'No se pudieron cargar');
        allOrders = json.orders || [];
        allDrivers = json.drivers || [];
        render();
      })
      .catch(err=>{
        document.getElementById('ordersBody').innerHTML =
          '<tr><td colspan="9" class="text-danger">No se pudieron cargar los pedidos.</td></tr>';
        console.error(err);
      });
  }

  function driversSelectHtml(selectedId){
    const opts = allDrivers.map(d=>{
      const id = Number(d.id);
      const sel = (id === Number(selectedId)) ? 'selected' : '';
      return `<option value="${id}" ${sel}>${d.nombre}</option>`;
    }).join('');
    return `<select class="form-select form-select-sm" data-driver-select>${opts}</select>`;
  }

  function render(){
    const q = (document.getElementById('searchBox').value || '').toLowerCase().trim();
    const rows = allOrders.filter(o=>{
      if(!q) return true;
      const cliente = String(o.nombreUsuario||'').toLowerCase();
      const estado = String(o.estado||'').toLowerCase();
      return cliente.includes(q) || estado.includes(q);
    });

    const body = document.getElementById('ordersBody');
    if(rows.length === 0){
      body.innerHTML = '<tr><td colspan="9" class="text-muted">No hay pedidos para mostrar.</td></tr>';
      return;
    }

    body.innerHTML = rows.map(o=>`
      <tr data-row="${o.id}">
        <td><strong>#${o.id}</strong></td>
        <td>${o.nombreUsuario || ''}</td>
        <td>${o.fecha || ''}</td>
        <td>${o.entrega_tipo || ''}</td>
        <td>${o.direccion || ''}</td>

        <td>
          ${driversSelectHtml(o.id_conductor)}
          <button class="btn btn-sm btn-outline-primary mt-2" data-save-driver>Guardar chofer</button>
        </td>

        <td>
          <select class="form-select form-select-sm" data-status>
            ${['Pendiente','En proceso','Finalizado','Cancelado'].map(st=>{
              const sel = (String(o.estado||'') === st) ? 'selected' : '';
              return `<option ${sel} value="${st}">${st}</option>`;
            }).join('')}
          </select>
          <button class="btn btn-sm btn-outline-primary mt-2" data-save-status>Guardar estado</button>
        </td>

        <td>${fmtCRC(o.total)}</td>

        <td>
          <button class="btn btn-sm btn-secondary" data-detail>Detalle</button>
        </td>
      </tr>
    `).join('');

    // bind
    body.querySelectorAll('[data-save-driver]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const tr = btn.closest('tr');
        const pedidoId = tr.getAttribute('data-row');
        const driverId = tr.querySelector('[data-driver-select]').value;

        const fd = new FormData();
        fd.append('pedido_id', pedidoId);
        fd.append('driver_id', driverId);

        fetch('../../Controller/OrderController.php?action=assignDriver', {method:'POST', body: fd})
          .then(r=>r.json())
          .then(j=>{
            if(!j.success) throw new Error(j.message || 'No se pudo guardar chofer');
            showMsg('Chofer actualizado.', true);
            fetchData();
          })
          .catch(e=>showMsg(e.message, false));
      });
    });

    body.querySelectorAll('[data-save-status]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const tr = btn.closest('tr');
        const pedidoId = tr.getAttribute('data-row');
        const estado = tr.querySelector('[data-status]').value;

        const fd = new FormData();
        fd.append('pedido_id', pedidoId);
        fd.append('estado', estado);

        fetch('../../Controller/OrderController.php?action=updateStatus', {method:'POST', body: fd})
          .then(r=>r.json())
          .then(j=>{
            if(!j.success) throw new Error(j.message || 'No se pudo guardar estado');
            showMsg('Estado actualizado.', true);
            fetchData();
          })
          .catch(e=>showMsg(e.message, false));
      });
    });

    body.querySelectorAll('[data-detail]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const tr = btn.closest('tr');
        const pedidoId = tr.getAttribute('data-row');

        const modalEl = document.getElementById('detailModal');
        const modal = new bootstrap.Modal(modalEl);
        document.getElementById('detailBody').innerHTML = 'Cargando...';
        modal.show();

        fetch('../../Controller/OrderController.php?action=details&pedido_id='+encodeURIComponent(pedidoId))
          .then(r=>r.json())
          .then(j=>{
            if(!j.success) throw new Error(j.message || 'No se pudo cargar detalle');
            const header = j.data.header || {};
            const items = j.data.items || [];

            document.getElementById('detailTitle').textContent = 'Detalle del pedido #' + header.id;

            const html = `
              <div class="mb-2"><strong>Cliente:</strong> ${header.nombreUsuario || ''}</div>
              <div class="mb-2"><strong>Fecha:</strong> ${header.fecha || ''}</div>
              <div class="mb-2"><strong>Estado:</strong> ${header.estado || ''}</div>
              <hr>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr></thead>
                  <tbody>
                    ${items.map(it=>`
                      <tr>
                        <td>${it.nombreProducto || ''}</td>
                        <td>${it.cantidad}</td>
                        <td>${fmtCRC(it.precio)}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            `;
            document.getElementById('detailBody').innerHTML = html;
          })
          .catch(e=>{
            document.getElementById('detailBody').innerHTML = '<div class="text-danger">'+e.message+'</div>';
          });
      });
    });
  }

  document.getElementById('btnRefrescar').addEventListener('click', fetchData);
  document.getElementById('searchBox').addEventListener('input', render);

  document.addEventListener('DOMContentLoaded', fetchData);
})();
</script>
</body>
</html>
