<?php
require_once __DIR__ . '/../conexion.php';
$trabajos = [];
$error = '';
try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $consulta = "SELECT t.id_ticket, e.nombre AS equipo, COALESCE(NULLIF(t.resolucion, ''), NULLIF(t.diagnostico, ''), 'Sin detalle registrado') AS detalle, t.fecha_cierre
                 FROM tecnico AS te
                 INNER JOIN ticket AS t ON t.id_tecnico = te.id_tecnico
                 INNER JOIN equipo AS e ON e.id_equipo = t.id_equipo
                 WHERE te.id_usuario = ? AND t.estado = 'Resuelto'
                 ORDER BY t.fecha_cierre DESC";
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $trabajos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el historial de trabajos.';
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Historial de trabajos - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css?v=8"></head>
<body class="app-page"><header><nav class="navbar"><a href="index-tecnico.php" class="brand">Novaris</a><a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header><div class="side-bar"><a href="index-tecnico.php" class="side-link">Inicio</a><a href="mis_tickets.php" class="side-link">Mis tickets</a><a href="historial_trabajos.php" class="side-link">Historial de trabajos</a><a href="equipos_tecnico.php" class="side-link">Equipos</a></div><div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion">Historial de trabajos</div><p>Consulta los tickets que finalizaste.</p></div>
<main class="help-page"><section class="ticket-list"><h2>Trabajos finalizados</h2><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php elseif ($trabajos === []): ?><p>Aún no registraste trabajos finalizados.</p><?php else: ?><div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>ID</th><th>Equipo</th><th>Detalle</th><th>Fecha</th></tr></thead><tbody><?php foreach ($trabajos as $trabajo): ?><tr><td><?php echo (int) $trabajo['id_ticket']; ?></td><td><?php echo escaparHTML($trabajo['equipo']); ?></td><td><?php echo escaparHTML($trabajo['detalle']); ?></td><td><?php echo escaparHTML($trabajo['fecha_cierre']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></main><script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
