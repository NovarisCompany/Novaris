<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}
$equipos = [];
$error = '';
try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $consulta = 'SELECT e.nombre, e.marca, e.modelo, e.estado_equipo, h.fecha_inicio
                 FROM historial_asignacion AS h
                 INNER JOIN equipo AS e ON e.id_equipo = h.id_equipo
                 WHERE h.id_usuario = ? AND h.fecha_fin IS NULL
                 ORDER BY h.fecha_inicio DESC';
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $equipos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudieron cargar los equipos asignados.';
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mis equipos - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=8" media="all"><link id="tema-claro" rel="stylesheet" href="../style_light.css?v=8" media="not all"><link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=1"><script src="solicitante-theme.js"></script></head>
<body class="app-page"><header><nav class="navbar"><a href="index-solicitante.php" class="brand">Novaris</a><a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><div class="modo-light theme-mode-control" data-tema="dark"><button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.3 6.3 5.5 5.5M17.7 6.3 18.5 5.5M6.3 17.7 5.5 18.5M17.7 17.7 18.5 18.5M21 12H20M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button></div><div class="modo-dark theme-mode-control" data-tema="light" hidden><button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21.25 12a9.25 9.25 0 1 1-9.25-9.25c.1 0 .12.13.03.17a6.5 6.5 0 0 0 3.47 11.83c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg></button></div><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header><div class="side-bar"><a href="index-solicitante.php" class="side-link">Inicio</a><a href="crear_ticket.php" class="side-link">Crear ticket</a><a href="mis_equipos.php" class="side-link">Mis equipos</a></div><div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion">Mis equipos</div><p>Consulta el estado de los equipos asignados a tu cuenta.</p></div>
<main class="help-page"><section class="ticket-list"><h2>Equipos asignados</h2><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php elseif ($equipos === []): ?><p>No tienes equipos asignados actualmente.</p><?php else: ?><div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>Equipo</th><th>Marca y modelo</th><th>Estado</th><th>Fecha de asignación</th></tr></thead><tbody><?php foreach ($equipos as $equipo): ?><tr><td><?php echo escaparHTML($equipo['nombre']); ?></td><td><?php echo escaparHTML(trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))); ?></td><td><?php echo escaparHTML($equipo['estado_equipo']); ?></td><td><?php echo escaparHTML($equipo['fecha_inicio']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></main><script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
