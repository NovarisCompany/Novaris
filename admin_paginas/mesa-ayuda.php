<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(1);

$mensajeExito = '';
$mensajeError = '';
$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$nombre = $nombreCompleto;
$email = $_SESSION['email'] ?? '';
$asunto = '';
$mensaje = '';
$tickets = [];

try {
    $conexion = conectarBD();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $mensajeError = 'La solicitud expiró. Recarga la página e inténtalo nuevamente.';
    } elseif ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
        $mensajeError = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'Ingresa un correo electrónico válido.';
    } else {
        try {
            $stmt = mysqli_prepare($conexion, 'INSERT INTO mesa_ayuda (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $email, $asunto, $mensaje);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $mensajeExito = 'Solicitud enviada correctamente. Nuestro equipo la revisará pronto.';
            $asunto = '';
            $mensaje = '';
        } catch (mysqli_sql_exception $exception) {
            $mensajeError = 'No se pudo guardar la solicitud. Intenta nuevamente.';
        }
    }
}

try {
    $resultado = mysqli_query(
        $conexion,
        'SELECT id, nombre, asunto, estado, creado_en FROM mesa_ayuda ORDER BY creado_en DESC LIMIT 20'
    );
    $tickets = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception $exception) {
    if ($mensajeError === '') {
        $mensajeError = 'No se pudo cargar la lista de solicitudes.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de ayuda - Novaris</title>
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
<div class="informacion">
    <span id="fechahoy"></span>
    <div id="titulo-informacion">Mesa de ayuda</div>
    <p><strong>Administrador:</strong> <?php echo escaparHTML($nombreCompleto); ?></p>
</div>
<main class="help-page">
    <?php if ($mensajeError !== ''): ?><div class="error"><?php echo escaparHTML($mensajeError); ?></div><?php endif; ?>
    <?php if ($mensajeExito !== ''): ?><div class="success"><?php echo escaparHTML($mensajeExito); ?></div><?php endif; ?>
    <div class="help-grid">
        <form method="post" class="help-form">
            <input type="hidden" name="csrf_token" value="<?php echo escaparHTML(generarTokenCsrf()); ?>">
            <h2>Enviar solicitud de soporte</h2>
            <div class="form-group"><label for="nombre">Nombre</label><input type="text" id="nombre" name="nombre" value="<?php echo escaparHTML($nombre); ?>" required maxlength="255"></div>
            <div class="form-group"><label for="email">Correo electrónico</label><input type="email" id="email" name="email" value="<?php echo escaparHTML($email); ?>" required maxlength="255"></div>
            <div class="form-group"><label for="asunto">Asunto</label><input type="text" id="asunto" name="asunto" value="<?php echo escaparHTML($asunto); ?>" required maxlength="255"></div>
            <div class="form-group"><label for="mensaje">Mensaje</label><textarea id="mensaje" name="mensaje" rows="6" required><?php echo escaparHTML($mensaje); ?></textarea></div>
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </form>
        <section class="ticket-list">
            <h2>Últimas solicitudes</h2>
            <?php if ($tickets === []): ?>
                <p>No hay solicitudes registradas aún.</p>
            <?php else: ?>
                <div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>ID</th><th>Nombre</th><th>Asunto</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr><td><?php echo (int) $ticket['id']; ?></td><td><?php echo escaparHTML($ticket['nombre']); ?></td><td><?php echo escaparHTML($ticket['asunto']); ?></td><td><?php echo escaparHTML($ticket['estado']); ?></td><td><?php echo escaparHTML($ticket['creado_en']); ?></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </section>
    </div>
</main>
<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>
</body>
</html>
