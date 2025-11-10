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
                    <th>Disponibilidad</th>
                    <th>Honor (★)</th>
                    <th>Especialización</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($tecnicos) {
                    foreach ($tecnicos as $tec) {
                        $role = htmlspecialchars($tec['role']);
                        echo "<tr class='row-role-$role'>";
                        echo "<td>" . htmlspecialchars($tec['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($tec['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($tec['email']) . "</td>";
                        echo "<td><span class='role $role'>$role</span></td>";
                        
                        $dispRaw = $tec['disponibilidad'] ?? '';
                        $dispTxt = (strcasecmp($dispRaw, 'Ocupado') === 0) ? 'No disponible' : ($dispRaw ?: '—');
                        $dispClass = (strcasecmp($dispRaw, 'Disponible') === 0) ? 'badge badge--success' : ((strcasecmp($dispRaw, 'Ocupado') === 0) ? 'badge badge--danger' : 'badge badge--muted');
                        echo "<td><span class='".$dispClass."'>".htmlspecialchars($dispTxt)."</span></td>";
                        
                        $avg = isset($tec['rating_avg']) ? (float)$tec['rating_avg'] : 0.0;
                        $count = (int)($tec['rating_count'] ?? 0);
                        if ($count === 0 && $avg <= 0) { $avg = 3.0; }
                        $full = (int)floor($avg);
                        $half = ($avg - $full) >= 0.5;
                        echo "<td><span title='Promedio: ".round($avg,1)." (".$count." califs)' style='color:#f5c518;'>";
                        for ($i=1;$i<=5;$i++) {
                            if ($i <= $full) echo "★"; else echo "☆";
                        }
                        echo "</span> <small>(".round($avg,1).", ".$count.")</small></td>";
                        echo "<td>" . htmlspecialchars($tec['especialidad']) . "</td>";
                        echo "<td><span class='created-at'>🕒 <time title='".htmlspecialchars($tec['created_exact'] ?? '')."'>".htmlspecialchars($tec['created_human'] ?? '')."</time></span></td>";
                        $userId = (int)($tec['user_id'] ?? 0);
                        echo "<td>
                            <div class='action-buttons'>
                                <a href='/ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id=" . htmlspecialchars($userId) . "&from=Admin/ListarTecnicos' class='btn edit-btn'>Actualizar</a>
                                |
                                <a href='/ProyectoPandora/Public/index.php?route=Admin/DeleteUser&id=" . htmlspecialchars($userId) . "' class='btn delete-btn' data-confirm='¿Eliminar este usuario?'>Eliminar</a>
                            </div>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No hay técnicos registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>