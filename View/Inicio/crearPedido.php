<?php
/*
    Vista: Crear Pedido (Usuario)
    - Permite que un usuario autenticado genere un nuevo pedido eligiendo
      productos y cantidades.
    - Utiliza ProductController.php para obtener el catálogo y OrderController.php
      para guardar el pedido. Después de crear el pedido, redirige a MisPedidos.
*/

session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Redirección si el usuario no está autenticado
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
    <link href="../css/pedidos.css?v=1" rel="stylesheet" />
    <title>Nuevo pedido | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Nuevo pedido</h2>
                        <p class="page-sub">Seleccioná productos y cantidades para tu orden</p>
                    </div>
                </div>
                <form id="pedidoForm">
                    <div class="table-responsive mb-3">
                        <table class="table align-middle" id="orderItemsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:40%">Producto</th>
                                    <th style="width:20%">Precio</th>
                                    <th style="width:20%">Cantidad</th>
                                    <th style="width:20%">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button type="button" id="btnAddItem" class="btn btn-outline-primary mb-3">
                        <i class="fas fa-plus me-1"></i> Agregar producto
                    </button>
                    <div class="d-flex justify-content-end mb-3">
                        <h5>Total: <span id="totalDisplay">₡ 0</span></h5>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="misPedidos.php" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Crear pedido</button>
                    </div>
                </form>
            </main>
            <?php showFooter(); ?>
        </div>
    </div>

    <?php showJs(); ?>
    <script>
    (function() {
        let products = [];
        const tableBody = document.querySelector('#orderItemsTable tbody');
        const totalDisplay = document.getElementById('totalDisplay');

        // Cargar productos del catálogo
        function loadProducts() {
            fetch('../../Controller/ProductController.php?action=list')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        products = data.data;
                        addRow();
                    }
                });
        }
        // Crear una fila de pedido
        function addRow() {
            const row = document.createElement('tr');
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm product-select';
            select.innerHTML = '<option value="">Seleccione producto</option>';
            products.forEach(p => {
                // Solo productos activos
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.nombre} (${p.categoria})`;
                opt.dataset.precio = p.precio;
                select.appendChild(opt);
            });
            const priceSpan = document.createElement('span');
            priceSpan.className = 'product-price';
            priceSpan.textContent = '₡ 0';
            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.min = '1';
            qtyInput.value = '1';
            qtyInput.className = 'form-control form-control-sm qty-input';
            const subSpan = document.createElement('span');
            subSpan.className = 'sub-total';
            subSpan.textContent = '₡ 0';
            const btnRemove = document.createElement('button');
            btnRemove.type = 'button';
            btnRemove.className = 'btn btn-sm btn-outline-danger btn-remove';
            btnRemove.innerHTML = '<i class="fas fa-trash"></i>';

            row.appendChild(createTd(select));
            row.appendChild(createTd(priceSpan));
            row.appendChild(createTd(qtyInput));
            row.appendChild(createTd(subSpan));
            row.appendChild(createTd(btnRemove));
            tableBody.appendChild(row);
            // Actualizar valores cuando cambie producto o cantidad
            select.addEventListener('change', () => {
                updateRow(row);
                updateTotal();
            });
            qtyInput.addEventListener('input', () => {
                if (parseInt(qtyInput.value) < 1) qtyInput.value = 1;
                updateRow(row);
                updateTotal();
            });
            btnRemove.addEventListener('click', () => {
                row.remove();
                updateTotal();
            });
        }
        function createTd(content) {
            const td = document.createElement('td');
            if (content instanceof HTMLElement) td.appendChild(content); else td.innerHTML = content;
            return td;
        }
        // Actualizar una fila: precio unitario y subtotal
        function updateRow(row) {
            const select = row.querySelector('.product-select');
            const priceEl = row.querySelector('.product-price');
            const qtyInput = row.querySelector('.qty-input');
            const subEl = row.querySelector('.sub-total');
            const pid = parseInt(select.value);
            if (!pid) {
                priceEl.textContent = '₡ 0';
                subEl.textContent   = '₡ 0';
                return;
            }
            const precio = parseFloat(select.options[select.selectedIndex].dataset.precio);
            const qty    = parseInt(qtyInput.value);
            priceEl.textContent = `₡ ${precio.toLocaleString('es-CR', {minimumFractionDigits: 0})}`;
            const subtotal = precio * qty;
            subEl.textContent = `₡ ${subtotal.toLocaleString('es-CR', {minimumFractionDigits: 0})}`;
        }
        // Actualizar total en base a filas
        function updateTotal() {
            let total = 0;
            tableBody.querySelectorAll('tr').forEach(row => {
                const select = row.querySelector('.product-select');
                const qtyInput = row.querySelector('.qty-input');
                const pid = parseInt(select.value);
                if (!pid) return;
                const precio = parseFloat(select.options[select.selectedIndex].dataset.precio);
                const qty    = parseInt(qtyInput.value);
                total += precio * qty;
            });
            totalDisplay.textContent = `₡ ${total.toLocaleString('es-CR', {minimumFractionDigits: 0})}`;
        }
        // Manejar submit
        document.getElementById('pedidoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const items = [];
            let valid = true;
            tableBody.querySelectorAll('tr').forEach(row => {
                const select = row.querySelector('.product-select');
                const qtyInput = row.querySelector('.qty-input');
                const pid = parseInt(select.value);
                const qty = parseInt(qtyInput.value);
                if (pid && qty > 0) {
                    items.push({id_producto: pid, cantidad: qty});
                } else {
                    valid = false;
                }
            });
            if (items.length === 0 || !valid) {
                alert('Debes seleccionar al menos un producto y una cantidad válida');
                return;
            }
            const formData = new FormData();
            formData.append('items', JSON.stringify(items));
            fetch('../../Controller/OrderController.php?action=create', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    alert('Pedido creado correctamente');
                    window.location.href = 'misPedidos.php';
                } else {
                    alert(data.error || 'No se pudo crear el pedido');
                }
            });
        });
        document.getElementById('btnAddItem').addEventListener('click', () => {
            addRow();
        });
        // Inicializar
        loadProducts();
    })();
    </script>
</body>
</html>