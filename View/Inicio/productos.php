<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Redirige a inicio de sesión si no hay usuario
if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}

// Determina si el usuario tiene perfil de administrador (perfil = 1)
$perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
$esAdmin = ($perfil == "1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php showCss(); ?>
    <style>
      :root{
        --jj-navy:#0c2c3c;
        --jj-navy-2:#143044;
        --jj-gold:#cca44c;
        --jj-gold-2:#a97823;
        --jj-maroon:#8c1c1c;
        --jj-cream:#f7f5f0;
        --jj-graphite:#1c2430;
      }

      .page-title{ font-weight: 800; letter-spacing: .2px; color: var(--jj-navy); }

      .filters-card{
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 16px;
        padding: 14px 14px 10px 14px;
        box-shadow: 0 14px 28px rgba(0,0,0,.10);
      }

      .search-input{
        border-radius: 999px !important;
        padding-left: 18px !important;
        border: 1px solid rgba(0,0,0,.16) !important;
      }
      .search-input:focus{
        border-color: rgba(204,164,76,.75) !important;
        box-shadow: 0 0 0 .2rem rgba(204,164,76,.22) !important;
      }

      .cat-filter-group{ display:flex; flex-wrap: wrap; gap: 8px; }
      .cat-filter-group button{
        border-radius: 999px;
        padding: 6px 12px;
        border: 1px solid rgba(0,0,0,.12);
        background: rgba(255,255,255,.85);
        font-size: 13px;
        font-weight: 700;
        color: var(--jj-navy);
        transition: .15s ease-in-out;
      }
      .cat-filter-group button.active{
        background: rgba(204,164,76,.22);
        border-color: rgba(204,164,76,.55);
        color: var(--jj-navy);
      }
      .cat-filter-group button:hover{ background: var(--jj-gold); color: var(--jj-graphite); }

      .view-cart-btn{ font-size:14px; padding:7px 14px; border-radius:999px; }

      .floating-cart-btn{
        position: fixed;
        bottom: 18px;
        right: 18px;
        z-index: 9999;
        background-color: var(--jj-gold);
        color: #1b1b1b;
        border: none;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        box-shadow: 0 18px 40px rgba(0,0,0,.22);
        display:flex;
        align-items:center;
        justify-content:center;
      }
      .floating-cart-btn:hover{ filter: brightness(.96); transform: translateY(-1px); }
      .floating-cart-btn .badge{
        position: absolute;
        top: -6px;
        right: -6px;
        border-radius: 999px;
        font-size: 12px;
        padding: 6px 8px;
        background: var(--jj-maroon);
        color:#fff;
      }

      .product-card{
        border: 1px solid rgba(0,0,0,.08);
        background: rgba(255,255,255,.93);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 26px rgba(0,0,0,.10);
        transition: .15s ease-in-out;
        height: 100%;
      }
      .product-card:hover{ transform: translateY(-2px); box-shadow: 0 18px 40px rgba(0,0,0,.14); }

      .product-img{ width: 100%; height: 180px; object-fit: cover; background: #fff; }
      .product-body{ padding: 12px 12px 10px 12px; }
      .product-category{
        display:inline-block;
        font-size: 12px;
        font-weight: 800;
        color: var(--jj-maroon);
        letter-spacing: .2px;
        margin-bottom: 4px;
      }
      .product-title{
        font-weight: 800;
        color: var(--jj-navy);
        font-size: 15px;
        margin: 0 0 8px 0;
        min-height: 38px;
      }
      .product-price{ font-size: 16px; font-weight: 900; color: var(--jj-graphite); margin-bottom: 4px; }
      .product-stock{ font-size: 12px; color: rgba(0,0,0,.65); }

      .product-actions{
        padding: 10px 12px 12px 12px;
        display:flex;
        gap: 8px;
        align-items:center;
        justify-content: space-between;
      }
      .btn-add{ border-radius: 12px; font-weight: 800; }

      .toast{
        position: fixed;
        bottom: 22px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(12,44,60,.96);
        color: #fff;
        padding: 10px 14px;
        border-radius: 12px;
        box-shadow: 0 18px 40px rgba(0,0,0,.25);
        opacity: 0;
        transition: .15s ease-in-out;
        z-index: 99999;
        font-weight: 700;
        font-size: 13px;
      }
      .toast.visible{ opacity: 1; }

      .btn-primary{
        background: linear-gradient(135deg, var(--jj-gold), #f1c55a) !important;
        border: none !important;
        color: #1b1b1b !important;
        font-weight: 900 !important;
      }
      .btn-outline-primary{
        border-color: rgba(204,164,76,.55) !important;
        color: var(--jj-navy) !important;
        font-weight: 800 !important;
      }
      .btn-outline-primary:hover{ background: rgba(204,164,76,.18) !important; }
    </style>
</head>

<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
  <?php showSideBar(); ?>

  <div id="layoutSidenav_content">
    <main class="container-fluid px-4">
      <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
        <h2 class="page-title m-0">Productos</h2>

        <?php if ($esAdmin): ?>
          <button class="btn btn-primary" id="btnNuevoProducto" type="button">
            <i class="fas fa-plus me-1"></i> Nuevo producto
          </button>
        <?php endif; ?>
      </div>

      <div class="filters-card mb-4">
        <div class="row align-items-center">
          <div class="col-md-4 mb-2">
            <input id="searchInput" class="form-control search-input" type="text" placeholder="Buscar...">
          </div>
          <div class="col-md-8 mb-2">
            <div class="d-flex flex-wrap align-items-center gap-3 w-100">
              <div id="catFilterGroup" class="cat-filter-group"></div>
              <button id="viewCartBtn" type="button" class="btn btn-outline-primary ms-auto view-cart-btn">
                <i class="fas fa-shopping-cart me-1"></i>
                Carrito (<span id="cartCount">0</span>)
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row" id="productList">
        <p>Cargando productos...</p>
      </div>
    </main>

    <button id="floatingCartBtn" class="floating-cart-btn" title="Ver carrito">
      <i class="fas fa-shopping-cart"></i>
      <span id="floatingCartBadge" class="badge d-none">0</span>
    </button>

    <?php showFooter(); ?>
  </div>
</div>

<?php showJs(); ?>

<script>
(function(){
  const esAdmin = <?= $esAdmin ? 'true' : 'false' ?>;

  const CART_URL = '../../Controller/CartController.php';
  const PROD_URL = '../../Controller/ProductController.php';

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function toast(msg){
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerText = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('visible'), 10);
    setTimeout(() => {
      t.classList.remove('visible');
      setTimeout(() => t.remove(), 250);
    }, 1200);
  }

  // ✅ parse seguro: si el servidor responde HTML/Notice, no rompe silenciosamente
  async function fetchJsonSafe(url, options){
    const r = await fetch(url, Object.assign({ cache: 'no-store' }, options || {}));
    const text = await r.text();
    try { return JSON.parse(text); }
    catch(e){
      console.error('Respuesta NO JSON desde:', url, '\n', text);
      throw new Error('La respuesta del servidor no es JSON (revisar Notices/Warnings en PHP).');
    }
  }

  async function updateCartCount() {
    try{
      const json = await fetchJsonSafe(CART_URL + '?action=count', { method: 'GET' });
      const count = Number(json.count || 0);

      const countEl = document.getElementById('cartCount');
      if (countEl) countEl.textContent = count;

      const badge = document.getElementById('floatingCartBadge');
      if (badge) {
        badge.textContent = count;
        badge.classList.toggle('d-none', !(count > 0));
      }
    }catch(e){
      // no spamear al usuario
      console.error(e);
    }
  }

  // ✅ handler robusto
  async function addToCart(productId, btn){
    try{
      if(btn){
        btn.disabled = true;
        btn.dataset.oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Agregando...';
      }

      const fd = new FormData();
      // nombres “oficiales”
      fd.append('product_id', productId);
      fd.append('qty', 1);
      // nombres alternos (por si algún controlador/modelo antiguo los espera)
      fd.append('idProducto', productId);
      fd.append('cantidad', 1);

      const json = await fetchJsonSafe(CART_URL + '?action=addAjax', { method:'POST', body: fd });

      if(!json.success){
        toast(json.message || 'No se pudo agregar al carrito.');
        if ((json.message || '').toLowerCase().includes('iniciar sesión')) {
          setTimeout(() => window.location = 'InicioSesion.php', 700);
        }
        return;
      }

      await updateCartCount();
      toast('Producto agregado al carrito');

    }catch(err){
      console.error(err);
      toast('No se pudo agregar al carrito (revisa consola / PHP notices).');
    }finally{
      if(btn){
        btn.disabled = false;
        btn.innerHTML = btn.dataset.oldText || '<i class="fas fa-cart-plus me-1"></i> Agregar';
      }
    }
  }

  let allProducts = [];
  let currentCategory = 'ALL';
  let currentSearch = '';

  function renderCategories(){
    const group = document.getElementById('catFilterGroup');
    if(!group) return;

    const cats = Array.from(new Set(allProducts.map(p => (p.categoria || '').trim()).filter(c => c.length>0)));
    cats.sort((a,b)=>a.localeCompare(b));

    group.innerHTML = '';
    const btnAll = document.createElement('button');
    btnAll.type = 'button';
    btnAll.textContent = 'Todos';
    btnAll.className = (currentCategory === 'ALL') ? 'active' : '';
    btnAll.addEventListener('click', ()=>{
      currentCategory = 'ALL';
      renderCategories();
      renderProducts();
    });
    group.appendChild(btnAll);

    cats.forEach(cat=>{
      const b = document.createElement('button');
      b.type = 'button';
      b.textContent = cat;
      b.className = (currentCategory === cat) ? 'active' : '';
      b.addEventListener('click', ()=>{
        currentCategory = cat;
        renderCategories();
        renderProducts();
      });
      group.appendChild(b);
    });
  }

  function applyFilters(list){
    return list.filter(p=>{
      const okCat = (currentCategory === 'ALL') || String(p.categoria || '') === currentCategory;
      const q = currentSearch.trim().toLowerCase();
      const okSearch = !q
        || String(p.nombre||'').toLowerCase().includes(q)
        || String(p.descripcion||'').toLowerCase().includes(q);
      return okCat && okSearch;
    });
  }

  function bindCartButtons(){
    // ✅ evita que el click “suba” y termine abriendo el <a> del producto
    document.querySelectorAll('[data-add-to-cart]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        e.preventDefault();
        e.stopPropagation();
        const id = btn.getAttribute('data-add-to-cart');
        addToCart(id, btn);
      });
    });
  }

  function renderProducts(){
    const listEl = document.getElementById('productList');
    if(!listEl) return;

    const list = applyFilters(allProducts);

    if(list.length === 0){
      listEl.innerHTML = '<p class="text-muted">No hay productos para mostrar.</p>';
      return;
    }

    listEl.innerHTML = '';
    list.forEach(p=>{
      const col = document.createElement('div');
      col.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';

      col.innerHTML = `
        <div class="product-card">
          <a href="verProducto.php?id=${encodeURIComponent(p.id)}" style="text-decoration:none; color:inherit;">
            <img src="${escapeHtml(p.imagen)}" alt="${escapeHtml(p.nombre)}" class="product-img" onerror="this.src='../../public/images/placeholder.png'">
            <div class="product-body">
              <span class="product-category">${escapeHtml(p.categoria || '')}</span>
              <h5 class="product-title">${escapeHtml(p.nombre || '')}</h5>
              <div class="product-price">₡${Number(p.precio || 0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</div>
              <div class="product-stock">Stock: ${Number(p.stock || 0)}</div>
            </div>
          </a>

          <div class="product-actions">
            ${esAdmin ? `
              <button class="btn btn-outline-primary btn-sm" type="button" data-edit="${p.id}">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" type="button" data-del="${p.id}">
                <i class="fas fa-trash"></i>
              </button>
            ` : `
              <button
                class="btn btn-primary btn-sm btn-add"
                type="button"
                data-add-to-cart="${escapeHtml(p.id)}"
                ${Number(p.stock||0)<=0 ? 'disabled' : ''}>
                <i class="fas fa-cart-plus me-1"></i> Agregar
              </button>

              <a class="btn btn-outline-primary btn-sm" href="carrito.php">
                <i class="fas fa-credit-card me-1"></i> Pagar
              </a>
            `}
          </div>
        </div>
      `;

      listEl.appendChild(col);
    });

    bindCartButtons();
  }

  function loadProducts(){
    fetchJsonSafe(PROD_URL + '?action=list')
      .then(json=>{
        if(!json.success) throw new Error(json.message || 'Error al cargar productos');
        allProducts = json.data || [];
        renderCategories();
        renderProducts();
      })
      .catch(err=>{
        console.error(err);
        const listEl = document.getElementById('productList');
        if(listEl) listEl.innerHTML = '<p class="text-danger">No se pudieron cargar los productos.</p>';
      });
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    loadProducts();

    const search = document.getElementById('searchInput');
    if(search){
      search.addEventListener('input', ()=>{
        currentSearch = search.value || '';
        renderProducts();
      });
    }

    const viewCartBtn = document.getElementById('viewCartBtn');
    if(viewCartBtn) viewCartBtn.addEventListener('click', ()=> window.location.href = 'carrito.php');

    const floatingBtn = document.getElementById('floatingCartBtn');
    if(floatingBtn) floatingBtn.addEventListener('click', ()=> window.location.href = 'carrito.php');
  });
})();
</script>

</body>
</html>
