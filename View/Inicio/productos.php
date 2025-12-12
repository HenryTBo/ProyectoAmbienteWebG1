<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Redirige a inicio de sesión si no hay usuario
if (!isset($_SESSION["ConsecutivoUsuario"])) {
    header("Location: InicioSesion.php");
    exit;
}

// Determina si el usuario tiene perfil de administrador (perfil = 1)
$esAdmin = isset($_SESSION["ConsecutivoPerfil"]) && $_SESSION["ConsecutivoPerfil"] == "1";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/productos.css?v=2" rel="stylesheet">
    <!-- Pequeño estilo inline para garantizar que las imágenes de productos no crezcan desproporcionadas
         en caso de que no se cargue la hoja de estilos externa -->
    <style>
      #productList img {
        max-width: 100%;
        height: auto;
      }

      /* Tostada flotante para notificar adición al carrito */
      .toast {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        background-color: var(--jj-blue-deep);
        color: var(--jj-cream-light);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 9999;
        font-size: 0.9rem;
      }
      .toast.visible {
        opacity: 1;
      }

      /* Filtro de categorías como lista de botones */
      .cat-filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }
      .cat-filter-group button {
        padding: 6px 12px;
        border: 1px solid var(--jj-gold);
        border-radius: 20px;
        background-color: #fff;
        color: var(--jj-blue-deep);
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
      }
      .cat-filter-group button.active,
      .cat-filter-group button:hover {
        background-color: var(--jj-gold);
        color: var(--jj-graphite);
      }

      /* Botón de carrito */
      .view-cart-btn {
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 20px;
      }

      /* Botón flotante de carrito que se muestra en la esquina inferior derecha */
      .floating-cart-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        background-color: var(--jj-gold);
        color: var(--jj-blue-deep);
        border: none;
        border-radius: 50%;
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        transition: transform 0.2s;
      }
      .floating-cart-btn:hover {
        transform: scale(1.05);
      }
      .floating-cart-btn .badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background-color: var(--jj-red-warm);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        border-radius: 50%;
        padding: 2px 6px;
      }
    </style>
</head>
<body class="sb-nav-fixed">
<?php showNavBar(); ?>
<div id="layoutSidenav">
    <?php showSideBar(); ?>
    <div id="layoutSidenav_content">
        <main class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title">Productos</h2>
                    <p class="page-sub">Inventario de Distribuidora JJ</p>
                </div>
                <?php if ($esAdmin): ?>
                <button id="btnNuevoProducto" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuevo producto
                </button>
                <?php endif; ?>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-4 mb-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                </div>
                <div class="col-md-6 mb-2">
                    <div class="d-flex flex-wrap align-items-center gap-3 w-100">
                        <div id="catFilterGroup" class="cat-filter-group"></div>
                        <button id="viewCartBtn" type="button" class="btn btn-outline-primary ms-auto view-cart-btn">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Carrito (<span id="cartCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>

            <div class="row" id="productList">
                <p>Cargando productos...</p>
            </div>
        </main>
        <!-- Botón de carrito flotante que sigue al usuario mientras navega -->
        <button id="floatingCartBtn" class="floating-cart-btn" title="Ver carrito">
            <i class="fas fa-shopping-cart"></i>
            <span id="floatingCartBadge" class="badge d-none">0</span>
        </button>
        <?php showFooter(); ?>
    </div>
</div>
<?php showJs(); ?>

<!-- Modal para crear/editar producto -->
<?php if ($esAdmin): ?>
<div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="productoModalLabel">Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="productoForm">
          <input type="hidden" id="productoId">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
          <div class="col-md-6">
              <label for="categoria" class="form-label">Categoría</label>
              <select id="categoria" name="categoria" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="Licorera">Licorera</option>
                <option value="Supermercado">Supermercado</option>
                <option value="Mayoreo">Mayoreo</option>
                <option value="Equipos">Equipos</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="precio" class="form-label">Precio</label>
              <input type="number" id="precio" name="precio" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-3">
              <label for="stock" class="form-label">Stock</label>
              <input type="number" id="stock" name="stock" class="form-control" min="0" required>
            </div>
            <div class="col-md-3">
              <label for="unidad" class="form-label">Unidad</label>
              <input type="text" id="unidad" name="unidad" class="form-control">
            </div>
            <div class="col-md-3">
              <label for="proveedor" class="form-label">Proveedor</label>
              <input type="text" id="proveedor" name="proveedor" class="form-control">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="imagen" class="form-label">URL Imagen</label>
              <input type="text" id="imagen" name="imagen" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="imagenFile" class="form-label">Imagen local</label>
              <input type="file" id="imagenFile" name="imagenFile" accept="image/*" class="form-control" />
            </div>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="es_equipo" name="es_equipo">
            <label class="form-check-label" for="es_equipo">Es equipo</label>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
            <label class="form-check-label" for="activo">Activo</label>
          </div>
        </form>
        <div class="alert alert-danger d-none" id="productoError"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnGuardarProducto" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function() {
        const esAdmin = <?php echo json_encode($esAdmin); ?>;
        let allProducts = [];
        let currentEditId = null;
        // Categoría seleccionada para filtrar ('' = todas)
        let selectedCat = '';

    // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
        // Leer parámetros de la URL para categoría y búsqueda inicial
        const params = new URLSearchParams(window.location.search);
        selectedCat = params.get('cat') || '';
        const initialSearch = params.get('q') || '';
        document.getElementById('searchInput').value = initialSearch;

        loadProducts();
        document.getElementById('searchInput').addEventListener('input', filterAndRender);
        // La selección de categoría se maneja mediante botones en populateCategories()

        // Inicializar contador del carrito y manejar clic en el botón de carrito
        updateCartCount();
        const cartBtn = document.getElementById('viewCartBtn');
        if (cartBtn) {
            cartBtn.addEventListener('click', () => {
                window.location.href = 'carrito.php';
            });
        }

        // Manejar clic en el botón flotante para ir al carrito
        const floatingBtn = document.getElementById('floatingCartBtn');
        if (floatingBtn) {
            floatingBtn.addEventListener('click', () => {
                window.location.href = 'carrito.php';
            });
        }

        if (esAdmin) {
            document.getElementById('btnNuevoProducto').addEventListener('click', () => {
                openModal();
            });
            document.getElementById('btnGuardarProducto').addEventListener('click', saveProduct);
        }
    });

    /**
     * Obtiene productos desde la API y almacena en allProducts
     */
    function loadProducts() {
        fetch('../../Controller/ProductController.php?action=list')
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    allProducts = json.data || [];
                    populateCategories();
                    filterAndRender();
                } else {
                    console.error(json.error || 'Error al cargar productos');
                }
            })
            .catch(err => console.error(err));
    }

    /**
     * Llena el filtro de categorías en base a los productos cargados
     */
    function populateCategories() {
        const cont = document.getElementById('catFilterGroup');
        // Obtener categorías únicas y ordenarlas
        const cats = [...new Set(allProducts.map(p => p.categoria).filter(c => c && c.trim() !== ''))].sort();
        // Agregar categoría 'Todas' como primer elemento (representada por cadena vacía)
        const categories = [''].concat(cats);
        cont.innerHTML = '';
        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = cat === '' ? 'Todas' : cat;
            btn.className = '';
            btn.addEventListener('click', () => {
                selectedCat = cat;
                // Actualizar estado activo
                cont.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterAndRender();
            });
            cont.appendChild(btn);
        });
        // Seleccionar botón activo inicial según selectedCat
        let activeFound = false;
        cont.querySelectorAll('button').forEach(btn => {
            if (btn.textContent === (selectedCat === '' ? 'Todas' : selectedCat)) {
                btn.classList.add('active');
                activeFound = true;
            }
        });
        if (!activeFound) {
            const firstBtn = cont.querySelector('button');
            if (firstBtn) firstBtn.classList.add('active');
        }
    }

    /**
     * Filtra los productos por búsqueda y categoría, luego los renderiza
     */
    function filterAndRender() {
        const term = document.getElementById('searchInput').value.toLowerCase();
        const cat  = selectedCat;
        const filtered = allProducts.filter(p => {
            const matchTerm = p.nombre.toLowerCase().includes(term) ||
                              p.descripcion.toLowerCase().includes(term) ||
                              p.proveedor.toLowerCase().includes(term) ||
                              p.categoria.toLowerCase().includes(term);
            const matchCat  = cat === '' || p.categoria === cat;
            return matchTerm && matchCat;
        });
        renderProducts(filtered);
    }

    /**
     * Actualiza el número de artículos mostrados en el botón de carrito
     */
    function updateCartCount() {
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem('jj_cart') || '[]');
        } catch (e) {
            cart = [];
        }
        let count = 0;
        cart.forEach(item => {
            count += (item.qty || 1);
        });
        const countEl = document.getElementById('cartCount');
        if (countEl) countEl.textContent = count;
        // Actualizar el badge del botón flotante
        const badge = document.getElementById('floatingCartBadge');
        if (badge) {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    // Exponer las funciones al ámbito global para que puedan ser
    // llamadas desde atributos onclick en el HTML generado dinámicamente.
    window.updateCartCount = updateCartCount;

    /**
     * Renderiza un listado de productos en tarjetas
     */
    function renderProducts(list) {
        const cont = document.getElementById('productList');
        cont.innerHTML = '';
        if (!list || list.length === 0) {
            cont.innerHTML = '<p>No hay productos disponibles.</p>';
            return;
        }
        list.forEach(p => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
                <div class="product-card">
                    <img src="${p.imagen}" alt="${p.nombre}" class="product-img">
                    <div class="product-body">
                        <span class="product-category">${p.categoria || ''}</span>
                        <h5 class="product-title">${p.nombre}</h5>
                        <div class="product-price">₡${parseFloat(p.precio).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</div>
                        <div class="product-stock">Stock: ${p.stock}</div>
                    </div>
                </div>
            `;
            if (esAdmin) {
                // Acciones de administrador: editar y eliminar
                const actions = document.createElement('div');
                actions.className = 'product-actions mt-2';
                const btnEdit = document.createElement('button');
                btnEdit.className = 'btn btn-sm btn-outline-primary';
                btnEdit.innerHTML = '<i class="fas fa-edit me-1"></i>Editar';
                btnEdit.addEventListener('click', () => openModal(p.id));
                const btnDelete = document.createElement('button');
                btnDelete.className = 'btn btn-sm btn-outline-danger ms-2';
                btnDelete.innerHTML = '<i class="fas fa-trash me-1"></i>Eliminar';
                btnDelete.addEventListener('click', () => confirmDelete(p.id));
                actions.appendChild(btnEdit);
                actions.appendChild(btnDelete);
                col.querySelector('.product-body').appendChild(actions);
            } else {
                // Acciones de cliente: ver detalle y agregar al carrito
                const actions = document.createElement('div');
                actions.className = 'product-actions mt-2';
                const btnView = document.createElement('a');
                btnView.className = 'btn btn-sm btn-outline-primary';
                btnView.href = 'productoDetalle.php?id=' + p.id;
                btnView.textContent = 'Ver';
                // Botón para agregar al carrito
                const btnAdd = document.createElement('button');
                btnAdd.type = 'button';
                btnAdd.className = 'btn btn-sm btn-outline-primary ms-2';
                btnAdd.textContent = 'Agregar';
                btnAdd.addEventListener('click', () => addToCart(p.id));
                actions.appendChild(btnView);
                actions.appendChild(btnAdd);
                col.querySelector('.product-body').appendChild(actions);
            }
            cont.appendChild(col);
        });
    }

    /**
     * Abre el modal de productos. Si se pasa ID se carga el producto para editar.
     */
    function openModal(id = null) {
        // limpiar formulario y estado
        document.getElementById('productoForm').reset();
        document.getElementById('productoError').classList.add('d-none');
        currentEditId = id;

        if (id) {
            document.getElementById('productoModalLabel').textContent = 'Editar producto';
            fetch('../../Controller/ProductController.php?action=view&id=' + id)
                .then(r => r.json())
                .then(json => {
                    if (json.success) {
                        const p = json.data;
                        document.getElementById('productoId').value = p.id;
                        document.getElementById('nombre').value = p.nombre;
                        document.getElementById('categoria').value = p.categoria;
                        document.getElementById('descripcion').value = p.descripcion;
                        document.getElementById('precio').value = p.precio;
                        document.getElementById('stock').value = p.stock;
                        document.getElementById('unidad').value = p.unidad;
                        document.getElementById('proveedor').value = p.proveedor;
                        document.getElementById('imagen').value = p.imagen;
                        document.getElementById('es_equipo').checked = p.es_equipo == 1;
                        document.getElementById('activo').checked    = p.activo == 1;
                    } else {
                        showError(json.error || 'No se pudo cargar el producto');
                    }
                });
        } else {
            document.getElementById('productoModalLabel').textContent = 'Nuevo producto';
        }

        // mostrar modal
        const modalEl = document.getElementById('productoModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    /**
     * Guarda el producto (crea o actualiza).
     */
    function saveProduct() {
        const data = {
            nombre: document.getElementById('nombre').value.trim(),
            descripcion: document.getElementById('descripcion').value.trim(),
            categoria: document.getElementById('categoria').value.trim(),
            precio: parseFloat(document.getElementById('precio').value),
            stock: parseInt(document.getElementById('stock').value),
            unidad: document.getElementById('unidad').value.trim(),
            proveedor: document.getElementById('proveedor').value.trim(),
            imagen: document.getElementById('imagen').value.trim(),
            es_equipo: document.getElementById('es_equipo').checked ? 1 : 0,
            activo: document.getElementById('activo').checked ? 1 : 0
        };

        // Validaciones básicas
        if (!data.nombre) {
            showError('El nombre es obligatorio'); return;
        }
        if (!data.categoria) {
            showError('La categoría es obligatoria'); return;
        }
        if (isNaN(data.precio) || data.precio <= 0) {
            showError('Precio inválido'); return;
        }
        if (isNaN(data.stock) || data.stock < 0) {
            showError('Stock inválido'); return;
        }

        const formData = new FormData();
        // Agregar campos de texto
        Object.keys(data).forEach(k => formData.append(k, data[k]));
        // Agregar archivo de imagen si existe
        const fileInput = document.getElementById('imagenFile');
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            formData.append('imagenFile', fileInput.files[0]);
        }

        let url = '../../Controller/ProductController.php?action=create';
        if (currentEditId) {
            url = '../../Controller/ProductController.php?action=update&id=' + currentEditId;
        }

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                bootstrap.Modal.getInstance(document.getElementById('productoModal')).hide();
                loadProducts();
            } else {
                showError(json.error || 'Ocurrió un error');
            }
        })
        .catch(err => {
            showError(err.message || 'Ocurrió un error inesperado');
        });
    }

    /**
     * Muestra un mensaje de error en el formulario
     */
    function showError(msg) {
        const alert = document.getElementById('productoError');
        alert.textContent = msg;
        alert.classList.remove('d-none');
    }

    /**
     * Confirma y elimina un producto
     */
    function confirmDelete(id) {
        if (!confirm('¿Seguro que desea eliminar este producto?')) return;
        fetch('../../Controller/ProductController.php?action=delete&id=' + id)
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    loadProducts();
                } else {
                    alert(json.error || 'No se pudo eliminar');
                }
            })
            .catch(err => {
                alert('Error: ' + err.message);
            });
    }

    /**
     * Agrega un producto al carrito almacenado en localStorage. Si el producto ya existe,
     * incrementa su cantidad. Muestra una notificación breve.
     * @param {number} id
     */
    function addToCart(id) {
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem('jj_cart') || '[]');
        } catch (e) {
            cart = [];
        }
        const item = cart.find(it => it.id == id);
        if (item) {
            item.qty++;
        } else {
            cart.push({ id: id, qty: 1 });
        }
        localStorage.setItem('jj_cart', JSON.stringify(cart));
        // Actualizar contador del carrito
        updateCartCount();
        // Mostrar una tostada simple
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerText = 'Producto agregado al carrito';
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('visible'), 10);
        setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => toast.remove(), 300);
        }, 1500);
    }

    // Hacer disponible addToCart fuera del IIFE para que funcione
    // el botón 'Agregar' definido en el HTML de las tarjetas.
    window.addToCart = addToCart;
})();
</script>
</body>
</html>
