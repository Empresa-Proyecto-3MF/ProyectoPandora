<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        
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
                if ($supervisor) {
                    foreach ($supervisor as $super) {
                        $role = htmlspecialchars($super['role']);
                        echo "<tr class='row-role-".$role."'>";
                        echo "<td>".htmlspecialchars($super['id'])."</td>";
                        echo "<td>".htmlspecialchars($super['name'])."</td>";
                        echo "<td>".htmlspecialchars($super['email'])."</td>";
                        echo "<td><span class='role ".$role."'>".$role."</span></td>";
                        echo "<td><span class='created-at'>🕒 <time title='".htmlspecialchars($super['created_exact'] ?? '')."'>".htmlspecialchars($super['created_human'] ?? '')."</time></span></td>";
                        echo "<td>
                            <div class='action-buttons'>
                                <a href='/ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id=".htmlspecialchars($super['id'])."&from=Admin/ListarSupers' class='btn edit-btn'>Actualizar</a>
                                |
                                <a href='/ProyectoPandora/Public/index.php?route=Admin/DeleteUser&id=".htmlspecialchars($super['id'])."' class='btn delete-btn' data-confirm='¿Eliminar este usuario?'>Eliminar</a>
                            </div>
                        </td>";
                        echo "</tr>";
                    }
                } else {

                    echo "<tr><td colspan='6'>No hay supervisores registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>