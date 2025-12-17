<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Solo admin
if (!isset($_SESSION["ConsecutivoPerfil"]) || (int)$_SESSION["ConsecutivoPerfil"] !== 1) {
    header("Location: Principal.php");
    exit;
}

/*
  Raíz robusta:
  Si SCRIPT_NAME contiene "/View/", se toma todo lo anterior como raíz del proyecto.
*/
$script = $_SERVER['SCRIPT_NAME'];
$posView = strpos($script, '/View/');
if ($posView !== false) {
    $root = substr($script, 0, $posView);
} else {
    $root = rtrim(dirname(dirname(dirname($script))), '/');
}

$API_URL = $root . '/Controller/EmployeeController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/empleados.css?v=3" rel="stylesheet" />
    <title>Empleados | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
<?php showNavBar(); ?>
<div id="layoutSidenav">
    <?php showSideBar(); ?>
    <div id="layoutSidenav_content">
        <main class="p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="page-title">Empleados</h2>
                    <p class="page-sub">Gestión de personal</p>
                </div>
                <button id="btnNuevoEmpleado" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-plus me-2"></i> Nuevo empleado
                </button>
            </div>

            <div id="alertBox" class="alert d-none" role="alert"></div>

            <div class="mb-3">
                <input type="text" id="search" class="form-control" placeholder="Buscar empleado por nombre o puesto...">
            </div>

            <div id="employeeList"></div>
        </main>

        <?php showFooter(); ?>
    </div>
</div>

<!-- Modal -->
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
                            <option value="Bodeguera">Bodeguera</option>
                            <option value="Bodeguero">Bodeguero</option>
                            <option value="Alistador">Alistador</option>
                            <option value="Chofer">Chofer</option>
                            <option value="Conductor">Conductor</option>
                            <option value="Contadora">Contadora</option>
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
    const API = <?php echo json_encode($API_URL, JSON_UNESCAPED_SLASHES); ?>;

    let employees = [];

    const listEl   = document.getElementById('employeeList');
    const searchEl = document.getElementById('search');
    const alertBox = document.getElementById('alertBox');

    const modalEl  = new bootstrap.Modal(document.getElementById('empleadoModal'));
    const formEl   = document.getElementById('empleadoForm');

    const empIdEl      = document.getElementById('empId');
    const empNombreEl  = document.getElementById('empNombre');
    const empPuestoEl  = document.getElementById('empPuesto');
    const empSalarioEl = document.getElementById('empSalario');
    const empActivoEl  = document.getElementById('empActivo');
    const titleEl      = document.getElementById('empleadoModalLabel');

    function showAlert(msg, type = 'danger') {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        alertBox.classList.add('d-none');
        alertBox.textContent = '';
    }

    async function apiGet(url) {
        const r = await fetch(url, { credentials: 'same-origin' });
        const t = await r.text();
        try { return JSON.parse(t); }
        catch (e) { throw new Error("Respuesta no válida del servidor: " + t); }
    }

    async function apiPost(url, formData) {
        const r = await fetch(url, { method:'POST', body: formData, credentials:'same-origin' });
        const t = await r.text();
        try { return JSON.parse(t); }
        catch (e) { throw new Error("Respuesta no válida del servidor: " + t); }
    }

    async function loadEmployees() {
        hideAlert();
        const data = await apiGet(`${API}?action=list`);
        if (!data.success) throw new Error(data.error || 'No se pudo cargar empleados');
        employees = Array.isArray(data.data) ? data.data : [];
        renderEmployees();
    }

    function renderEmployees() {
        const filter = (searchEl.value || '').toLowerCase().trim();
        listEl.innerHTML = '';

        const filtered = employees.filter(emp => {
            const n = (emp.nombre || '').toLowerCase();
            const p = (emp.puesto || '').toLowerCase();
            return n.includes(filter) || p.includes(filter);
        });

        if (filtered.length === 0) {
            listEl.innerHTML = '<p class="text-muted">No se encontraron empleados.</p>';
            return;
        }

        filtered.forEach(emp => {
            const card = document.createElement('div');
            card.className = 'employee-card';

            const salario = (Number(emp.salario) || 0).toLocaleString('es-CR', { minimumFractionDigits: 0 });

            card.innerHTML = `
                <div>
                    <div class="employee-name">${escapeHtml(emp.nombre || '')}</div>
                    <div class="employee-position">${escapeHtml(emp.puesto || '')}</div>
                    <div class="employee-salary">₡ ${salario}</div>
                </div>
                <div class="employee-actions mt-3">
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${emp.id}">
                        Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${emp.id}">
                        Eliminar
                    </button>
                </div>
            `;
            listEl.appendChild(card);
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }

    function openCreateModal() {
        empIdEl.value = '';
        empNombreEl.value = '';
        empPuestoEl.value = '';
        empSalarioEl.value = '';
        empActivoEl.checked = true;
        titleEl.textContent = 'Nuevo Empleado';
        modalEl.show();
    }

    function openEditModal(id) {
        const emp = employees.find(e => String(e.id) === String(id));
        if (!emp) return;
        empIdEl.value = emp.id;
        empNombreEl.value = emp.nombre || '';
        empPuestoEl.value = emp.puesto || '';
        empSalarioEl.value = emp.salario || '';
        empActivoEl.checked = String(emp.activo) === '1';
        titleEl.textContent = 'Editar Empleado';
        modalEl.show();
    }

    formEl.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideAlert();

        const payload = new FormData();
        payload.append('nombre', (empNombreEl.value || '').trim());
        payload.append('puesto', empPuestoEl.value || '');
        payload.append('salario', empSalarioEl.value || '0');
        payload.append('activo', empActivoEl.checked ? '1' : '0');

        try {
            let url = `${API}?action=create`;
            if (empIdEl.value) url = `${API}?action=update&id=${encodeURIComponent(empIdEl.value)}`;

            const data = await apiPost(url, payload);
            if (!data.success) throw new Error(data.error || 'Error al guardar');

            modalEl.hide();
            await loadEmployees();
            showAlert('Empleado guardado correctamente.', 'success');

        } catch (err) {
            showAlert(err.message || 'Error al guardar', 'danger');
        }
    });

    async function confirmDelete(id) {
        if (!confirm('¿Seguro que deseas eliminar este empleado?')) return;
        hideAlert();
        try {
            const data = await apiGet(`${API}?action=delete&id=${encodeURIComponent(id)}`);
            if (!data.success) throw new Error(data.error || 'Error al eliminar');
            await loadEmployees();
            showAlert('Empleado eliminado.', 'success');
        } catch (err) {
            showAlert(err.message || 'Error al eliminar', 'danger');
        }
    }

    document.getElementById('btnNuevoEmpleado').addEventListener('click', openCreateModal);
    searchEl.addEventListener('input', renderEmployees);

    listEl.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) return openEditModal(editBtn.getAttribute('data-id'));

        const delBtn = e.target.closest('.btn-delete');
        if (delBtn) return confirmDelete(delBtn.getAttribute('data-id'));
    });

    loadEmployees().catch(err => showAlert(err.message || 'No se pudieron cargar los empleados.'));
})();
</script>
</body>
</html>
