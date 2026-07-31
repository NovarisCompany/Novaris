<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(2);

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$estadisticas = ['activos' => 0, 'pendientes' => 0, 'en_reparacion' => 0];
$ticketsRecientes = [];
$error = '';

try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $stmt = mysqli_prepare($conexion, 'SELECT id_tecnico FROM tecnico WHERE id_usuario = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $tecnico = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $idTecnico = (int) ($tecnico['id_tecnico'] ?? 0);

    if ($idTecnico > 0) {
        $stmt = mysqli_prepare(
            $conexion,
            "SELECT SUM(estado <> 'Resuelto') AS activos, SUM(estado = 'Pendiente') AS pendientes
             FROM ticket WHERE id_tecnico = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idTecnico);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        $estadisticas['activos'] = (int) ($resultado['activos'] ?? 0);
        $estadisticas['pendientes'] = (int) ($resultado['pendientes'] ?? 0);

        $stmt = mysqli_prepare(
            $conexion,
            "SELECT t.id_ticket, t.titulo, t.estado, t.prioridad
             FROM ticket AS t
             WHERE t.id_tecnico = ? AND t.estado <> 'Resuelto'
             ORDER BY t.fecha_creacion DESC
             LIMIT 3"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idTecnico);
        mysqli_stmt_execute($stmt);
        $ticketsRecientes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }

    $resultado = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM equipo WHERE estado_equipo = 'En reparacion'");
    $estadisticas['en_reparacion'] = (int) (mysqli_fetch_assoc($resultado)['total'] ?? 0);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el panel técnico.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel técnico - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=8">
</head>
<body class="app-page">
<header><nav class="navbar"><a href="index-tecnico.php" class="brand">Novaris</a><a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header>
<div class="side-bar"><a href="index-tecnico.php" class="side-link">Inicio</a><a href="mis_tickets.php" class="side-link">Mis tickets</a><a href="historial_trabajos.php" class="side-link">Historial de trabajos</a><a href="equipos_tecnico.php" class="side-link">Equipos</a></div>
<div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div><p>Gestiona los tickets que tienes asignados y consulta el inventario de soporte.</p></div>
<main class="help-page">
    <?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php endif; ?>
    <div class="help-grid">
        <section class="help-form"><h2>Resumen</h2><p>Tickets activos: <strong><?php echo $estadisticas['activos']; ?></strong></p><p>Tickets pendientes: <strong><?php echo $estadisticas['pendientes']; ?></strong></p><p>Equipos en reparación: <strong><?php echo $estadisticas['en_reparacion']; ?></strong></p><div class="acciones-botones"><a href="mis_tickets.php" class="accion-botones">Ver tickets asignados</a><a href="historial_trabajos.php" class="accion-botones">Ver historial</a><a href="equipos_tecnico.php" class="accion-botones">Ver equipos</a></div></section>
        <section class="ticket-list"><h2>Tickets recientes</h2><?php if ($ticketsRecientes === []): ?><p>No tienes tickets activos asignados.</p><?php else: ?><ul><?php foreach ($ticketsRecientes as $ticket): ?><li>#<?php echo (int) $ticket['id_ticket']; ?> — <?php echo escaparHTML($ticket['titulo']); ?> (<?php echo escaparHTML($ticket['estado']); ?>, <?php echo escaparHTML($ticket['prioridad']); ?>)</li><?php endforeach; ?></ul><?php endif; ?></section>
    </div>
</main>
<script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script>
</body>
</html>
