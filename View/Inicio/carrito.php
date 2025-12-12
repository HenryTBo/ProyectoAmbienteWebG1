<?php
/*
    Vista: Carrito de compras
    - Muestra los productos agregados al carrito desde localStorage (jj_cart).
    - Permite modificar la cantidad, eliminar artículos y procesar el pedido.
    - Al finalizar la compra se envía al OrderController para crear un pedido.
*/
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Solo usuarios autenticados pueden ver el carrito
if (!isset($_SESSION["ConsecutivoUsuario"])) {
    header("Location: InicioSesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/pedidos.css?v=2" rel="stylesheet">
    <style>
        /* Estilos adicionales para la tabla del carrito */
        #cartTable thead {
            background-color: var(--jj-blue-deep);
            color: var(--jj-cream-light);
        }
        #cartTable td, #cartTable th {
            vertical-align: middle;
        }
        .cart-qty {
            max-width: 80px;
        }
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
        .toast.visible { opacity: 1; }
    </style>
    <title>Mi carrito | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4 container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Mi carrito</h2>
                        <p class="page-sub">Revisa los artículos que has agregado</p>
                    </div>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-striped" id="cartTable">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <h5>Total: <span id="cartTotal">₡ 0</span></h5>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="productos.php" class="btn btn-secondary me-2">Seguir comprando</a>
                    <button id="btnFinalizar" class="btn btn-primary">Finalizar pedido</button>
                </div>
            </main>
            <?php showFooter(); ?>
        </div>
    </div>
    <?php showJs(); ?>
    <script>
    (function(){
        let products = [];
        let cart = [];
        const tbody = document.querySelector('#cartTable tbody');
        const totalEl = document.getElementById('cartTotal');
        const btnFinalizar = document.getElementById('btnFinalizar');

        function loadCatalogo() {
            return fetch('../../Controller/ProductController.php?action=list')
                .then(r => r.json())
                .then(json => json.success ? json.data : []);
        }
        function loadCart() {
            try {
                cart = JSON.parse(localStorage.getItem('jj_cart') || '[]');
            } catch (e) {
                cart = [];
            }
        }
        function saveCart() {
            localStorage.setItem('jj_cart', JSON.stringify(cart));
        }
        function renderCart() {
            tbody.innerHTML = '';
            let total = 0;
            if (cart.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tu carrito está vacío.</td></tr>';
                totalEl.textContent = '₡ 0';
                return;
            }
            cart.forEach((item, idx) => {
                const p = products.find(pr => pr.id == item.id);
                if (!p) return;
                const subtotal = p.precio * item.qty;
                total += subtotal;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.nombre}</td>
                    <td>₡ ${parseFloat(p.precio).toLocaleString('es-CR', {minimumFractionDigits:0})}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm cart-qty" data-index="${idx}" value="${item.qty}" min="1" />
                    </td>
                    <td>₡ ${parseFloat(subtotal).toLocaleString('es-CR', {minimumFractionDigits:0})}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger btn-remove" data-index="${idx}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            totalEl.textContent = '₡ ' + total.toLocaleString('es-CR', {minimumFractionDigits:0});
        }
        function updateQuantity(index, qty) {
            qty = parseInt(qty);
            if (isNaN(qty) || qty < 1) qty = 1;
            cart[index].qty = qty;
            saveCart();
            renderCart();
        }
        function removeItem(index) {
            cart.splice(index, 1);
            saveCart();
            renderCart();
        }
        function finalizarPedido() {
            if (cart.length === 0) {
                alert('Tu carrito está vacío');
                return;
            }
            // Crear array de items con id_producto y cantidad
            const items = cart.map(it => ({ id_producto: it.id, cantidad: it.qty }));
            const formData = new FormData();
            formData.append('items', JSON.stringify(items));
            fetch('../../Controller/OrderController.php?action=create', {
                method: 'POST',
                body: formData
            }).then(r => r.json())
              .then(json => {
                  if (json.success) {
                      // Limpiar carrito y mostrar toast
                      localStorage.removeItem('jj_cart');
                      const t = document.createElement('div');
                      t.className = 'toast';
                      t.innerText = 'Pedido generado correctamente';
                      document.body.appendChild(t);
                      setTimeout(() => t.classList.add('visible'), 10);
                      setTimeout(() => {
                          t.classList.remove('visible');
                          setTimeout(() => t.remove(), 300);
                          window.location.href = 'misPedidos.php';
                      }, 1500);
                  } else {
                      alert(json.error || 'No se pudo crear el pedido');
                  }
              })
              .catch(err => alert('Error al crear el pedido: ' + err.message));
        }
        // Eventos del DOM
        tbody.addEventListener('input', function(e) {
            const inp = e.target.closest('input.cart-qty');
            if (inp) {
                const index = parseInt(inp.getAttribute('data-index'));
                updateQuantity(index, inp.value);
            }
        });
        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove');
            if (btn) {
                const index = parseInt(btn.getAttribute('data-index'));
                removeItem(index);
            }
        });
        btnFinalizar.addEventListener('click', finalizarPedido);
        // Inicializar catálogo y carrito
        loadCatalogo().then(data => {
            products = data;
            loadCart();
            renderCart();
        });
    })();
    </script>
</body>
</html>