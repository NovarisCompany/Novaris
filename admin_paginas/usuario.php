<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(1);

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$usuarios = [];
$error = '';
$mensaje = $_SESSION['mensaje_usuario'] ?? '';
unset($_SESSION['mensaje_usuario']);

try {
    $conexion = conectarBD();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $estadoCuenta = $_POST['estado_cuenta'] ?? '';
        $estadosPermitidos = ['Pendiente', 'Aprobada', 'Rechazada'];

        if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $error = 'La solicitud expiró. Recarga la página e inténtalo nuevamente.';
        } elseif ($idUsuario <= 0 || !in_array($estadoCuenta, $estadosPermitidos, true)) {
            $error = 'La actualización solicitada no es válida.';
        } else {
            $stmt = mysqli_prepare($conexion, 'UPDATE usuario SET estado_cuenta = ? WHERE id_usuario = ?');
            mysqli_stmt_bind_param($stmt, 'si', $estadoCuenta, $idUsuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $_SESSION['mensaje_usuario'] = 'Estado de cuenta actualizado correctamente.';
            header('Location: usuario.php');
            exit;
        }
    }

    $resultado = mysqli_query(
        $conexion,
        'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.estado_cuenta, r.nombre_rol
         FROM usuario AS u
         INNER JOIN roles AS r ON r.id_rol = u.id_rol
         ORDER BY u.apellido, u.nombre'
    );
    $usuarios = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar la lista de usuarios.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=6">
</head>
<body class="app-page">
<header><nav class="navbar">
    <a href="index-admin.php" class="brand">Novaris</a>
    <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
    <a href="../perfil.php" class="nav-cta">Mi perfil</a>
</nav></header>
<div class="side-bar">
    <div class="home-link"><a href="index-admin.php" class="side-link">Inicio</a></div>
    <div class="inventario-link"><a href="inventario.php" class="side-link">Inventario</a></div>
    <div class="mesa-ayuda-link"><a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a></div>
    <div class="usuarios-link"><a href="usuario.php" class="side-link">Usuarios</a></div>
</div>
<div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div></div>
<main class="usuario-info">
    <h1>Usuarios</h1>
    <?php if ($mensaje !== ''): ?>
        <div class="success"><?php echo escaparHTML($mensaje); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="error"><?php echo escaparHTML($error); ?></div>
    <?php elseif ($usuarios === []): ?>
        <p>No hay usuarios registrados.</p>
    <?php else: ?>
        <div class="usuario-table"><table><thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?php echo escaparHTML($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                <td><?php echo escaparHTML($usuario['email']); ?></td>
                <td><?php echo escaparHTML($usuario['nombre_rol']); ?></td>
                <td><?php echo escaparHTML($usuario['estado_cuenta']); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo escaparHTML(generarTokenCsrf()); ?>">
                        <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario['id_usuario']; ?>">
                        <select name="estado_cuenta" aria-label="Estado de <?php echo escaparHTML($usuario['email']); ?>">
                            <?php foreach (['Pendiente', 'Aprobada', 'Rechazada'] as $estado): ?>
                                <option value="<?php echo $estado; ?>"<?php echo $usuario['estado_cuenta'] === $estado ? ' selected' : ''; ?>><?php echo $estado; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</main>
<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>
</body>
</html>
