<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="actualizar-wrapper animated bounceInUp">
            <h3>Actualizar Usuario</h3>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
                <div class="alert alert-warning" role="alert" style="margin-bottom:10px;">El nombre es obligatorio.</div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'RolInvalido'): ?>
                <div class="alert alert-warning" role="alert" style="margin-bottom:10px;">Seleccioná un rol válido.</div>
            <?php endif; ?>

            <?php if (empty($user)): ?>
                <p>Usuario no encontrado.</p>
            <?php else: ?>
            <?php $isSelf = isset($_SESSION['user']['id']) && isset($user['id']) && ((int)$_SESSION['user']['id'] === (int)$user['id']); ?>
            <form method="POST" action="">
                <input type="hidden" name="from" value="<?= $_GET['from'] ?? 'Admin/ListarUsers' ?>">
                <input type="hidden" name="id" value="<?= $user['id'] ?? '' ?>">

                <label>Nombre</label>
                <input type="text" name="name" value="<?= htmlspecialchars(trim($user['name'] ?? '')) ?>" required>

                <label>Rol</label>
                <?php if ($isSelf): ?>
                    <small style="display:block; margin:4px 0 8px; color:#d97706;">No podés cambiar tu propio rol mientras estás logueado. Si lo hacés por otra vía, se cerrará tu sesión para aplicar el cambio.</small>
                <?php endif; ?>
                <select name="role" required <?= $isSelf ? 'disabled' : '' ?>>
                    <option value="Cliente" <?= ($user['role'] ?? '') === 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="Tecnico" <?= ($user['role'] ?? '') === 'Tecnico' ? 'selected' : '' ?>>Técnico</option>
                    <option value="Supervisor" <?= ($user['role'] ?? '') === 'Supervisor' ? 'selected' : '' ?>>Supervisor</option>
                    <option value="Administrador" <?= ($user['role'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>
                <?php if ($isSelf): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($user['role'] ?? '') ?>" />
                <?php endif; ?>

                <button type="submit">Guardar</button>
            </form>
            <a href="/ProyectoPandora/Public/index.php?route=Admin/ListarUsers" class="btn-volver">Volver a la lista de Usuarios</a>
            <?php endif; ?>
        </div>

    </div>
</main>
