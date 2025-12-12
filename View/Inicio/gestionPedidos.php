<?php
/*
    Vista: Gestión de pedidos (Administrador)
    - Lista todos los pedidos con información del cliente y estado.
    - Permite ver el detalle de cada pedido en un modal.
    - Permite actualizar el estado de cada pedido mediante un selector.
*/

session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Sólo admins pueden gestionar los pedidos
if (!isset($_SESSION["ConsecutivoPerfil"]) || $_SESSION["ConsecutivoPerfil"] != "1") {
    header("Location: Principal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/pedidos.css?v=1" rel="stylesheet" />
    <title>Gestión de pedidos | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Gestión de pedidos</h2>
                        <p class="page-sub">Revisa las órdenes de compra y actualiza su estado</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="gestionPedidosTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
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

    <!-- Modal Detalle Pedido (mismo que en misPedidos) -->
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
        const tbody = document.querySelector('#gestionPedidosTable tbody');
        const detalleModal = new bootstrap.Modal(document.getElementById('pedidoDetalleModal'));
        const detalleBody = document.getElementById('detalleBody');
        let pedidos = [];
        const estados = ['Pendiente', 'En proceso', 'En camino', 'Entregado', 'Cancelado'];

        function loadPedidos() {
            fetch('../../Controller/OrderController.php?action=list')
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
            pedidos.forEach(p => {
                const tr = document.createElement('tr');
                const options = estados.map(est => `<option value="${est}" ${p.estado === est ? 'selected' : ''}>${est}</option>`).join('');
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.nombreUsuario}</td>
                    <td>${(new Date(p.fecha)).toLocaleString('es-CR')}</td>
                    <td>
                        <select class="form-select form-select-sm estado-select" data-id="${p.id}">
                            ${options}
                        </select>
                    </td>
                    <td>₡ ${parseFloat(p.total).toLocaleString('es-CR', {minimumFractionDigits: 0})}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-ver" data-id="${p.id}">Ver</button>
                        <button class="btn btn-sm btn-outline-success btn-guardar" data-id="${p.id}">Guardar</button>
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
                        html += `<p><strong>Cliente:</strong> ${pedido.nombreUsuario}</p>`;
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
        function guardarEstado(id, nuevoEstado) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('estado', nuevoEstado);
            fetch('../../Controller/OrderController.php?action=updateStatus', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    alert('Estado actualizado');
                    loadPedidos();
                } else {
                    alert(data.error || 'No se pudo actualizar');
                }
            });
        }
        tbody.addEventListener('click', function(e) {
            const verBtn = e.target.closest('.btn-ver');
            if (verBtn) {
                const id = verBtn.getAttribute('data-id');
                verDetalle(id);
                return;
            }
            const guardarBtn = e.target.closest('.btn-guardar');
            if (guardarBtn) {
                const id = guardarBtn.getAttribute('data-id');
                const select = tbody.querySelector(`.estado-select[data-id="${id}"]`);
                if (select) {
                    guardarEstado(id, select.value);
                }
            }
        });
        // Inicializar
        loadPedidos();
    })();
    </script>
</body>
</html>