<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Seguridad básica (si tu proyecto ya lo maneja diferente, lo podés ajustar)
if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}

$perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
$isAdmin = ((string)$perfil === "1");
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

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-3">
                <div>
                    <h1 class="m-0">Productos</h1>
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <?php if ($isAdmin): ?>
                        <button id="btnNuevoProducto" type="button" class="btn btn-warning btn-soft">
                            <i class="fas fa-plus me-1"></i> Nuevo producto
                        </button>
                    <?php endif; ?>

                    <a class="btn btn-outline-light btn-soft" href="carrito.php">
                        <i class="fas fa-shopping-cart me-1"></i> Carrito (<span id="cartCountTop">0</span>)
                    </a>
                </div>
            </div>

            <div class="card-soft mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <input id="txtBuscar" class="form-control search-pill" style="max-width:420px" placeholder="Buscar...">

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-soft btn-sm" data-cat="Todos">Todos</button>
                        <button type="button" class="btn btn-outline-secondary btn-soft btn-sm" data-cat="Licorera">Licorera</button>
                        <button type="button" class="btn btn-outline-secondary btn-soft btn-sm" data-cat="Mayoreo">Mayoreo</button>
                    </div>
                </div>
            </div>

            <div id="productsError" class="alert alert-danger d-none"></div>

            <div id="productsGrid" class="row g-3">
                <div class="col-12 text-muted">Cargando...</div>
            </div>

        </main>
        <?php showFooter(); ?>
    </div>
</div>

<?php showJs(); ?>

<!-- Modal Crear/Editar -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px; overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(90deg, rgba(12,44,60,1) 0%, rgba(20,48,68,1) 70%, rgba(204,164,76,.14) 100%); color:#fff;">
        <h5 class="modal-title" id="productModalTitle" style="font-weight:900;">Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="productForm">
        <div class="modal-body">
            <div id="productFormError" class="alert alert-danger d-none"></div>

            <input type="hidden" id="p_id" name="id" value="">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Nombre</label>
                    <input class="form-control" id="p_nombre" name="nombre" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Categoría</label>
                    <input class="form-control" id="p_categoria" name="categoria" placeholder="Licorera" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea class="form-control" id="p_descripcion" name="descripcion" rows="2"></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Precio</label>
                    <input class="form-control" id="p_precio" name="precio" type="number" step="0.01" min="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Stock</label>
                    <input class="form-control" id="p_stock" name="stock" type="number" step="1" min="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Unidad</label>
                    <input class="form-control" id="p_unidad" name="unidad" placeholder="pack / unidad">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Proveedor</label>
                    <input class="form-control" id="p_proveedor" name="proveedor">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Imagen (URL opcional)</label>
                    <input class="form-control" id="p_imagen" name="imagen" placeholder="https://... o imagenes/xxx.jpg">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Subir imagen</label>
                    <input class="form-control" id="p_imagenFile" name="imagenFile" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <div class="form-text">Si subís archivo, reemplaza la URL.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">¿Equipo?</label>
                    <select class="form-select" id="p_es_equipo" name="es_equipo">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Activo</label>
                    <select class="form-select" id="p_activo" name="activo">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-soft" id="btnGuardarProducto">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const API_PRODUCTS = '../../Controller/ProductController.php';
  const API_CART = '../../Controller/CartController.php';

  const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

  const grid = document.getElementById('productsGrid');
  const errBox = document.getElementById('productsError');
  const txtBuscar = document.getElementById('txtBuscar');
  const cartCountTop = document.getElementById('cartCountTop');

  let allProducts = [];
  let currentCat = 'Todos';

  function showError(msg){
    errBox.textContent = msg;
    errBox.classList.remove('d-none');
  }
  function clearError(){
    errBox.classList.add('d-none');
    errBox.textContent = '';
  }

  async function getJson(url){
    const r = await fetch(url);
    const t = await r.text();
    try { return JSON.parse(t); }
    catch(e){ throw new Error('Respuesta inválida: ' + t); }
  }

  async function postForm(url, formData){
    const r = await fetch(url, { method:'POST', body: formData });
    const t = await r.text();
    try { return JSON.parse(t); }
    catch(e){ throw new Error('Respuesta inválida: ' + t); }
  }

  function money(n){
    return '₡' + Number(n||0).toLocaleString('es-CR',{minimumFractionDigits:2, maximumFractionDigits:2});
  }

  function escapeHtml(s){
    return String(s||'')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function filteredProducts(){
    const q = (txtBuscar.value||'').trim().toLowerCase();
    return allProducts.filter(p=>{
      if(currentCat !== 'Todos' && String(p.categoria||'') !== currentCat) return false;
      if(!q) return true;
      const t = [p.nombre, p.descripcion, p.categoria, p.proveedor].join(' ').toLowerCase();
      return t.includes(q);
    });
  }

  function render(){
    clearError();
    const list = filteredProducts();

    if(list.length === 0){
      grid.innerHTML = `<div class="col-12 text-muted">No hay productos para mostrar.</div>`;
      return;
    }

    grid.innerHTML = list.map(p=>{
      const id = Number(p.id);
      const img = p.imagen ? p.imagen : '';
      const nombre = escapeHtml(p.nombre||'');
      const cat = escapeHtml(p.categoria||'');
      const precio = money(p.precio);
      const stock = Number(p.stock||0);

      return `
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card-soft" style="height:100%;">
            <div style="border-radius:16px; overflow:hidden;">
              <img src="${img}" alt="" style="width:100%; height:200px; object-fit:cover; display:block;">
            </div>

            <div style="padding:14px 14px 10px;">
              <div class="text-muted" style="font-weight:900; font-size:12px;">${cat}</div>
              <div style="font-weight:900; line-height:1.2; margin-bottom:8px;">${nombre}</div>

              <div style="font-weight:900; margin-bottom:6px;">${precio}</div>
              <div class="text-muted" style="font-size:12px;">Stock: ${stock}</div>
            </div>

            <div class="d-flex gap-2 align-items-center justify-content-between" style="padding:10px 14px 14px;">
              <button type="button" class="btn btn-warning btn-soft btn-sm" data-add="${id}">
                <i class="fas fa-cart-plus"></i>
              </button>

              ${isAdmin ? `
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary btn-soft btn-sm" data-edit="${id}" title="Editar">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-soft btn-sm" data-del="${id}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              ` : `<span></span>`}
            </div>
          </div>
        </div>
      `;
    }).join('');

    // Bind botones
    grid.querySelectorAll('[data-add]').forEach(btn=>{
      btn.addEventListener('click', ()=> addToCart(btn.getAttribute('data-add')));
    });

    if(isAdmin){
      grid.querySelectorAll('[data-edit]').forEach(btn=>{
        btn.addEventListener('click', ()=> openEdit(btn.getAttribute('data-edit')));
      });
      grid.querySelectorAll('[data-del]').forEach(btn=>{
        btn.addEventListener('click', ()=> doDelete(btn.getAttribute('data-del')));
      });
    }
  }

  async function loadProducts(){
    grid.innerHTML = `<div class="col-12 text-muted">Cargando...</div>`;
    try{
      const j = await getJson(`${API_PRODUCTS}?action=list`);
      if(!j.success) throw new Error(j.message || 'No se pudieron cargar los productos');
      allProducts = j.data || [];
      render();
    }catch(err){
      console.error(err);
      showError(err.message || 'Error cargando productos');
      grid.innerHTML = `<div class="col-12 text-muted">No se pudieron cargar.</div>`;
    }
  }

  // -------------------------
  // Carrito (solo sumar contador arriba)
  // -------------------------
  async function refreshCartCount(){
    try{
      const j = await getJson(`${API_CART}?action=list`);
      if(j.success && j.data && Array.isArray(j.data.items)){
        const c = j.data.items.reduce((a,b)=> a + Number(b.cantidad||0), 0);
        cartCountTop.textContent = String(c);
      }
    }catch(e){
      // ignore
    }
  }

  async function addToCart(productId){
    try{
      const fd = new FormData();
      fd.append('id', productId);
      const j = await postForm(`${API_CART}?action=add`, fd);
      if(!j.success) throw new Error(j.message || 'No se pudo agregar');
      await refreshCartCount();
    }catch(err){
      console.error(err);
      showError(err.message || 'No se pudo agregar al carrito');
    }
  }

  // -------------------------
  // ADMIN: Crear / Editar / Eliminar
  // -------------------------
  const modalEl = document.getElementById('productModal');
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('productForm');
  const formErr = document.getElementById('productFormError');
  const title = document.getElementById('productModalTitle');

  const p_id = document.getElementById('p_id');
  const p_nombre = document.getElementById('p_nombre');
  const p_categoria = document.getElementById('p_categoria');
  const p_descripcion = document.getElementById('p_descripcion');
  const p_precio = document.getElementById('p_precio');
  const p_stock = document.getElementById('p_stock');
  const p_unidad = document.getElementById('p_unidad');
  const p_proveedor = document.getElementById('p_proveedor');
  const p_imagen = document.getElementById('p_imagen');
  const p_imagenFile = document.getElementById('p_imagenFile');
  const p_es_equipo = document.getElementById('p_es_equipo');
  const p_activo = document.getElementById('p_activo');

  function clearFormError(){
    formErr.classList.add('d-none');
    formErr.textContent = '';
  }
  function showFormError(msg){
    formErr.textContent = msg;
    formErr.classList.remove('d-none');
  }

  function openCreate(){
    clearFormError();
    title.textContent = 'Nuevo producto';

    p_id.value = '';
    p_nombre.value = '';
    p_categoria.value = '';
    p_descripcion.value = '';
    p_precio.value = 0;
    p_stock.value = 0;
    p_unidad.value = '';
    p_proveedor.value = '';
    p_imagen.value = '';
    p_imagenFile.value = '';
    p_es_equipo.value = '0';
    p_activo.value = '1';

    modal.show();
  }

  async function openEdit(id){
    clearFormError();
    title.textContent = 'Editar producto';

    try{
      const j = await getJson(`${API_PRODUCTS}?action=view&id=${encodeURIComponent(id)}`);
      if(!j.success) throw new Error(j.message || 'No se pudo cargar el producto');

      const p = j.data || {};
      p_id.value = p.id || id;
      p_nombre.value = p.nombre || '';
      p_categoria.value = p.categoria || '';
      p_descripcion.value = p.descripcion || '';
      p_precio.value = p.precio ?? 0;
      p_stock.value = p.stock ?? 0;
      p_unidad.value = p.unidad || '';
      p_proveedor.value = p.proveedor || '';
      // Guardamos lo que venga en BD (puede ser imagenes/xxx.jpg)
      p_imagen.value = (p.imagen_raw || p.imagen || '');
      p_imagenFile.value = '';
      p_es_equipo.value = String(p.es_equipo ?? 0);
      p_activo.value = String(p.activo ?? 1);

      modal.show();

    }catch(err){
      console.error(err);
      showError(err.message || 'No se pudo abrir editar');
    }
  }

  async function doDelete(id){
    if(!confirm('¿Eliminar este producto?')) return;

    try{
      const fd = new FormData();
      fd.append('id', id);
      const j = await postForm(`${API_PRODUCTS}?action=delete`, fd);
      if(!j.success) throw new Error(j.message || 'No se pudo eliminar');

      await loadProducts();

    }catch(err){
      console.error(err);
      showError(err.message || 'No se pudo eliminar');
    }
  }

  // Submit Create/Update
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    clearFormError();

    try{
      const id = (p_id.value||'').trim();
      const fd = new FormData();

      fd.append('nombre', p_nombre.value.trim());
      fd.append('categoria', p_categoria.value.trim());
      fd.append('descripcion', p_descripcion.value.trim());
      fd.append('precio', p_precio.value);
      fd.append('stock', p_stock.value);
      fd.append('unidad', p_unidad.value.trim());
      fd.append('proveedor', p_proveedor.value.trim());
      fd.append('imagen', p_imagen.value.trim());
      fd.append('es_equipo', p_es_equipo.value);
      fd.append('activo', p_activo.value);

      if (p_imagenFile.files && p_imagenFile.files[0]) {
        fd.append('imagenFile', p_imagenFile.files[0]);
      }

      let url = `${API_PRODUCTS}?action=create`;
      if(id){
        url = `${API_PRODUCTS}?action=update&id=${encodeURIComponent(id)}`;
      }

      const j = await postForm(url, fd);
      if(!j.success) throw new Error(j.message || 'No se pudo guardar');

      modal.hide();
      await loadProducts();

    }catch(err){
      console.error(err);
      showFormError(err.message || 'No se pudo guardar');
    }
  });

  // Botón nuevo
  const btnNuevo = document.getElementById('btnNuevoProducto');
  if(btnNuevo){
    btnNuevo.addEventListener('click', openCreate);
  }

  // Filtros categoría
  document.querySelectorAll('[data-cat]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      currentCat = btn.getAttribute('data-cat') || 'Todos';
      render();
    });
  });

  // Buscar
  txtBuscar.addEventListener('input', render);

  // Init
  document.addEventListener('DOMContentLoaded', async ()=>{
    await loadProducts();
    await refreshCartCount();
  });

})();
</script>

</body>
</html>
