<?php
/*
    Vista: Gestión de Cuentas de Usuario
    - Permite a los administradores ver el listado de cuentas
      (identificación, nombre, correo y rol) y actualizar el
      perfil (rol) de cada usuario.
    - Utiliza UserAdminController.php para las operaciones.
    - Se aplica la paleta corporativa para la tabla y botones.
*/

session_start();
include_once __DIR__ . '/../layoutInterno.php';

// Solo administradores pueden acceder
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
    <link href="../css/usuarios.css?v=1" rel="stylesheet" />
    <title>Cuentas | Distribuidora JJ</title>
</head>
<body class="sb-nav-fixed">
    <?php showNavBar(); ?>
    <div id="layoutSidenav">
        <?php showSideBar(); ?>
        <div id="layoutSidenav_content">
            <main class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="page-title">Cuentas de usuario</h2>
                        <p class="page-sub">Administra los roles de las cuentas registradas</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="usuariosTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Identificación</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
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

    <?php showJs(); ?>
    <script>
    (function() {
        let usuarios = [];
        const tbody = document.querySelector('#usuariosTable tbody');

        function loadUsers() {
            fetch('../../Controller/UserAdminController.php?action=list')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        usuarios = data.data;
                        renderTable();
                    }
                });
        }
        function renderTable() {
            tbody.innerHTML = '';
            usuarios.forEach((u, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${idx + 1}</td>
                    <td>${u.Identificacion}</td>
                    <td>${u.Nombre}</td>
                    <td>${u.CorreoElectronico}</td>
                    <td>
                        <select class="form-select form-select-sm role-select" data-id="${u.ConsecutivoUsuario}">
                            <option value="1" ${u.ConsecutivoPerfil == 1 ? 'selected' : ''}>Administrador</option>
                            <option value="2" ${u.ConsecutivoPerfil == 2 ? 'selected' : ''}>Usuario</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-save-role" data-id="${u.ConsecutivoUsuario}">
                            Guardar
                        </button>
                    </td>
                `;
                // No permitir que el mismo admin cambie su rol a sí mismo
                if (parseInt(u.ConsecutivoUsuario) === parseInt(<?php echo json_encode($_SESSION['ConsecutivoUsuario']); ?>)) {
                    const selectEl = tr.querySelector('.role-select');
                    selectEl.disabled = true;
                    const btn = tr.querySelector('.btn-save-role');
                    btn.disabled = true;
                    btn.classList.add('disabled');
                }
                tbody.appendChild(tr);
            });
        }
        function updateRole(userId, perfil) {
            const form = new FormData();
            form.append('id', userId);
            form.append('perfil', perfil);
            fetch('../../Controller/UserAdminController.php?action=updateRole', {
                method: 'POST',
                body: form
            }).then(r => r.json()).then(data => {
                if (!data.success) {
                    alert(data.error || 'Error al actualizar el rol');
                } else {
                    loadUsers();
                }
            });
        }
        // Evento para guardar rol
        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-save-role');
            if (btn) {
                const id = btn.getAttribute('data-id');
                const select = tbody.querySelector(`.role-select[data-id="${id}"]`);
                if (select) {
                    updateRole(id, select.value);
                }
            }
        });
        // Inicializar
        loadUsers();
    })();
    </script>
</body>
</html>