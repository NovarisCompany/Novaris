<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(2);

$equipos = [];
$error = '';
try {
    $conexion = conectarBD();
    $resultado = mysqli_query($conexion, 'SELECT e.nombre, e.marca, e.modelo, e.estado_equipo, a.nombre_area, a.ubicacion FROM equipo AS e INNER JOIN area AS a ON a.id_area = e.id_area ORDER BY e.nombre');
    $equipos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el inventario de equipos.';
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Equipos - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css?v=8"></head>
<body class="app-page"><header><nav class="navbar"><a href="index-tecnico.php" class="brand">Novaris</a><a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header><div class="side-bar"><a href="index-tecnico.php" class="side-link">Inicio</a><a href="mis_tickets.php" class="side-link">Mis tickets</a><a href="historial_trabajos.php" class="side-link">Historial de trabajos</a><a href="equipos_tecnico.php" class="side-link">Equipos</a></div><div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion">Equipos</div><p>Consulta el inventario disponible para soporte.</p></div>
<main class="help-page"><section class="ticket-list"><h2>Listado de equipos</h2><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php elseif ($equipos === []): ?><p>No hay equipos registrados.</p><?php else: ?><div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>Equipo</th><th>Marca y modelo</th><th>Estado</th><th>Ubicación</th></tr></thead><tbody><?php foreach ($equipos as $equipo): ?><tr><td><?php echo escaparHTML($equipo['nombre']); ?></td><td><?php echo escaparHTML(trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))); ?></td><td><?php echo escaparHTML($equipo['estado_equipo']); ?></td><td><?php echo escaparHTML($equipo['nombre_area'] . ' - ' . $equipo['ubicacion']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></main><script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
