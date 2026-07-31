<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(2);

$tickets = [];
$error = '';
try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $consulta = "SELECT t.id_ticket, CONCAT(u.nombre, ' ', u.apellido) AS solicitante, t.titulo, t.prioridad, t.estado
                 FROM tecnico AS te
                 INNER JOIN ticket AS t ON t.id_tecnico = te.id_tecnico
                 INNER JOIN usuario AS u ON u.id_usuario = t.id_usuario
                 WHERE te.id_usuario = ? AND t.estado <> 'Resuelto'
                 ORDER BY FIELD(t.prioridad, 'Alta', 'Media', 'Baja'), t.fecha_creacion DESC";
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $tickets = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudieron cargar los tickets asignados.';
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mis tickets - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css?v=8"></head>
<body class="app-page">
<header><nav class="navbar"><a href="index-tecnico.php" class="brand">Novaris</a><a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header>
<div class="side-bar"><a href="index-tecnico.php" class="side-link">Inicio</a><a href="mis_tickets.php" class="side-link">Mis tickets</a><a href="historial_trabajos.php" class="side-link">Historial de trabajos</a><a href="equipos_tecnico.php" class="side-link">Equipos</a></div>
<div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion">Mis tickets</div><p>Revisa los tickets activos asignados a tu cuenta.</p></div>
<main class="help-page"><section class="ticket-list"><h2>Tickets activos</h2><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php elseif ($tickets === []): ?><p>No tienes tickets activos asignados.</p><?php else: ?><div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>ID</th><th>Solicitante</th><th>Asunto</th><th>Prioridad</th><th>Estado</th></tr></thead><tbody><?php foreach ($tickets as $ticket): ?><tr><td><?php echo (int) $ticket['id_ticket']; ?></td><td><?php echo escaparHTML($ticket['solicitante']); ?></td><td><?php echo escaparHTML($ticket['titulo']); ?></td><td><?php echo escaparHTML($ticket['prioridad']); ?></td><td><?php echo escaparHTML($ticket['estado']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></main>
<script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
