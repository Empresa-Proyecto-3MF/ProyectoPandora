<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        
        <div class="botones">
            <div class="dropdown">
                <label for="menu-toggle" class="dropdown-label-admin">
                    Opciones<i class='bxr bx-caret-down'></i>
                </label>
                <input type="checkbox" id="menu-toggle" />
                <div class="dropdown-menu">
                    <a class="btn-table" href="index.php?route=Admin/ListarUsers">Todos</a>
                    <a class="btn-table" href="index.php?route=Admin/ListarClientes">Clientes</a>
                    <a class="btn-table" href="index.php?route=Admin/ListarAdmins">Admin</a>
                    <a class="btn-table" href="index.php?route=Admin/ListarSupervisores">Supervisor</a>
                    <a class="btn-table" href="index.php?route=Admin/ListarTecnicos">Técnico</a>
                </div>
            </div>
            <div class="btn-table-acciones">
                <a class="btn-acciones-user" href="index.php?route=Register/RegisterAdmin">Añadir Usuario</a>
            </div>
        </div>

        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha de creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($users) {
                    foreach ($users as $user) {
                        $role = htmlspecialchars($user['role']);
                        echo "<tr>";
                        echo "<td data-label='ID'>" . htmlspecialchars($user['id']) . "</td>";
                        echo "<td data-label='Nombre'>" . htmlspecialchars($user['name']) . "</td>";
                        echo "<td data-label='Correo'>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td data-label='Rol'><span class='role $role'>$role</span></td>";
                        echo "<td data-label='Fecha de creación'><span class='created-at'>🕒 <time title='" . htmlspecialchars($user['created_exact'] ?? '') . "'>" . htmlspecialchars($user['created_human'] ?? '') . "</time></span></td>";
                        echo "<td data-label='Acciones'>";
                        echo "<div class='action-buttons'>";
                        echo "<a href='index.php?route=Admin/ActualizarUser&id=" . htmlspecialchars($user['id']) . "&from=Admin/ListarUsers' class='btn edit-btn'>Actualizar</a> | ";
                        echo "<a href='index.php?route=Admin/DeleteUser&id=" . htmlspecialchars($user['id']) . "' class='btn delete-btn' data-confirm='¿Eliminar este usuario?'>Eliminar</a>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay usuarios registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<script src="js/Tablas.js"></script>
<script src="js/DarkMode.js?v=<?= time(); ?>" defer></script>
