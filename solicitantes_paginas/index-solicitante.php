<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario']) || (int) ($_SESSION['id_rol'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$resumen = ['tickets_activos' => 0, 'equipos_mantenimiento' => 0];
$tickets = [];
$ticketsPorEstado = [];
$equiposPorEstado = [];
$error = '';

try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];

    $stmt = mysqli_prepare($conexion, "SELECT estado, COUNT(*) AS total FROM ticket WHERE id_usuario = ? GROUP BY estado ORDER BY estado");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $ticketsPorEstado = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conexion, "SELECT e.estado_equipo AS estado, COUNT(DISTINCT e.id_equipo) AS total
        FROM historial_asignacion AS h
        INNER JOIN equipo AS e ON e.id_equipo = h.id_equipo
        WHERE h.id_usuario = ? AND h.fecha_fin IS NULL
        GROUP BY e.estado_equipo ORDER BY e.estado_equipo");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $equiposPorEstado = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

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

// Ambos gráficos y sus leyendas se generan con los mismos datos del usuario.
function prepararGraficoSolicitante(array $filas): array
{
    $colores = ['Pendiente' => '#0B1047', 'En proceso' => '#3B82F6', 'Resuelto' => '#93C5FD',
        'Disponible' => '#34d399', 'Asignado' => '#0B1047', 'En reparacion' => '#3B82F6', 'Baja' => '#93C5FD'];
    $alternativos = ['#a78bfa', '#22d3ee', '#fb923c'];
    $total = array_sum(array_column($filas, 'total'));
    foreach ($filas as $indice => &$fila) {
        $fila['color'] = $colores[$fila['estado']] ?? $alternativos[$indice % count($alternativos)];
        $fila['porcentaje'] = $total > 0 ? (int) $fila['total'] / $total * 100 : 0;
    }
    unset($fila);
    return ['filas' => $filas, 'total' => $total];
}
$graficoTickets = prepararGraficoSolicitante($ticketsPorEstado);
$graficoEquipos = prepararGraficoSolicitante($equiposPorEstado);
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
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=12" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=12" media="not all">
    <link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=3">

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
        <div class="tickets-link">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5V8"></path><path d="M15 16V19"></path><path d="M15 11V13"></path><path d="M4.4416 10.4557C3.79252 9.73379 2.96682 8.93468 3.07899 7.97035C3.35831 5.56902 4.46022 5 7.648 5H16.352C19.5398 5 20.6417 5.56902 20.921 7.97035C21.0332 8.93468 20.2075 9.73379 19.5584 10.4557C19.2424 10.8072 19 11.3037 19 12C19 12.6963 19.2424 13.1928 19.5584 13.5443C20.2075 14.2662 21.0332 15.0653 20.921 16.0296C20.6417 18.431 19.5398 19 16.352 19H7.648C4.46022 19 3.35831 18.431 3.07899 16.0296C2.96682 15.0653 3.79252 15.0653 4.4416 13.5443C4.75761 13.1928 5 12.6963 5 12C5 11.3037 4.75761 10.8072 4.4416 10.4557Z"></path></svg>
            <a href="mis_ticketsS.php" class="side-link">Mis tickets</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="informacion"><div class="error"><?php echo escaparHTML($error); ?></div></div>
    <?php endif; ?>

    <?php if ($error === ''): ?>
    <section class="solicitante-graficos" aria-label="Resumen visual de mis solicitudes y equipos">
        <article class="solicitante-grafico">
            <h3>Mis tickets por estado</h3>
            <p class="grafico-descripcion">Todas tus solicitudes, incluidas las resueltas.</p>
            <?php if ($graficoTickets['total'] > 0): ?>
                <div class="grafico-canvas"><canvas id="graficoMisTickets" role="img" aria-label="Mis tickets por estado. Cantidades en la lista siguiente."></canvas></div>
            <?php else: ?>
                <p class="grafico-vacio">Todavía no has creado tickets.</p>
            <?php endif; ?>
            <ul class="grafico-leyenda">
                <?php foreach ($graficoTickets['filas'] as $fila): ?>
                    <li><span class="grafico-color" style="background: <?php echo escaparHTML($fila['color']); ?>" aria-hidden="true"></span><span><?php echo escaparHTML($fila['estado']); ?></span><strong><?php echo (int) $fila['total']; ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </article>
        <article class="solicitante-grafico">
            <h3>Estado de mis equipos</h3>
            <p class="grafico-descripcion">Equipos que tienes asignados actualmente.</p>
            <?php if ($graficoEquipos['total'] > 0): ?>
                <p class="grafico-total"><strong><?php echo (int) $graficoEquipos['total']; ?></strong> equipos asignados</p>
                <div class="grafico-canvas grafico-canvas-barra"><canvas id="graficoMisEquipos" role="img" aria-label="Equipos por estado. Cantidades y porcentajes en la lista siguiente."></canvas></div>
            <?php else: ?>
                <p class="grafico-vacio">No tienes equipos asignados actualmente.</p>
            <?php endif; ?>
            <ul class="grafico-leyenda">
                <?php foreach ($graficoEquipos['filas'] as $fila): ?>
                    <li><span class="grafico-color" style="background: <?php echo escaparHTML($fila['color']); ?>" aria-hidden="true"></span><span><?php echo escaparHTML($fila['estado']); ?></span><strong><?php echo (int) $fila['total']; ?> <small>(<?php echo round($fila['porcentaje'], 1); ?>%)</small></strong></li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>
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
                            <td><a href="mis_ticketsS.php"><?php echo escaparHTML($ticket['titulo']); ?></a></td>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script id="datos-graficos-solicitante" type="application/json"><?php echo json_encode(['tickets' => $graficoTickets, 'equipos' => $graficoEquipos], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <script>
(function () {
    const datos = document.getElementById('datos-graficos-solicitante');
    if (!datos || typeof Chart === 'undefined') return;
    const resumen = JSON.parse(datos.textContent);
    const graficos = [];
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function leyenda() {
        return {
            position: 'bottom',
            labels: { padding: 18, usePointStyle: true, pointStyle: 'circle' },
            onHover(evento, item, leyenda) {
                leyenda.chart.canvas.style.cursor = 'pointer';
                const indice = item.datasetIndex ?? 0;
                leyenda.chart.setActiveElements([{ datasetIndex: indice, index: item.index ?? 0 }]);
                leyenda.chart.update('none');
            },
            onLeave(evento, item, leyenda) {
                leyenda.chart.canvas.style.cursor = 'default';
                leyenda.chart.setActiveElements([]);
                leyenda.chart.update('none');
            }
        };
    }

    const tickets = document.getElementById('graficoMisTickets');
    if (tickets) {
        graficos.push(new Chart(tickets, {
            type: 'doughnut',
            data: {
                labels: resumen.tickets.filas.map(fila => fila.estado),
                datasets: [{
                    data: resumen.tickets.filas.map(fila => Number(fila.total)),
                    backgroundColor: resumen.tickets.filas.map(fila => fila.color),
                    borderWidth: 3,
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                animation: reducirMovimiento ? false : { duration: 650 },
                layout: { padding: 12 },
                plugins: {
                    legend: leyenda(),
                    tooltip: { callbacks: { label(contexto) {
                        const cantidad = contexto.parsed;
                        return `${contexto.label}: ${cantidad} (${(cantidad / resumen.tickets.total * 100).toFixed(1)}%)`;
                    } } }
                }
            }
        }));
    }

    const equipos = document.getElementById('graficoMisEquipos');
    if (equipos) {
        graficos.push(new Chart(equipos, {
            type: 'bar',
            data: {
                labels: ['Mis equipos'],
                datasets: resumen.equipos.filas.map(fila => ({
                    label: fila.estado,
                    data: [Number(fila.total)],
                    backgroundColor: fila.color,
                    hoverBackgroundColor: fila.color,
                    borderWidth: 2,
                    borderSkipped: false,
                    barThickness: 48
                }))
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                animation: reducirMovimiento ? false : { duration: 650 },
                plugins: {
                    legend: leyenda(),
                    tooltip: { callbacks: { label(contexto) {
                        const cantidad = contexto.parsed.x;
                        return `${contexto.dataset.label}: ${cantidad} (${(cantidad / resumen.equipos.total * 100).toFixed(1)}%)`;
                    } } }
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, border: { display: false } },
                    y: { stacked: true, grid: { display: false }, border: { display: false } }
                }
            }
        }));
    }

    function actualizarTema() {
        const estilos = getComputedStyle(document.documentElement);
        const texto = estilos.getPropertyValue('--text-primary').trim();
        const borde = estilos.getPropertyValue('--outline').trim();
        graficos.forEach(grafico => {
            grafico.options.plugins.legend.labels.color = texto;
            grafico.data.datasets.forEach(dataset => { dataset.borderColor = estilos.getPropertyValue('--panel').trim(); });
            if (grafico.options.scales.x) {
                grafico.options.scales.x.ticks.color = texto;
                grafico.options.scales.y.ticks.color = texto;
                grafico.options.scales.x.grid.color = borde;
            }
            grafico.update('none');
        });
    }
    new MutationObserver(() => requestAnimationFrame(actualizarTema))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    document.querySelectorAll('#tema-claro, #tema-oscuro').forEach(enlace => enlace.addEventListener('load', actualizarTema));
    actualizarTema();
}());

    </script>
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
