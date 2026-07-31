<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(3);

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$resumen = ['tickets_activos' => 0, 'equipos_mantenimiento' => 0];
$tickets = [];
$error = '';

try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $stmt = mysqli_prepare($conexion, "SELECT COUNT(*) AS total FROM ticket WHERE id_usuario = ? AND estado <> 'Resuelto'");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $resumen['tickets_activos'] = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'] ?? 0);
    mysqli_stmt_close($stmt);

    $consulta = "SELECT COUNT(*) AS total
                 FROM historial_asignacion AS h
                 INNER JOIN equipo AS e ON e.id_equipo = h.id_equipo
                 WHERE h.id_usuario = ? AND h.fecha_fin IS NULL AND e.estado_equipo = 'En reparacion'";
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $resumen['equipos_mantenimiento'] = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'] ?? 0);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conexion, "SELECT id_ticket, titulo, estado FROM ticket WHERE id_usuario = ? AND estado <> 'Resuelto' ORDER BY fecha_creacion DESC LIMIT 3");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $tickets = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el resumen de tus solicitudes.';
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Panel solicitante - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css?v=8"></head>
<body class="app-page"><header><nav class="navbar"><a href="index-solicitante.php" class="brand">Novaris</a><a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header><div class="side-bar"><a href="index-solicitante.php" class="side-link">Inicio</a><a href="crear_ticket.php" class="side-link">Crear ticket</a><a href="mis_equipos.php" class="side-link">Mis equipos</a></div>
<div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div><p>Gestiona tus solicitudes y revisa el estado de los equipos asignados.</p></div>
<main class="help-page"><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php endif; ?><div class="help-grid"><section class="help-form"><h2>Acciones rápidas</h2><p>Tickets activos: <strong><?php echo $resumen['tickets_activos']; ?></strong></p><p>Equipos en mantenimiento: <strong><?php echo $resumen['equipos_mantenimiento']; ?></strong></p><div class="acciones-botones"><a href="crear_ticket.php" class="accion-botones">Crear nuevo ticket</a><a href="mis_equipos.php" class="accion-botones">Ver mis equipos</a></div></section><section class="ticket-list"><h2>Tickets recientes</h2><?php if ($tickets === []): ?><p>No tienes tickets activos.</p><?php else: ?><ul><?php foreach ($tickets as $ticket): ?><li>#<?php echo (int) $ticket['id_ticket']; ?> — <?php echo escaparHTML($ticket['titulo']); ?> (<?php echo escaparHTML($ticket['estado']); ?>)</li><?php endforeach; ?></ul><?php endif; ?></section></div></main>
<script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
