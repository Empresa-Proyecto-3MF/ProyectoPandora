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
            <label for="menu-toggle" class="dropdown-label">Opciones</label>
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
                if ($clientes) {
                    foreach ($clientes as $cliente) {
                       $role = htmlspecialchars($cliente['role']);
                        echo "<tr class='row-role-$role'>";
                        echo "<td>".htmlspecialchars($cliente['id'])."</td>";
                        echo "<td>".htmlspecialchars($cliente['name'])."</td>";
                        echo "<td>".htmlspecialchars($cliente['email'])."</td>";
                        echo "<td><span class='role $role'>$role</span></td>";
                        echo "<td><span class='created-at'>🕒 <time title='".htmlspecialchars($cliente['created_exact'] ?? '')."'>".htmlspecialchars($cliente['created_human'] ?? '')."</time></span></td>";
                        $userId = (int)($cliente['user_id'] ?? $cliente['id']);
                        echo "<td>  
                                <div class='action-buttons'>
                                    <a href='/ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id=".$userId."&from=Admin/ListarClientes' class='btn edit-btn'>Actualizar</a>
                                    |
                                    <a href='/ProyectoPandora/Public/index.php?route=Admin/DeleteUser&id=".$userId."' class='btn delete-btn' data-confirm='¿Eliminar este usuario?'>Eliminar</a>
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