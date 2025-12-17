<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php showCss(); ?>
  <link rel="stylesheet" href="../css/jj-store-modern.css">
</head>

<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
  <?php showSideBar(); ?>

  <div id="layoutSidenav_content">
    <main class="container-fluid px-4 page-shell">

      <div class="page-hero">
        <div class="d-flex flex-wrap align-items-start align-items-md-center justify-content-between gap-2">
          <div>
            <h1>Mis pedidos</h1>
            <p>Seguimiento de tus compras y estado del pedido</p>
          </div>

          <div class="d-flex gap-2">
            <a class="btn btn-outline-light btn-soft" href="productos.php">
              <i class="fas fa-store me-1"></i> Ir a productos
            </a>
            <button class="btn btn-primary btn-soft" id="btnRefresh" type="button">
              <i class="fas fa-rotate me-1"></i> Actualizar
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <input id="txtSearch" class="form-control search-pill" style="max-width:360px" placeholder="Buscar por estado / #pedido">
        </div>
      </div>

      <div class="card-soft">
        <div class="card-head">
          <div class="title">
            <i class="fas fa-clipboard-list"></i>
            <span>Historial</span>
          </div>
          <small style="opacity:.9; font-weight:900;">Últimos pedidos</small>
        </div>

        <div class="card-body">
          <div class="table-wrap">
            <table class="table-modern" aria-label="Tabla de pedidos">
              <thead>
                <tr>
                  <th>Pedido</th>
                  <th>Fecha</th>
                  <th>Entrega</th>
                  <th>Dirección</th>
                  <th>Estado</th>
                  <th class="text-end">Total</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodyMisPedidos">
                <tr><td colspan="7" class="text-muted">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>

    <?php showFooter(); ?>
  </div>
</div>

<?php showJs(); ?>

<!-- Modal Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px; overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(90deg, rgba(12,44,60,1) 0%, rgba(20,48,68,1) 70%, rgba(204,164,76,.14) 100%); color:#fff;">
        <h5 class="modal-title" style="font-weight:900;">Detalle del pedido</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="detalleContenido" class="text-muted">Cargando...</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary btn-soft" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const API = '../../Controller/OrderController.php';
  let orders = [];

  const $ = (id) => document.getElementById(id);

  function toast(msg){
    const t = document.createElement('div');
    t.className = 'toast-jj';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(()=>t.classList.add('visible'), 10);
    setTimeout(()=>{
      t.classList.remove('visible');
      setTimeout(()=>t.remove(), 220);
    }, 1400);
  }

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function moneyCRC(n){
    const num = Number(n || 0);
    return '₡' + num.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
  }

  function stateBadge(estadoRaw){
    const e = String(estadoRaw || '').toLowerCase();
    let cls = 'st-proceso';
    let label = estadoRaw || 'En proceso';

    if(e.includes('pend')) cls = 'st-pendiente';
    if(e.includes('entreg') || e.includes('final') || e.includes('complet')) cls = 'st-entregado';
    if(e.includes('cancel')) cls = 'st-cancelado';

    return `<span class="badge-state ${cls}"><i class="fas fa-circle" style="font-size:7px; opacity:.8;"></i>${escapeHtml(label)}</span>`;
  }

  function render(){
    const q = ($('txtSearch').value || '').trim().toLowerCase();
    const tbody = $('tbodyMisPedidos');

    const list = orders.filter(o=>{
      if(!q) return true;
      const txt = [o.id, o.estado, o.entrega_tipo, o.direccion].join(' ').toLowerCase();
      return txt.includes(q);
    });

    if(list.length === 0){
      tbody.innerHTML = `
        <tr><td colspan="7">
          <div class="empty">
            <h3>Sin pedidos</h3>
            <p>No encontramos pedidos para tu búsqueda.</p>
          </div>
        </td></tr>`;
      return;
    }

    tbody.innerHTML = list.map(o=>{
      const id = Number(o.id);
      return `
        <tr>
          <td class="mono"><strong>#${id}</strong></td>
          <td>${escapeHtml(o.fecha || '')}</td>
          <td>${escapeHtml(o.entrega_tipo || 'Tienda')}</td>
          <td title="${escapeHtml(o.direccion || '-')}" style="max-width:360px;">
            <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              ${escapeHtml(o.direccion || '-')}
            </div>
          </td>
          <td>${stateBadge(o.estado)}</td>
          <td class="text-end" style="font-weight:900;">${moneyCRC(o.total)}</td>
          <td class="text-end" style="min-width:140px;">
            <button class="btn btn-outline-secondary btn-sm btn-soft w-100" type="button" data-detail="${id}">
              <i class="fas fa-eye me-1"></i> Detalle
            </button>
          </td>
        </tr>
      `;
    }).join('');

    tbody.querySelectorAll('[data-detail]').forEach(btn=>{
      btn.addEventListener('click', ()=> openDetail(btn.getAttribute('data-detail')));
    });
  }

  function loadOrders(){
    return fetch(`${API}?action=my`)
      .then(r=>r.json())
      .then(j=>{
        if(!j.success) throw new Error(j.message || 'No se pudieron cargar tus pedidos');
        orders = j.data || [];
      });
  }

  function openDetail(orderId){
    const el = $('detalleContenido');
    el.innerHTML = 'Cargando...';

    fetch(`${API}?action=details&pedido_id=${encodeURIComponent(orderId)}`)
      .then(r=>r.json())
      .then(j=>{
        if(!j.success) throw new Error(j.message || 'No se pudo obtener detalle');

        const info = j.data?.order || j.order || null;
        const items = j.data?.items || j.items || [];

        let html = '';
        if(info){
          html += `
            <div class="mb-3">
              <div><strong>Pedido:</strong> #${escapeHtml(info.id)}</div>
              <div><strong>Fecha:</strong> ${escapeHtml(info.fecha || '')}</div>
              <div><strong>Entrega:</strong> ${escapeHtml(info.entrega_tipo || 'Tienda')}</div>
              <div><strong>Dirección:</strong> ${escapeHtml(info.direccion || '-')}</div>
              <div><strong>Estado:</strong> ${escapeHtml(info.estado || '')}</div>
              <div><strong>Total:</strong> ${moneyCRC(info.total)}</div>
            </div>
          `;
        }

        html += `
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                ${items.map(it=>{
                  const nombre = it.nombreProducto || it.nombre || '';
                  const qty = Number(it.cantidad || 0);
                  const precio = Number(it.precio || 0);
                  const sub = qty * precio;
                  return `
                    <tr>
                      <td>${escapeHtml(nombre)}</td>
                      <td>${qty}</td>
                      <td>${moneyCRC(precio)}</td>
                      <td><strong>${moneyCRC(sub)}</strong></td>
                    </tr>
                  `;
                }).join('')}
              </tbody>
            </table>
          </div>
        `;

        el.innerHTML = html || 'Sin detalle.';
      })
      .catch(err=>{
        console.error(err);
        el.innerHTML = '<div class="text-danger">No se pudo cargar el detalle.</div>';
      });

    new bootstrap.Modal(document.getElementById('detalleModal')).show();
  }

  function refresh(){
    loadOrders()
      .then(()=>render())
      .catch(err=>{
        console.error(err);
        $('tbodyMisPedidos').innerHTML =
          `<tr><td colspan="7" class="text-danger">No se pudieron cargar tus pedidos.</td></tr>`;
      });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    $('btnRefresh').addEventListener('click', ()=>{ toast('Actualizando...'); refresh(); });
    $('txtSearch').addEventListener('input', render);
    refresh();
  });
})();
</script>

</body>
</html>
