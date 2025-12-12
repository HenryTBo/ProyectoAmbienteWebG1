<?php
/*
    Vista: Mis Pedidos (Usuario)
    - Muestra al usuario autenticado la lista de sus pedidos con fecha, estado y total.
    - Permite ver el detalle de cada pedido en un modal.
    - Incluye botón para ir a crear un nuevo pedido.
*/

session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Solo usuarios autenticados pueden ver esta página
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
    <title>Mis pedidos | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Mis pedidos</h2>
                        <p class="page-sub">Consulta el historial y estado de tus órdenes</p>
                    </div>
                    <a href="crearPedido.php" class="btn btn-primary d-flex align-items-center">
                        <i class="fas fa-plus me-2"></i> Nuevo pedido
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="pedidosTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </main>
            <?php showFooter(); ?>
        </div>
    </div>

    <!-- Modal Detalle Pedido -->
    <div class="modal fade" id="pedidoDetalleModal" tabindex="-1" aria-labelledby="pedidoDetalleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pedidoDetalleModalLabel">Detalle del pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleBody"></div>
                </div>
            </div>
        </div>
    </div>

    <?php showJs(); ?>
    <script>
    (function() {
        const tbody = document.querySelector('#pedidosTable tbody');
        const detalleModal = new bootstrap.Modal(document.getElementById('pedidoDetalleModal'));
        const detalleBody = document.getElementById('detalleBody');
        let pedidos = [];

        function loadPedidos() {
            fetch('../../Controller/OrderController.php?action=my')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        pedidos = data.data;
                        renderTable();
                    }
                });
        }
        function renderTable() {
            tbody.innerHTML = '';
            pedidos.forEach((p, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${(new Date(p.fecha)).toLocaleString('es-CR')}</td>
                    <td>${p.estado}</td>
                    <td>₡ ${parseFloat(p.total).toLocaleString('es-CR', {minimumFractionDigits: 0})}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-ver" data-id="${p.id}">Ver</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
        function verDetalle(id) {
            fetch(`../../Controller/OrderController.php?action=view&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const pedido = data.data.pedido;
                        const detalle = data.data.detalle;
                        let html = `<p><strong>Pedido #${pedido.id}</strong></p>`;
                        html += `<p><strong>Fecha:</strong> ${(new Date(pedido.fecha)).toLocaleString('es-CR')}</p>`;
                        html += `<p><strong>Estado:</strong> ${pedido.estado}</p>`;
                        html += `<p><strong>Total:</strong> ₡ ${parseFloat(pedido.total).toLocaleString('es-CR', {minimumFractionDigits: 0})}</p>`;
                        html += '<hr><h6>Artículos</h6>';
                        html += '<ul class="list-group">';
                        detalle.forEach(item => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    ${item.nombreProducto} <br>
                                    <small class="text-muted">Cantidad: ${item.cantidad}</small>
                                </span>
                                <span>₡ ${parseFloat(item.precio * item.cantidad).toLocaleString('es-CR', {minimumFractionDigits: 0})}</span>
                            </li>`;
                        });
                        html += '</ul>';
                        detalleBody.innerHTML = html;
                        detalleModal.show();
                    }
                });
        }
        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-ver');
            if (btn) {
                const id = btn.getAttribute('data-id');
                verDetalle(id);
            }
        });
        // Init
        loadPedidos();
    })();
    </script>
</body>
</html>