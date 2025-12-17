<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Redirige a inicio de sesión si no hay usuario
if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php showCss(); ?>
    <link rel="stylesheet" href="../css/carrito.css">
</head>

<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
    <?php showSideBar(); ?>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">

            <div class="cart-hero mt-4 mb-4">
                <div>
                    <h2 class="cart-title">Carrito</h2>
                    <p class="cart-subtitle">Revisá tus productos y finalizá el pedido</p>
                </div>
                <div class="cart-hero-actions">
                    <a class="btn btn-outline-light" href="productos.php">
                        <i class="fas fa-arrow-left me-1"></i> Seguir comprando
                    </a>
                    <button id="btnVaciar" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Vaciar
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Productos -->
                <div class="col-lg-8">
                    <div class="card cart-card">
                        <div class="card-header cart-card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fw-bold">
                                    <i class="fas fa-shopping-basket me-2"></i> Productos
                                </div>
                                <div class="text-muted">
                                    Ítems: <span id="itemsCount">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="cartError" class="alert alert-danger d-none"></div>
                            <div id="cartLoading" class="text-muted">Cargando...</div>

                            <div id="cartEmpty" class="cart-empty d-none">
                                <h4>Tu carrito está vacío</h4>
                                <p class="text-muted mb-3">Agregá productos desde la tienda para verlos acá.</p>
                                <a class="btn btn-primary" href="productos.php">
                                    <i class="fas fa-store me-1"></i> Ir a productos
                                </a>
                            </div>

                            <div id="cartItems" class="d-none"></div>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="col-lg-4">
                    <div class="card cart-card sticky-summary">
                        <div class="card-header cart-card-header">
                            <div class="fw-bold"><i class="fas fa-receipt me-2"></i> Resumen</div>
                        </div>
                        <div class="card-body">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong id="subtotal">₡0,00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Envío</span>
                                <strong id="envio">₡0,00</strong>
                            </div>
                            <hr>
                            <div class="summary-row total">
                                <span>Total</span>
                                <strong id="total">₡0,00</strong>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-bold">Entrega</label>
                                <select id="entrega" class="form-select">
                                    <option value="Tienda">Tienda</option>
                                    <option value="Domicilio">Domicilio</option>
                                </select>
                            </div>

                            <div class="mt-3 d-none" id="direccionWrap">
                                <label class="form-label fw-bold">Dirección</label>
                                <textarea id="direccion" class="form-control" rows="3" placeholder="Indicá la dirección completa"></textarea>
                            </div>

                            <button id="btnFinalizar" class="btn btn-primary w-100 mt-3">
                                Finalizar pedido
                            </button>

                            <p class="text-muted small mt-2 mb-0">
                                Si elegís domicilio, asegurate de indicar la dirección.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <?php showFooter(); ?>
    </div>
</div>

<?php showJs(); ?>

<script>
(function(){

  const money = (n) => {
    const val = Number(n || 0);
    return '₡' + val.toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  const elLoading = document.getElementById('cartLoading');
  const elError = document.getElementById('cartError');
  const elEmpty = document.getElementById('cartEmpty');
  const elItems = document.getElementById('cartItems');
  const elCount = document.getElementById('itemsCount');

  const elSubtotal = document.getElementById('subtotal');
  const elEnvio = document.getElementById('envio');
  const elTotal = document.getElementById('total');

  const btnVaciar = document.getElementById('btnVaciar');
  const btnFinalizar = document.getElementById('btnFinalizar');

  const entrega = document.getElementById('entrega');
  const direccionWrap = document.getElementById('direccionWrap');
  const direccion = document.getElementById('direccion');

  function showError(msg){
    elError.textContent = msg;
    elError.classList.remove('d-none');
  }
  function clearError(){
    elError.classList.add('d-none');
    elError.textContent = '';
  }

  async function apiGet(url){
    const r = await fetch(url, { method: 'GET' });
    const t = await r.text();
    try { return JSON.parse(t); }
    catch(e){ throw new Error('Respuesta inválida del servidor: ' + t); }
  }

  async function apiPost(url, bodyObj){
    const fd = new FormData();
    Object.keys(bodyObj || {}).forEach(k => fd.append(k, bodyObj[k]));
    const r = await fetch(url, { method: 'POST', body: fd });
    const t = await r.text();
    try { return JSON.parse(t); }
    catch(e){ throw new Error('Respuesta inválida del servidor: ' + t); }
  }

  function renderCart(data){
    const items = (data && data.items) ? data.items : [];

    elLoading.classList.add('d-none');
    clearError();

    if(items.length === 0){
      elItems.classList.add('d-none');
      elEmpty.classList.remove('d-none');
      elCount.textContent = '0';
      elSubtotal.textContent = money(0);
      elEnvio.textContent = money(0);
      elTotal.textContent = money(0);
      return;
    }

    elEmpty.classList.add('d-none');
    elItems.classList.remove('d-none');

    // Contador (suma de cantidades)
    const count = items.reduce((a,b)=> a + Number(b.cantidad||0), 0);
    elCount.textContent = count;

    elSubtotal.textContent = money(data.subtotal);
    elEnvio.textContent = money(data.envio);
    elTotal.textContent = money(data.total);

    let html = '';
    items.forEach(it => {
      const id = it.id;
      html += `
        <div class="cart-item">
          <img class="cart-item-img" src="${it.imagen}" alt="">
          <div class="cart-item-body">
            <div class="cart-item-title">${it.nombre}</div>
            <div class="cart-item-meta">${money(it.precio)} c/u</div>

            <div class="cart-item-actions">
              <div class="qty">
                <button class="qty-btn" data-dec="${id}">-</button>
                <input class="qty-input" value="${it.cantidad}" data-qty="${id}" />
                <button class="qty-btn" data-inc="${id}">+</button>
              </div>

              <div class="cart-item-subtotal">${money(it.subtotal)}</div>

              <button class="btn btn-outline-danger btn-sm" data-del="${id}">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    });

    elItems.innerHTML = html;

    // events
    elItems.querySelectorAll('[data-inc]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-inc');
        const input = elItems.querySelector(`[data-qty="${id}"]`);
        const qty = Math.max(1, Number(input.value||1) + 1);
        await setQty(id, qty);
      });
    });

    elItems.querySelectorAll('[data-dec]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-dec');
        const input = elItems.querySelector(`[data-qty="${id}"]`);
        const qty = Math.max(1, Number(input.value||1) - 1);
        await setQty(id, qty);
      });
    });

    elItems.querySelectorAll('[data-qty]').forEach(input=>{
      input.addEventListener('change', async ()=>{
        const id = input.getAttribute('data-qty');
        const qty = Math.max(1, Number(input.value||1));
        await setQty(id, qty);
      });
    });

    elItems.querySelectorAll('[data-del]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-del');
        await removeItem(id);
      });
    });
  }

  async function loadCart(){
    elLoading.classList.remove('d-none');
    clearError();

    try{
      const json = await apiGet('../../Controller/CartController.php?action=list');
      if(!json.success) throw new Error(json.message || 'No se pudo cargar el carrito');
      renderCart(json.data);
    }catch(err){
      elLoading.classList.add('d-none');
      showError(err.message || 'No se pudo cargar el carrito');
      console.error(err);
    }
  }

  async function setQty(id, qty){
    try{
      const json = await apiPost('../../Controller/CartController.php?action=setQty', { id, qty });
      if(!json.success) throw new Error(json.message || 'No se pudo actualizar');
      await loadCart();
    }catch(err){
      showError(err.message || 'No se pudo actualizar');
      console.error(err);
    }
  }

  async function removeItem(id){
    try{
      const json = await apiPost('../../Controller/CartController.php?action=remove', { id });
      if(!json.success) throw new Error(json.message || 'No se pudo eliminar');
      await loadCart();
    }catch(err){
      showError(err.message || 'No se pudo eliminar');
      console.error(err);
    }
  }

  async function clearCart(){
    try{
      const json = await apiPost('../../Controller/CartController.php?action=clear', {});
      if(!json.success) throw new Error(json.message || 'No se pudo vaciar');
      await loadCart();
    }catch(err){
      showError(err.message || 'No se pudo vaciar');
      console.error(err);
    }
  }

  btnVaciar.addEventListener('click', ()=>{
    if(confirm('¿Vaciar carrito?')) clearCart();
  });

  entrega.addEventListener('change', ()=>{
    direccionWrap.classList.toggle('d-none', entrega.value !== 'Domicilio');
  });

  btnFinalizar.addEventListener('click', async ()=>{
    try{
      clearError();

      // Si eligió domicilio, pedir dirección
      if(entrega.value === 'Domicilio' && !String(direccion.value||'').trim()){
        showError('Debés indicar la dirección para entrega a domicilio.');
        return;
      }

      const payload = {
        entrega: entrega.value,
        direccion: entrega.value === 'Domicilio' ? direccion.value.trim() : ''
      };

      // Importante: este endpoint debe existir en tu proyecto
      const json = await apiPost('../../Controller/OrderController.php?action=create', payload);

      if(!json.success){
        throw new Error(json.message || 'No se pudo finalizar el pedido');
      }

      window.location.href = 'misPedidos.php';

    }catch(err){
      showError(err.message || 'No se pudo finalizar el pedido');
      console.error(err);
    }
  });

  document.addEventListener('DOMContentLoaded', loadCart);
})();
</script>

</body>
</html>
