<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel solicitante - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=8" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=8" media="not all">
    <link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=1">
    <script src="solicitante-theme.js"></script>
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-solicitante.php" class="brand"><span>Nov</span><span>aris</span></a>
            <div class="modo-light theme-mode-control" data-tema="dark">
                <button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.3 6.3 5.5 5.5M17.7 6.3 18.5 5.5M6.3 17.7 5.5 18.5M17.7 17.7 18.5 18.5M21 12H20M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>
            <div class="modo-dark theme-mode-control" data-tema="light" hidden>
                <button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21.25 12a9.25 9.25 0 1 1-9.25-9.25c.1 0 .12.13.03.17a6.5 6.5 0 0 0 3.47 11.83c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg>
                </button>
            </div>
            <a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link active">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <a href="index-solicitante.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg>
            <a href="crear_ticket.php" class="side-link">Crear ticket</a>
        </div>
        <div class="mesa-ayuda-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <a href="mis_equipos.php" class="side-link">Mis equipos</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="informacion"><div class="error"><?php echo escaparHTML($error); ?></div></div>
    <?php endif; ?>

    <div class="info-resumen">
        <div class="tickets">
            <h3>Tickets activos:</h3>
            <p><?php echo $resumen['tickets_activos']; ?></p>
            <h4>Solicitudes abiertas:</h4>
            <p><?php echo $resumen['tickets_activos']; ?></p>
        </div>
        <div class="inventario">
            <h3>Equipos en mantenimiento:</h3>
            <p><?php echo $resumen['equipos_mantenimiento']; ?></p>
            <h4>Estado de mis equipos:</h4>
            <p><a href="mis_equipos.php">Consultar equipos</a></p>
        </div>
    </div>

    <div class="ultimos_tickets">
        <h3>Mis tickets recientes:</h3>
        <table>
            <thead><tr><th>ID</th><th>Asunto</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if ($tickets === []): ?>
                    <tr><td colspan="3">No hay tickets activos.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo (int) $ticket['id_ticket']; ?></td>
                            <td><?php echo escaparHTML($ticket['titulo']); ?></td>
                            <td><?php echo escaparHTML($ticket['estado']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="acciones_rapidas">
        <div class="acciones-botones">
            <a class="accion-botones" href="crear_ticket.php">Crear ticket</a>
            <a class="accion-botones" href="mis_equipos.php">Ver mis equipos</a>
            <a class="accion-botones" href="../perfil.php">Editar perfil</a>
        </div>
    </div>

    <script>
        document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    </script>
</body>
</html>
