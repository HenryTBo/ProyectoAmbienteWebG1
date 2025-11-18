<?php
// View/productos.php
// Vista de Productos - Distribuidora JJ
// Colocar en: ProyectoAmbienteWebG1/View/productos.php

// Intentar usar la conexión del proyecto
$use_db = false;
$products = [];

try {
    if (file_exists(__DIR__ . '/../Model/ConexionModel.php')) {
        include_once __DIR__ . '/../Model/ConexionModel.php';
        $conn = OpenConnection(); // la función que detecté en tu proyecto
        $use_db = true;

        // Consulta básica — ajusta el nombre de la tabla si tu DB la llama distinto
        $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo
                FROM productos
                WHERE activo = 1";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $products[] = $row;
        }
        CloseConnection($conn);
    }
} catch (Exception $e) {
    // Si hay error en DB, seguimos con datos de muestra abajo
    $use_db = false;
}

// Si no había DB o no hay resultados, usar datos de ejemplo para la UI
if (!$use_db || count($products) === 0) {
    $products = [
        ["id"=>1,"nombre"=>"Cerveza Imperial 24x355ml","descripcion"=>"Pack de 24 unidades - ideal para bares."
        ,"categoria"=>"Licorera","precio"=>45000,"stock"=>120,"unidad"=>"pack","proveedor"=>"Cervecería Regional"
        ,"imagen"=>"https://www.bing.com/images/search?view=detailV2&ccid=E7fYuad7&id=FA84C060E69A46A9ED48FD0D8859A903B045EE66&thid=OIP.E7fYuad7Q21rjoPrQEaeNgHaHa&mediaurl=https%3a%2f%2fwalmartcr.vtexassets.com%2farquivos%2fids%2f901534-500-auto%3fv%3d638796945244600000%26width%3d500%26height%3dauto%26aspect%3dtrue&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.13b7d8b9a77b436d6b8e83eb40469e36%3frik%3dZu5FsAOpWYgN%252fQ%26pid%3dImgRaw%26r%3d0&exph=500&expw=500&q=Cerveza+Imperial+24pack+costa+rica&FORM=IRPRST&ck=69B2EDC7CDBC8A51B4BD03DDB6A3FF59&selectedIndex=38&itb=0","es_equipo"=>0],
        ["id"=>2,"nombre"=>"Gaseosa Coca-Cola 2L","descripcion"=>"Botella 2 litros - venta por unidad o caja.",
        "categoria"=>"Supermercado","precio"=>800,"stock"=>500,"unidad"=>"unidad","proveedor"=>"Coca-Cola",
        "imagen"=>"https://via.placeholder.com/300x200?text=Coca-Cola","es_equipo"=>0],
        ["id"=>3,"nombre"=>"Congelador Exhibidor 200L","descripcion"=>"Congelador vertical para punto de venta. Disponible renta o venta.",
        "categoria"=>"Mayoreo","precio"=>650000,"stock"=>5,"unidad"=>"unidad","proveedor"=>"Equipamientos JJ",
        "imagen"=>"https://via.placeholder.com/300x200?text=Congelador","es_equipo"=>1],
        ["id"=>4,"nombre"=>"Diario (entrega a domicilio)","descripcion"=>"Servicio de entrega diaria de diarios y prensa local.",
        "categoria"=>"Distribucion","precio"=>300000,"stock"=>9999,"unidad"=>"servicio","proveedor"=>"Distribuidora JJ","imagen"=>"https://via.placeholder.com/300x200?text=Diario","es_equipo"=>0],
        ["id"=>5,"nombre"=>"Caja retornable de cerveza","descripcion"=>"Cajas retornables para recolección en la ruta.",
        "categoria"=>"Logistica","precio"=>0,"stock"=>200,"unidad"=>"unidad","proveedor"=>"Cervecería Regional",
        "imagen"=>"https://via.placeholder.com/300x200?text=Caja+Retornable","es_equipo"=>0]
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos — Distribuidora JJ</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .product-card { min-height: 360px; }
    .badge-stock { font-size: 0.85rem; }
    .precio { font-weight:700; font-size:1.05rem; color:#0b5ed7; }
    .category-chip { cursor:pointer; }
    .equipment { border-left: 4px solid #17a2b8; padding-left: .5rem; }
    .small-muted { font-size:0.86rem; color:#6c757d; }
  </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Distribuidora JJ</a>
    <div class="ms-auto text-white small-muted">Licorera · Supermercado · Mayoreo</div>
  </div>
</nav>

<div class="container my-4">
  <div class="row mb-3">
    <div class="col-md-8">
      <h2>Catálogo de Productos</h2>
      <p class="small-muted">Explore productos por categoría, busque por nombre o filtre por disponibilidad. Aquí puede ver opciones de venta al por menor y por mayor, así como equipos en renta/venta.</p>
    </div>
    <div class="col-md-4 d-flex align-items-center justify-content-end">
      <div>
        <input id="searchInput" class="form-control" placeholder="Buscar producto, proveedor, categoría..." />
      </div>
    </div>
  </div>

  <!-- Categorías / Filtros -->
  <div class="row mb-3">
    <div class="col">
      <div class="btn-group" role="group" aria-label="categorias">
        <button class="btn btn-outline-secondary category-chip active" data-cat="All">Todos</button>
        <button class="btn btn-outline-secondary category-chip" data-cat="Licorera">Licorera</button>
        <button class="btn btn-outline-secondary category-chip" data-cat="Supermercado">Supermercado</button>
        <button class="btn btn-outline-secondary category-chip" data-cat="Mayoreo">Mayoreo</button>
        <button class="btn btn-outline-secondary category-chip" data-cat="Distribucion">Distribución</button>
        <button class="btn btn-outline-secondary category-chip" data-cat="Logistica">Logística</button>
        <button class="btn btn-outline-secondary" id="onlyEquipment">Solo Equipos</button>
      </div>
    </div>
    <div class="col-auto">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="toggleStock" checked>
        <label class="form-check-label small-muted" for="toggleStock">Mostrar solo con stock</label>
      </div>
    </div>
  </div>

  <!-- Productos -->
  <div id="productsGrid" class="row g-3">
    <?php foreach($products as $p): ?>
      <div class="col-sm-6 col-md-4 col-lg-3 product-item" 
           data-name="<?=htmlspecialchars($p['nombre'])?>" 
           data-desc="<?=htmlspecialchars($p['descripcion'])?>" 
           data-cat="<?=htmlspecialchars($p['categoria'])?>" 
           data-stock="<?=intval($p['stock'])?>" 
           data-proveedor="<?=htmlspecialchars($p['proveedor'])?>"
           data-precio="<?=floatval($p['precio'])?>"
           data-id="<?=intval($p['id'])?>"
           data-es-equipo="<?=intval($p['es_equipo'])?>">
        <div class="card product-card h-100">
          <img src="<?=htmlspecialchars($p['imagen'])?>" class="card-img-top" alt="<?=htmlspecialchars($p['nombre'])?>" style="height:160px;object-fit:cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1"><?=htmlspecialchars($p['nombre'])?></h5>
            <p class="small-muted mb-2"><?=htmlspecialchars(substr($p['descripcion'],0,90))?><?=strlen($p['descripcion'])>90?"...":""?></p>

            <div class="mb-2">
              <span class="badge bg-secondary"><?=$p['categoria']?></span>
              <?php if(intval($p['es_equipo'])===1): ?>
                <span class="badge bg-info text-dark">Equipo</span>
              <?php endif; ?>
              <?php if(intval($p['stock'])<=5): ?>
                <span class="badge bg-warning text-dark badge-stock">Stock bajo</span>
              <?php elseif(intval($p['stock'])==0): ?>
                <span class="badge bg-danger badge-stock">Agotado</span>
              <?php endif; ?>
            </div>

            <div class="mt-auto">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="precio">₡ <?=number_format($p['precio'],0,',','.')?></div>
                  <div class="small-muted"><?=intval($p['stock'])?> en stock · <?=htmlspecialchars($p['unidad'] ?? 'unidad')?></div>
                </div>
                <div class="text-end">
                  <button class="btn btn-sm btn-outline-primary mb-1 btn-detail" data-id="<?=$p['id']?>">Ver</button>
                  <button class="btn btn-sm btn-primary btn-add" data-id="<?=$p['id']?>">Agregar</button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer small-muted">
            Proveedor: <?=htmlspecialchars($p['proveedor'])?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Paginación / acciones -->
  <div class="row mt-4">
    <div class="col-md-6">
      <button id="viewCart" class="btn btn-success">Ver carrito <span id="cartCount" class="badge bg-light text-dark">0</span></button>
      <button id="bulkOrder" class="btn btn-outline-secondary">Pedido por mayor / Crédito</button>
    </div>
    <div class="col-md-6 text-end small-muted">
      <span>Entrega y rutas disponibles para la zona sur. Recolección de retornables gestionada en cada ruta.</span>
    </div>
  </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalTitle" class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <img id="modalImage" src="" alt="" class="img-fluid" style="width:100%;height:320px;object-fit:cover;">
          </div>
          <div class="col-md-6">
            <p id="modalDesc" class="small-muted"></p>
            <p><strong>Proveedor:</strong> <span id="modalProveedor"></span></p>
            <p><strong>Categoría:</strong> <span id="modalCat"></span></p>
            <p><strong>Stock:</strong> <span id="modalStock"></span></p>
            <p class="precio" id="modalPrecio"></p>

            <div class="mb-2">
              <label for="qtySelect" class="form-label">Cantidad</label>
              <input id="qtySelect" type="number" class="form-control" value="1" min="1" />
            </div>

            <div class="d-flex gap-2">
              <button id="modalAdd" class="btn btn-primary">Agregar al carrito</button>
              <button id="modalOrder" class="btn btn-outline-secondary">Solicitar pedido mayor / crédito</button>
            </div>

            <hr>
            <div>
              <h6>Opciones de entrega</h6>
              <ul>
                <li>Entrega estándar en ruta (sin costo para pedidos mayores a ₡500,000).</li>
                <li>Entrega a domicilio (cargo adicional según distancia).</li>
                <li>Servicio diario (suscripciones de entrega de diarios y prensa).</li>
              </ul>
            </div>

            <div class="mt-2">
              <h6>Política de retornables</h6>
              <p class="small-muted">Se recogen cajas y botellas retornables en la ruta. El canje se realiza con las cervecerías y proveedores según acuerdo.</p>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Carrito -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Carrito de Compras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="cartItems"></div>
        <div class="mt-3 text-end">
          <div class="h5">Total: <span id="cartTotal">₡0</span></div>
          <button id="checkoutBtn" class="btn btn-success mt-2">Finalizar pedido</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Pedido Mayor -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pedido por mayor / Solicitar Crédito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="bulkForm">
          <div class="mb-2">
            <label class="form-label">Cliente / Negocio</label>
            <input class="form-control" name="cliente" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Monto estimado (₡)</label>
            <input class="form-control" name="monto" type="number" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Forma de pago</label>
            <select class="form-select" name="pago">
              <option value="contado">Contado</option>
              <option value="credito">Crédito</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Observaciones / Equipo requerido</label>
            <textarea class="form-control" name="obs" rows="3"></textarea>
          </div>
          <div class="text-end">
            <button class="btn btn-primary" type="submit">Enviar solicitud</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Client-side interactivity: búsqueda, filtros, carrito (localStorage)
let products = Array.from(document.querySelectorAll('.product-item')).map(el => ({
  id: el.dataset.id,
  name: el.dataset.name,
  desc: el.dataset.desc,
  cat: el.dataset.cat,
  stock: parseInt(el.dataset.stock),
  proveedor: el.dataset.proveedor,
  precio: parseFloat(el.dataset.precio),
  esEquipo: parseInt(el.dataset.esEquipo),
  img: el.querySelector('img').src
}));

// Helpers
function formatPrice(v){ return '₡ ' + new Intl.NumberFormat('es-CR').format(v); }

// Search & filter
const searchInput = document.getElementById('searchInput');
const categoryButtons = document.querySelectorAll('.category-chip');
const onlyEquipmentBtn = document.getElementById('onlyEquipment');
const toggleStock = document.getElementById('toggleStock');

function renderGrid(filterFn){
  document.getElementById('productsGrid').querySelectorAll('.product-item').forEach(el => {
    const id = el.dataset.id;
    const p = products.find(x=>x.id==id);
    let show = filterFn ? filterFn(p) : true;
    el.style.display = show ? '' : 'none';
  });
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const activeCat = Array.from(categoryButtons).find(b=>b.classList.contains('active'))?.dataset.cat || 'All';
  const onlyEq = onlyEquipmentBtn.classList.contains('active');
  const requireStock = toggleStock.checked;

  renderGrid(p => {
    if(!p) return false;
    if(requireStock && p.stock<=0) return false;
    if(onlyEq && p.esEquipo!==1) return false;
    if(activeCat!='All' && p.cat!=activeCat) return false;
    if(q && !(p.name.toLowerCase().includes(q) || p.desc.toLowerCase().includes(q) || p.proveedor.toLowerCase().includes(q) || p.cat.toLowerCase().includes(q))) return false;
    return true;
  });
}

// UI bindings
searchInput.addEventListener('input', applyFilters);
categoryButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    categoryButtons.forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
  });
});
onlyEquipmentBtn.addEventListener('click', () => {
  onlyEquipmentBtn.classList.toggle('active');
  applyFilters();
});
toggleStock.addEventListener('change', applyFilters);

// Detail modal
const productModal = new bootstrap.Modal(document.getElementById('productModal'));
document.querySelectorAll('.btn-detail').forEach(b => b.addEventListener('click', e => {
  const id = e.currentTarget.dataset.id;
  const p = products.find(x=>x.id==id);
  if(!p) return;
  document.getElementById('modalTitle').innerText = p.name;
  document.getElementById('modalImage').src = p.img;
  document.getElementById('modalDesc').innerText = p.desc;
  document.getElementById('modalProveedor').innerText = p.proveedor;
  document.getElementById('modalCat').innerText = p.cat;
  document.getElementById('modalStock').innerText = p.stock;
  document.getElementById('modalPrecio').innerText = formatPrice(p.precio);
  document.getElementById('qtySelect').value = 1;
  document.getElementById('modalAdd').dataset.id = p.id;
  productModal.show();
}));

// Carrito simple (localStorage)
let cart = JSON.parse(localStorage.getItem('jj_cart')||'[]');
function updateCartCount(){ document.getElementById('cartCount').innerText = cart.reduce((s,i)=>s+i.qty,0); }
function saveCart(){ localStorage.setItem('jj_cart', JSON.stringify(cart)); updateCartCount(); }

document.querySelectorAll('.btn-add').forEach(b => b.addEventListener('click', e => {
  const id = e.currentTarget.dataset.id;
  addToCart(id,1);
}));

document.getElementById('modalAdd').addEventListener('click', e => {
  addToCart(e.currentTarget.dataset.id, parseInt(document.getElementById('qtySelect').value||1));
  productModal.hide();
});

function addToCart(id, qty){
  const p = products.find(x=>x.id==id);
  if(!p) return alert('Producto no encontrado');
  const existing = cart.find(x=>x.id==id);
  if(existing) existing.qty += qty; else cart.push({id:id, name:p.name, precio:p.precio, qty:qty});
  saveCart();
  alert('Producto agregado al carrito');
}

// Ver carrito
const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
document.getElementById('viewCart').addEventListener('click', () => {
  renderCartItems();
  cartModal.show();
});

function renderCartItems(){
  const container = document.getElementById('cartItems');
  container.innerHTML = '';
  if(cart.length===0){ container.innerHTML = '<div class="alert alert-info">El carrito está vacío.</div>'; document.getElementById('cartTotal').innerText='₡0'; return; }
  let total = 0;
  cart.forEach(item => {
    total += item.precio * item.qty;
    const row = document.createElement('div');
    row.className = 'd-flex justify-content-between align-items-center border-bottom py-2';
    row.innerHTML = `<div><strong>${item.name}</strong><div class="small-muted">₡ ${new Intl.NumberFormat('es-CR').format(item.precio)} · Cant: <input type="number" class="form-control d-inline-block qty-sm" value="${item.qty}" style="width:70px" data-id="${item.id}"></div></div>
                     <div>₡ ${new Intl.NumberFormat('es-CR').format(item.precio*item.qty)} <br><button class="btn btn-sm btn-link text-danger remove-item" data-id="${item.id}">Eliminar</button></div>`;
    container.appendChild(row);
  });
  document.getElementById('cartTotal').innerText = formatPrice(total);
  // bind qty change & remove
  container.querySelectorAll('.qty-sm').forEach(inp=>{
    inp.addEventListener('change', e=>{
      const id = e.target.dataset.id;
      const v = parseInt(e.target.value) || 1;
      const it = cart.find(x=>x.id==id);
      if(it){ it.qty = v; saveCart(); renderCartItems(); }
    });
  });
  container.querySelectorAll('.remove-item').forEach(btn=>{
    btn.addEventListener('click', e=>{
      const id = e.currentTarget.dataset.id;
      cart = cart.filter(x=>x.id!=id); saveCart(); renderCartItems(); updateCartCount();
    });
  });
}

document.getElementById('checkoutBtn').addEventListener('click', () => {
  if(cart.length===0) return alert('Carrito vacío');
  // Aquí podrías enviar a un endpoint PHP que procese la orden
  alert('Simulación: Pedido enviado. Implementa el endpoint en el servidor para procesarlo.');
  cart = []; saveCart(); renderCartItems(); cartModal.hide();
});

// Pedido por mayor
const bulkModal = new bootstrap.Modal(document.getElementById('bulkModal'));
document.getElementById('bulkOrder').addEventListener('click', ()=>bulkModal.show());
document.getElementById('bulkForm').addEventListener('submit', e=>{
  e.preventDefault();
  const form = new FormData(e.target);
  // enviar por fetch a un endpoint PHP (no implementado aquí)
  alert('Solicitud de pedido por mayor enviada (simulación). Revisa en el backend para procesar solicitudes reales.');
  e.target.reset();
  bulkModal.hide();
});

// Init
applyFilters();
saveCart();
updateCartCount();
</script>
</body>
</html>
