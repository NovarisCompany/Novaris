<?php
session_start();
require_once __DIR__ . '/../conexion.php';
if (!isset($_SESSION['id_usuario']) || (int) ($_SESSION['id_rol'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

$conexion = conectarBD();



$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$tickets = [];
$error = '';
$mensaje = $_SESSION['mensaje_ticket'] ?? '';
unset($_SESSION['mensaje_ticket']);

try {
    
    $idUsuario = (int) $_SESSION['id_usuario'];
    $consulta = "SELECT t.id_ticket, t.titulo, t.descripcion, t.prioridad, t.estado,
        t.fecha_creacion, t.diagnostico, t.resolucion, e.nombre AS equipo_nombre,
        tu.nombre AS tecnico_nombre, tu.apellido AS tecnico_apellido
        FROM ticket AS t
        INNER JOIN equipo AS e ON e.id_equipo = t.id_equipo
        LEFT JOIN tecnico AS te ON te.id_tecnico = t.id_tecnico
        LEFT JOIN usuario AS tu ON tu.id_usuario = te.id_usuario
        WHERE t.id_usuario = ?
        ORDER BY t.fecha_creacion DESC, t.id_ticket DESC";
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $tickets = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $exception) {
    $error = 'No se pudieron cargar tus tickets.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis tickets - Novaris</title>
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=8" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=8" media="not all">
    <link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=1">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-solicitante.php" class="brand"><span>Nov</span><span>aris</span></a>

            <div class="modo-light theme-mode-control" data-tema="dark">
                <button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.31412 6.31412L5.5 5.5M17.6859 6.31412L18.5 5.5M6.31412 17.69L5.5 18.5001M17.6859 17.69L18.5 18.5001M21 12H20M16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>

            <div class="modo-dark theme-mode-control" data-tema="light" hidden>
                <button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.9 2.31c-.16-.41-.74-.41-.9 0l-.43 1.1a.75.75 0 0 1-.27.27l-1.09.43c-.41.16-.41.74 0 .9l1.09.43c.13.05.22.15.27.27l.43 1.1c.16.41.74.41.9 0l.43-1.1a.75.75 0 0 1 .27-.27l1.09-.43c.41-.16.41-.74 0-.9l-1.09-.43a.75.75 0 0 1-.27-.27l-.43-1.1Z" stroke="#1C274C"></path><path d="M21.25 12c0 5.11-4.14 9.25-9.25 9.25A9.25 9.25 0 1 1 12 2.75c.09 0 .1.12.03.16A6.5 6.5 0 0 0 15.5 14.75c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg>
                </button>
            </div>

            <a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
            
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link">
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
          <div class="tickets-link active">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5V8"></path><path d="M15 16V19"></path><path d="M15 11V13"></path><path d="M4.4416 10.4557C3.79252 9.73379 2.96682 8.93468 3.07899 7.97035C3.35831 5.56902 4.46022 5 7.648 5H16.352C19.5398 5 20.6417 5.56902 20.921 7.97035C21.0332 8.93468 20.2075 9.73379 19.5584 10.4557C19.2424 10.8072 19 11.3037 19 12C19 12.6963 19.2424 13.1928 19.5584 13.5443C20.2075 14.2662 21.0332 15.0653 20.921 16.0296C20.6417 18.431 19.5398 19 16.352 19H7.648C4.46022 19 3.35831 18.431 3.07899 16.0296C2.96682 15.0653 3.79252 15.0653 4.4416 13.5443C4.75761 13.1928 5 12.6963 5 12C5 11.3037 4.75761 11.3037 4.4416 10.4557Z"></path></svg>
            <a href="mis_ticketsS.php" class="side-link">Mis tickets</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
        <p>Consulta el estado de los tickets asignados a tu cuenta.</p>
    </div>

    <main class="help-page">
        <section class="ticket-list">
            <h2>Mis tickets</h2>
            <p>Consulta el estado de tus solicitudes y la solución registrada por el técnico.</p>
            <?php if ($mensaje !== ''): ?><div class="mensaje-exito"><?php echo escaparHTML($mensaje); ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="mensaje-error" role="alert"><?php echo escaparHTML($error); ?></div><?php endif; ?>
            <div class="ticket-table-wrapper">
                <table class="ticket-table">
                    <thead>
                        <tr><th>ID</th><th>Título</th><th>Equipo</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th>Fecha de creación</th><th>Diagnóstico y solución</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($tickets === [] && $error === ''): ?>
                            <tr><td colspan="8">Todavía no has creado tickets.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($tickets as $ticketItem): ?>
                            <tr>
                                <td><?php echo (int) $ticketItem['id_ticket']; ?></td>
                                <td><?php echo escaparHTML($ticketItem['titulo']); ?></td>
                                <td><?php echo escaparHTML($ticketItem['equipo_nombre']); ?></td>
                                <td><?php echo escaparHTML($ticketItem['prioridad']); ?></td>
                                <td><?php echo escaparHTML($ticketItem['estado']); ?></td>
                                <?php $nombreTecnico = trim(($ticketItem['tecnico_nombre'] ?? '') . ' ' . ($ticketItem['tecnico_apellido'] ?? '')); ?>
                                <td><?php echo escaparHTML($nombreTecnico !== '' ? $nombreTecnico : 'Sin asignar'); ?></td>
                                <td><?php echo escaparHTML($ticketItem['fecha_creacion']); ?></td>
                                <td><?php echo nl2br(escaparHTML($ticketItem['diagnostico'] ?? '')); ?><p><?php echo nl2br(escaparHTML($ticketItem['resolucion'] ?: 'Sin solución registrada todavía.')); ?></p></td>
                                
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const fechaFormateada = new Date().toLocaleDateString('es-ES', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        document.getElementById('fechahoy').textContent = fechaFormateada;
    </script>
    <script>
        (function () {
            const temaOscuro = document.getElementById('tema-oscuro');
            const temaClaro = document.getElementById('tema-claro');
            const temaGuardado = localStorage.getItem('novaris-tema');
            const temaInicial = temaGuardado === 'light' ? 'light' : 'dark';

            function aplicarTema(tema, guardar = true) {
                const esClaro = tema === 'light';
                document.documentElement.dataset.theme = tema;
                temaOscuro.media = esClaro ? 'not all' : 'all';
                temaClaro.media = esClaro ? 'all' : 'not all';
                document.querySelector('[data-tema="dark"]').hidden = esClaro;
                document.querySelector('[data-tema="light"]').hidden = !esClaro;
                if (guardar) localStorage.setItem('novaris-tema', tema);
            }

            window.cambiarTema = function (tema) {
                document.documentElement.classList.add('theme-ready');
                aplicarTema(tema);
            };

            document.addEventListener('DOMContentLoaded', function () {
                aplicarTema(temaInicial, false);
                setTimeout(function () {
                    document.documentElement.classList.add('theme-ready');
                }, 1000);
            });
        }());
    </script>
</body>
</html>
