<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
        <div class="dropdown">
            <label for="menu-toggle" class="dropdown-label-admin">Opciones</label>
            <input type="checkbox" id="menu-toggle" />
        
            <div class="dropdown-menu">
                <a class="btn-table" href="/ProyectoPandora/Public/index.php?route=Admin/ListarUsers">Todos</a>
                <a class="btn-table" href="/ProyectoPandora/Public/index.php?route=Admin/ListarClientes">Clientes</a>
                <a class="btn-table" href="/ProyectoPandora/Public/index.php?route=Admin/ListarAdmins">Admin</a>
                <a class="btn-table" href="/ProyectoPandora/Public/index.php?route=Admin/ListarSupervisores">Supervisor</a>
                <a class="btn-table" href="/ProyectoPandora/Public/index.php?route=Admin/ListarTecnicos">Tecnico</a>
            </div>
        </div>
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Roles</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($administradores) && !empty($administradores)) {
                    foreach ($administradores as $admin) {
                        $role = htmlspecialchars($admin['role']);
                        echo "<tr class='row-role-".$role."'>";
                        echo "<td>".htmlspecialchars($admin['id'])."</td>";
                        echo "<td>".htmlspecialchars($admin['name'])."</td>";
                        echo "<td>".htmlspecialchars($admin['email'])."</td>";
                        echo "<td><span class='role ".$role."'>".$role."</span></td>";
                        echo "<td><span class='created-at'>🕒 <time title='".htmlspecialchars($admin['created_exact'] ?? '')."'>".htmlspecialchars($admin['created_human'] ?? '')."</time></span></td>";
                        echo "<td>
                                <div class='action-buttons'>
                                <a href='/ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id=".htmlspecialchars($admin['id'])."&from=Admin/ListarAdmins' class='btn edit-btn'>Actualizar</a>
                                    <a href='/ProyectoPandora/Public/index.php?route=Admin/DeleteUser&id=".htmlspecialchars($admin['id'])."' class='btn delete-btn' data-confirm='¿Eliminar este usuario?'>Eliminar</a>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay clientes registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>