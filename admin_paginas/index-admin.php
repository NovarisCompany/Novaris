<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(1);

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$resumen = ['en_proceso' => 0, 'pendientes' => 0, 'usuarios_activos' => 0, 'equipos' => 0];
$tickets = [];
$error = '';

try {
    $conexion = conectarBD();
    $resultado = mysqli_query(
        $conexion,
        "SELECT
            SUM(estado = 'En proceso') AS en_proceso,
            SUM(estado = 'Pendiente') AS pendientes
         FROM ticket"
    );
    $resumenTickets = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);
    $resumen['en_proceso'] = (int) ($resumenTickets['en_proceso'] ?? 0);
    $resumen['pendientes'] = (int) ($resumenTickets['pendientes'] ?? 0);

    $resultado = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM usuario WHERE estado_cuenta = 'Aprobada'");
    $resumen['usuarios_activos'] = (int) (mysqli_fetch_assoc($resultado)['total'] ?? 0);
    mysqli_free_result($resultado);

    $resultado = mysqli_query($conexion, 'SELECT COUNT(*) AS total FROM equipo');
    $resumen['equipos'] = (int) (mysqli_fetch_assoc($resultado)['total'] ?? 0);
    mysqli_free_result($resultado);

    $resultado = mysqli_query(
        $conexion,
        'SELECT id_ticket, titulo, estado, fecha_creacion
         FROM ticket
         ORDER BY fecha_creacion DESC
         LIMIT 5'
    );
    $tickets = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el resumen del panel.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo - Novaris</title>
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
    <div class="reportes-link"><a href="usuario.php" class="side-link">Usuarios</a></div>
</div>
<div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div></div>
<main>
    <?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php endif; ?>
    <div class="info-resumen">
        <div class="tickets"><h3>Tickets en proceso:</h3><p><?php echo $resumen['en_proceso']; ?></p><h4>Tickets pendientes:</h4><p><?php echo $resumen['pendientes']; ?></p></div>
        <div class="inventario"><h3>Usuarios activos:</h3><p><?php echo $resumen['usuarios_activos']; ?></p><h4>Equipos registrados:</h4><p><?php echo $resumen['equipos']; ?></p></div>
    </div>
    <section class="ultimos_tickets">
        <h3>Últimos tickets</h3>
        <?php if ($tickets === []): ?>
            <p>No hay tickets registrados.</p>
        <?php else: ?>
            <table><thead><tr><th>ID</th><th>Asunto</th><th>Estado</th><th>Fecha de creación</th></tr></thead><tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr><td><?php echo (int) $ticket['id_ticket']; ?></td><td><?php echo escaparHTML($ticket['titulo']); ?></td><td><?php echo escaparHTML($ticket['estado']); ?></td><td><?php echo escaparHTML($ticket['fecha_creacion']); ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php endif; ?>
    </section>
</main>
<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>
</body>
</html>
