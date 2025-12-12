<?php
/*
    Vista: Gestión de Empleados
    - Permite a los administradores listar, buscar, crear, editar y eliminar
      registros de empleados. Se apoya en EmployeeController.php para
      realizar las operaciones CRUD vía AJAX y en EmployeeModel.php
      para acceder a la base de datos mediante procedimientos
      almacenados.

    Estructura general:
      * Solo accesible para perfiles de administrador (ConsecutivoPerfil=1).
      * Incluye buscador y botón para crear un empleado.
      * Lista empleados en tarjetas con nombre, puesto y salario.
      * Modal Bootstrap para crear/editar empleados.
*/

session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Redirección si el usuario no es administrador
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
    <link href="../css/empleados.css?v=1" rel="stylesheet" />
    <title>Empleados | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4">
                <!-- Encabezado -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Empleados</h2>
                        <p class="page-sub">Gestioná al personal de Distribuidora JJ</p>
                    </div>
                    <button id="btnNuevoEmpleado" class="btn btn-primary d-flex align-items-center">
                        <i class="fas fa-plus me-2"></i> Nuevo empleado
                    </button>
                </div>
                <!-- Buscador -->
                <div class="mb-3">
                    <input type="text" id="search" class="form-control" placeholder="Buscar empleado por nombre o puesto...">
                </div>
                <!-- Contenedor de empleados -->
                <div id="employeeList" class="row g-3"></div>
            </main>
            <?php showFooter(); ?>
        </div>
    </div>

    <!-- Modal para crear/editar empleado -->
    <div class="modal fade" id="empleadoModal" tabindex="-1" aria-labelledby="empleadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="empleadoForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="empleadoModalLabel">Nuevo Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="empId">
                        <div class="mb-3">
                            <label for="empNombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="empNombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="empPuesto" class="form-label">Puesto</label>
                            <select class="form-select" id="empPuesto" required>
                                <option value="">Seleccione...</option>
                                <option value="Cajero">Cajero</option>
                                <option value="Bodeguero">Bodeguero</option>
                                <option value="Alistador">Alistador</option>
                                <option value="Chofer">Chofer</option>
                                <option value="Contador">Contador</option>
                                <option value="TI">TI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="empSalario" class="form-label">Salario mensual (₡)</label>
                            <input type="number" class="form-control" id="empSalario" min="0" step="0.01" required>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="empActivo" checked>
                            <label class="form-check-label" for="empActivo">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php showJs(); ?>
    <script>
    (function() {
        let employees = [];
        const listEl = document.getElementById('employeeList');
        const searchEl = document.getElementById('search');
        const modalEl  = new bootstrap.Modal(document.getElementById('empleadoModal'));
        const formEl   = document.getElementById('empleadoForm');
        const empIdEl  = document.getElementById('empId');
        const empNombreEl = document.getElementById('empNombre');
        const empPuestoEl = document.getElementById('empPuesto');
        const empSalarioEl = document.getElementById('empSalario');
        const empActivoEl  = document.getElementById('empActivo');
        const titleEl = document.getElementById('empleadoModalLabel');

        // Cargar empleados desde el servidor
        function loadEmployees() {
            fetch('../../Controller/EmployeeController.php?action=list')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        employees = data.data;
                        renderEmployees();
                    }
                });
        }
        // Renderiza la lista según el filtro
        function renderEmployees() {
            const filter = searchEl.value.toLowerCase();
            listEl.innerHTML = '';
            const filtered = employees.filter(emp => {
                return emp.nombre.toLowerCase().includes(filter) || emp.puesto.toLowerCase().includes(filter);
            });
            if (filtered.length === 0) {
                listEl.innerHTML = '<p class="text-muted">No se encontraron empleados.</p>';
                return;
            }
            filtered.forEach(emp => {
                // Crear contenedor de tarjeta directamente sin envoltorio col-md-4 para aprovechar flexbox
                const card = document.createElement('div');
                card.className = 'employee-card';
                card.innerHTML = `
                    <div>
                        <div class="employee-name">${emp.nombre}</div>
                        <div class="employee-position">${emp.puesto}</div>
                        <div class="employee-salary">₡ ${parseFloat(emp.salario).toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                    </div>
                    <div class="employee-actions mt-3">
                        <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${emp.id}">
                            <i class="fas fa-edit me-1"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${emp.id}">
                            <i class="fas fa-trash me-1"></i> Eliminar
                        </button>
                    </div>
                `;
                listEl.appendChild(card);
            });
        }
        // Abrir modal para nuevo
        function openCreateModal() {
            empIdEl.value    = '';
            empNombreEl.value= '';
            empPuestoEl.value= '';
            empSalarioEl.value= '';
            empActivoEl.checked = true;
            titleEl.textContent = 'Nuevo Empleado';
            modalEl.show();
        }
        // Abrir modal para editar
        function openEditModal(id) {
            const emp = employees.find(e => e.id == id);
            if (!emp) return;
            empIdEl.value     = emp.id;
            empNombreEl.value = emp.nombre;
            empPuestoEl.value = emp.puesto;
            empSalarioEl.value= emp.salario;
            empActivoEl.checked= emp.activo == 1;
            titleEl.textContent = 'Editar Empleado';
            modalEl.show();
        }
        // Guardar (crear/actualizar)
        formEl.addEventListener('submit', function(e) {
            e.preventDefault();
            const payload = new FormData();
            payload.append('nombre', empNombreEl.value.trim());
            payload.append('puesto', empPuestoEl.value);
            payload.append('salario', empSalarioEl.value);
            payload.append('activo', empActivoEl.checked ? '1' : '0');
            let url  = '../../Controller/EmployeeController.php?action=create';
            if (empIdEl.value) {
                url = `../../Controller/EmployeeController.php?action=update&id=${empIdEl.value}`;
            }
            fetch(url, {
                method: 'POST',
                body: payload
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    modalEl.hide();
                    loadEmployees();
                } else {
                    alert(data.error || 'Error al guardar');
                }
            });
        });
        // Confirmar eliminación
        function confirmDelete(id) {
            if (!confirm('¿Seguro que deseas eliminar este empleado?')) return;
            fetch(`../../Controller/EmployeeController.php?action=delete&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loadEmployees();
                    } else {
                        alert(data.error || 'Error al eliminar');
                    }
                });
        }
        // Eventos
        document.getElementById('btnNuevoEmpleado').addEventListener('click', openCreateModal);
        searchEl.addEventListener('input', renderEmployees);
        listEl.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                openEditModal(id);
                return;
            }
            const delBtn = e.target.closest('.btn-delete');
            if (delBtn) {
                const id = delBtn.getAttribute('data-id');
                confirmDelete(id);
            }
        });
        // Inicializar
        loadEmployees();
    })();
    </script>
</body>
</html>