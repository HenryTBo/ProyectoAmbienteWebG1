<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Seguridad: solo admin
$perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
if ($perfil != "1") {
    header("Location: Inicio.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php showCss(); ?>
    <link rel="stylesheet" href="../css/pedidos.css">
</head>
<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
    <?php showSideBar(); ?>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">

            <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
                <div>
                    <h2 class="page-title m-0">Gestión de pedidos</h2>
                    <div class="text-muted">Administrá el estado y seguimiento de pedidos</div>
                </div>
                <button id="btnRefresh" class="btn btn-outline-primary">
                    <i class="fas fa-rotate me-1"></i> Actualizar
                </button>
            </div>

            <div id="ordersError" class="alert alert-danger d-none"></div>

            <div class="card orders-card">
                <div class="card-header orders-header">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div class="fw-bold">
                            <i class="fas fa-list-check me-2"></i> Pedidos
                        </div>
                        <input id="searchInput" class="form-control orders-search" placeholder="Buscar por #pedido / estado / entrega / dirección">
                    </div>
                </div>
                <div class="card-body">
                    <div id="ordersLoading" class="text-muted">Cargando...</div>

                    <div class="table-responsive d-none" id="ordersTableWrap">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Entrega</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="ordersTbody"></tbody>
                        </table>
                    </div>

                    <div id="ordersEmpty" class="text-muted d-none">No hay pedidos para mostrar.</div>
                </div>
            </div>

        </main>
        <?php showFooter(); ?>
    </div>
</div>

<?php showJs(); ?>

<script>
(function(){
  const elErr = document.getElementById('ordersError');
  const elLoading = document.getElementById('ordersLoading');
  const elWrap = document.getElementById('ordersTableWrap');
  const elEmpty = document.getElementById('ordersEmpty');
  const tbody = document.getElementById('ordersTbody');
  const search = document.getElementById('searchInput');

  let all = [];
  let q = '';

  const money = (n)=> '₡' + Number(n||0).toLocaleString('es-CR',{minimumFractionDigits:2, maximumFractionDigits:2});

  function showError(msg){
    elErr.textContent = msg;
    elErr.classList.remove('d-none');
  }
  function clearError(){
    elErr.classList.add('d-none');
    elErr.textContent = '';
  }

  async function getJson(url){
    const r = await fetch(url, { cache: 'no-store' });
    const t = await r.text();
    try { return JSON.parse(t); }
    catch(e){ throw new Error('Respuesta inválida: ' + t); }
  }

  function applyFilter(list){
    const qq = (q||'').trim().toLowerCase();
    if(!qq) return list;
    return list.filter(o=>{
      return String(o.id||'').includes(qq)
        || String(o.estado||'').toLowerCase().includes(qq)
        || String(o.entrega_tipo||'').toLowerCase().includes(qq)
        || String(o.direccion||'').toLowerCase().includes(qq);
    });
  }

  function render(){
    const list = applyFilter(all);

    elLoading.classList.add('d-none');
    clearError();

    if(list.length === 0){
      elWrap.classList.add('d-none');
      elEmpty.classList.remove('d-none');
      return;
    }

    elEmpty.classList.add('d-none');
    elWrap.classList.remove('d-none');

    tbody.innerHTML = '';
    list.forEach(o=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><strong>#${o.id}</strong></td>
        <td>${o.fecha || ''}</td>
        <td>${o.entrega_tipo || ''}</td>
        <td class="text-truncate" style="max-width:240px;">${o.direccion || '-'}</td>
        <td><span class="badge bg-secondary">${o.estado || ''}</span></td>
        <td class="text-end">${money(o.total)}</td>
        <td class="text-end">
          <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-primary btn-sm" data-set="${o.id}" data-state="En proceso">En proceso</button>
            <button class="btn btn-outline-success btn-sm" data-set="${o.id}" data-state="Entregado">Entregado</button>
            <button class="btn btn-outline-danger btn-sm" data-set="${o.id}" data-state="Cancelado">Cancelado</button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('[data-set]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-set');
        const estado = btn.getAttribute('data-state');
        await setEstado(id, estado);
      });
    });
  }

  async function load(){
    elLoading.classList.remove('d-none');
    elWrap.classList.add('d-none');
    elEmpty.classList.add('d-none');
    clearError();

    try{
      // ✅ ahora coincide con tu controlador real
      const json = await getJson('../../Controller/OrderController.php?action=listAdmin');

      if(!json.success) throw new Error(json.message || 'No se pudieron cargar los pedidos');

      // ✅ compatibilidad: puede venir en data o en orders
      all = json.data || json.orders || [];
      render();
    }catch(err){
      elLoading.classList.add('d-none');
      showError(err.message || 'Error cargando pedidos');
      console.error(err);
    }
  }

  async function setEstado(id, estado){
    try{
      const fd = new FormData();
      fd.append('pedido_id', id);
      fd.append('estado', estado);

      // ✅ ahora coincide con tu controlador real
      const r = await fetch('../../Controller/OrderController.php?action=updateStatus', { method:'POST', body: fd });
      const t = await r.text();
      let json;
      try { json = JSON.parse(t); } catch(e){ throw new Error('Respuesta inválida: ' + t); }

      if(!json.success) throw new Error(json.message || 'No se pudo actualizar');
      await load();
    }catch(err){
      showError(err.message || 'No se pudo actualizar');
      console.error(err);
    }
  }

  document.getElementById('btnRefresh').addEventListener('click', load);

  if(search){
    search.addEventListener('input', ()=>{
      q = search.value || '';
      render();
    });
  }

  document.addEventListener('DOMContentLoaded', load);
})();
</script>
</body>
</html>
