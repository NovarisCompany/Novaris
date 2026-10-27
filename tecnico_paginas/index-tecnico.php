<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario']) || (int) ($_SESSION['id_rol'] ?? 0) !== 2) {
    header('Location: ../login.php');
    exit;
}

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$estadisticas = ['activos' => 0, 'en_proceso' => 0, 'resueltos' => 0];
$ticketsRecientes = [];
$ticketsPorEstado = [];
$ticketsPorPrioridad = [];
$error = '';
try {
    $conexion = conectarBD();
    $idUsuario = (int) $_SESSION['id_usuario'];
    $stmt = mysqli_prepare($conexion, "SELECT t.estado, COUNT(*) AS total
        FROM ticket AS t
        INNER JOIN tecnico AS te ON te.id_tecnico = t.id_tecnico
        INNER JOIN usuario AS tu ON tu.id_usuario = te.id_usuario
        WHERE te.id_usuario = ? AND te.estado = 'Activo' AND tu.id_rol = 2
        GROUP BY t.estado");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ticketsPorEstado[$fila['estado']] = (int) $fila['total'];
        if ($fila['estado'] === 'Resuelto') {
            $estadisticas['resueltos'] = (int) $fila['total'];
        } else {
            $estadisticas['activos'] += (int) $fila['total'];
            if ($fila['estado'] === 'En proceso') {
                $estadisticas['en_proceso'] = (int) $fila['total'];
            }
        }
    }
    mysqli_stmt_close($stmt);
    $stmt = mysqli_prepare($conexion, "SELECT t.prioridad, COUNT(*) AS total
        FROM ticket AS t
        INNER JOIN tecnico AS te ON te.id_tecnico = t.id_tecnico
        INNER JOIN usuario AS tu ON tu.id_usuario = te.id_usuario
        WHERE te.id_usuario = ? AND te.estado = 'Activo' AND tu.id_rol = 2 AND t.estado <> 'Resuelto'
        GROUP BY t.prioridad ORDER BY FIELD(t.prioridad, 'Alta', 'Media', 'Baja'), t.prioridad");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ticketsPorPrioridad[$fila['prioridad']] = (int) $fila['total'];
    }
    mysqli_stmt_close($stmt);
    $stmt = mysqli_prepare($conexion, "SELECT t.id_ticket AS id, t.titulo, t.estado, t.prioridad
        FROM ticket AS t
        INNER JOIN tecnico AS te ON te.id_tecnico = t.id_tecnico
        INNER JOIN usuario AS tu ON tu.id_usuario = te.id_usuario
        WHERE te.id_usuario = ? AND te.estado = 'Activo' AND tu.id_rol = 2 AND t.estado <> 'Resuelto'
        ORDER BY t.fecha_creacion DESC, t.id_ticket DESC LIMIT 3");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $ticketsRecientes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $exception) {
    $error = 'No se pudo cargar el resumen de tus tickets.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel tecnico - Novaris</title>
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=12" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=12" media="not all">
    <link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=3">

</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-tecnico.php" class="brand"><span>Nov</span><span>aris</span></a>
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
            <a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link active">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <a href="index-tecnico.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg>
            <a href="mis_ticketsT.php" class="side-link">Mis tickets</a>
        </div>
        <div class="mesa-ayuda-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <a href="historial_trabajos.php" class="side-link">Historial de trabajos</a>
        </div>
        <div class="reportes-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M8 11h8M8 15h5" stroke-linecap="round"/></svg>
            <a href="equipos_tecnico.php" class="side-link">Equipos</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
    </div>

    <?php if ($error === ''): ?>
    <section class="tecnico-graficos" aria-label="Resumen visual de mis tickets asignados">
        <?php foreach ([['titulo' => 'Mis tickets por estado', 'descripcion' => 'Todos tus tickets asignados, incluidos los resueltos.', 'id' => 'graficoEstadosTecnico', 'datos' => $ticketsPorEstado], ['titulo' => 'Tickets activos por prioridad', 'descripcion' => 'Prioridades de los tickets que tienes por resolver.', 'id' => 'graficoPrioridadesTecnico', 'datos' => $ticketsPorPrioridad]] as $grafico): ?>
        <article class="tecnico-grafico">
            <h2><?php echo escaparHTML($grafico['titulo']); ?></h2>
            <p><?php echo escaparHTML($grafico['descripcion']); ?></p>
            <?php if (array_sum($grafico['datos']) > 0): ?>
                <div class="tecnico-grafico-canvas"><canvas id="<?php echo escaparHTML($grafico['id']); ?>" role="img" aria-label="<?php echo escaparHTML($grafico['titulo']); ?>. Cantidades en la lista siguiente."></canvas></div>
                <ul class="tecnico-grafico-resumen">
                    <?php foreach ($grafico['datos'] as $etiqueta => $cantidad): ?>
                        <li><span><?php echo escaparHTML($etiqueta); ?></span><strong><?php echo (int) $cantidad; ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="tecnico-grafico-vacio">No tienes tickets <?php echo $grafico['id'] === 'graficoEstadosTecnico' ? 'asignados' : 'activos'; ?>.</p>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <main class="help-page">
        <?php if ($error !== ''): ?><div class="mensaje-error" role="alert"><?php echo escaparHTML($error); ?></div><?php endif; ?>
        <div class="help-grid">
            <section class="help-form">
                <h2>Resumen de trabajo</h2>
                <p>Tickets activos: <strong><?php echo $estadisticas['activos']; ?></strong></p>
                <p>Tickets en proceso: <strong><?php echo $estadisticas['en_proceso']; ?></strong></p>
                <p>Tickets resueltos: <strong><?php echo $estadisticas['resueltos']; ?></strong></p>
                <div class="acciones-botones">
                    <a href="mis_ticketsT.php" class="accion-botones">Ver mis tickets</a>
                    <a href="historial_trabajos.php" class="accion-botones">Ver historial</a>
                </div>
            </section>
            <section class="ticket-list">
                <h2>Tickets activos recientes</h2>
                <?php if ($ticketsRecientes === [] && $error === ''): ?><p>No tienes tickets activos asignados.</p><?php endif; ?>
                <ul>
                    <?php foreach ($ticketsRecientes as $ticket): ?>
                        <li><a href="mis_ticketsT.php">#<?php echo (int) $ticket['id']; ?> - <?php echo escaparHTML($ticket['titulo']); ?></a> (<?php echo escaparHTML($ticket['estado']); ?>, <?php echo escaparHTML($ticket['prioridad']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script id="datos-graficos-tecnico" type="application/json"><?php echo json_encode(['estados' => $ticketsPorEstado, 'prioridades' => $ticketsPorPrioridad], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <script>
(function () {
    const elemento = document.getElementById('datos-graficos-tecnico');
    if (!elemento || typeof Chart === 'undefined') return;
    const datos = JSON.parse(elemento.textContent);
    const graficos = [];
    const colores = {
        Pendiente: '#0B1047', 'En proceso': '#3B82F6', Resuelto: '#93C5FD',
        Alta: '#0B1047', Media: '#3B82F6', Baja: '#93C5FD'
    };
    const alternativos = ['#a78bfa', '#22d3ee', '#fb923c'];
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function crearGrafico(id, valores, barras) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const etiquetas = Object.keys(valores);
        const cantidades = Object.values(valores).map(Number);
        const total = cantidades.reduce((suma, valor) => suma + valor, 0);
        const fondos = etiquetas.map((etiqueta, indice) => colores[etiqueta] || alternativos[indice % alternativos.length]);
        const opciones = {
            responsive: true, maintainAspectRatio: false,
            animation: reducirMovimiento ? false : { duration: 650 },
            layout: { padding: 12 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 18, usePointStyle: true, pointStyle: 'circle' },
                    onHover(evento, item, leyenda) {
                        leyenda.chart.canvas.style.cursor = 'pointer';
                        leyenda.chart.setActiveElements([{ datasetIndex: barras ? item.datasetIndex : 0, index: barras ? 0 : item.index }]);
                        leyenda.chart.update('none');
                    },
                    onLeave(evento, item, leyenda) {
                        leyenda.chart.canvas.style.cursor = 'default';
                        leyenda.chart.setActiveElements([]);
                        leyenda.chart.update('none');
                    }
                },
                tooltip: { callbacks: { label(contexto) {
                    const cantidad = barras ? contexto.parsed.x : contexto.parsed;
                    const etiqueta = barras ? contexto.dataset.label : contexto.label;
                    return `${etiqueta}: ${cantidad} (${(cantidad / total * 100).toFixed(1)}%)`;
                } } }
            }
        };
        if (barras) {
            opciones.indexAxis = 'y';
            opciones.scales = {
                x: { beginAtZero: true, ticks: { precision: 0 }, border: { display: false } },
                y: { grid: { display: false }, border: { display: false } }
            };
        } else {
            opciones.cutout = '68%';
        }
        graficos.push(new Chart(canvas, {
            type: barras ? 'bar' : 'doughnut',
            data: barras ? {
                labels: ['Tickets activos'],
                datasets: etiquetas.map((etiqueta, indice) => ({
                    label: etiqueta, data: [cantidades[indice]], backgroundColor: fondos[indice],
                    borderRadius: 6, borderSkipped: false, maxBarThickness: 38
                }))
            } : {
                labels: etiquetas,
                datasets: [{ data: cantidades, backgroundColor: fondos, borderWidth: 3, hoverOffset: 12 }]
            },
            options: opciones
        }));
    }
    crearGrafico('graficoEstadosTecnico', datos.estados, false);
    crearGrafico('graficoPrioridadesTecnico', datos.prioridades, true);

    function actualizarTema() {
        const estilos = getComputedStyle(document.documentElement);
        const texto = estilos.getPropertyValue('--text-primary').trim();
        graficos.forEach(grafico => {
            grafico.options.plugins.legend.labels.color = texto;
            if (grafico.config.type === 'bar') {
                grafico.options.scales.x.ticks.color = texto;
                grafico.options.scales.y.ticks.color = texto;
                grafico.options.scales.x.grid.color = estilos.getPropertyValue('--outline').trim();
            } else {
                grafico.data.datasets[0].borderColor = estilos.getPropertyValue('--panel').trim();
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
        const fechaFormateada = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
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
            window.cambiarTema = function (tema) { document.documentElement.classList.add('theme-ready'); aplicarTema(tema); };
            document.addEventListener('DOMContentLoaded', function () {
                aplicarTema(temaInicial, false);
                setTimeout(function () { document.documentElement.classList.add('theme-ready'); }, 1000);
            });
        }());
    </script>
</body>
</html>
