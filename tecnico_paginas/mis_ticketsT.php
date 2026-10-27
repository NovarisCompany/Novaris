<?php
session_start();
require_once __DIR__ . '/../conexion.php';
if (!isset($_SESSION['id_usuario']) || (int) ($_SESSION['id_rol'] ?? 0) !== 2) {
    header('Location: ../login.php');
    exit;
}

$conexion = conectarBD();



$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$tickets = [];
$error = '';
$mensaje = $_SESSION['mensaje_trabajo'] ?? '';
unset($_SESSION['mensaje_trabajo']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $idTicket = (int) ($_POST['id_ticket'] ?? 0);
        $idUsuario = (int) $_SESSION['id_usuario'];
        $diagnostico = is_string($_POST['diagnostico'] ?? null) ? trim($_POST['diagnostico']) : '';
        $resolucion = is_string($_POST['resolucion'] ?? null) ? trim($_POST['resolucion']) : '';
        $accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';
        if ($idTicket <= 0 || !in_array($accion, ['guardar_avance', 'resolver'], true)) {
            throw new InvalidArgumentException('Ticket o acción inválidos.');
        }
        if (strlen($diagnostico) > 65535 || strlen($resolucion) > 65535) {
            throw new InvalidArgumentException('El diagnóstico o la solución son demasiado largos.');
        }
        if ($accion === 'resolver' && ($diagnostico === '' || $resolucion === '')) {
            throw new InvalidArgumentException('Completa el diagnóstico y la solución antes de resolver el ticket.');
        }
        $estado = $accion === 'resolver' ? 'Resuelto' : 'En proceso';
        $stmt = mysqli_prepare($conexion, "UPDATE ticket t
            INNER JOIN tecnico te ON te.id_tecnico = t.id_tecnico
            SET t.diagnostico = ?, t.resolucion = ?, t.estado = ?,
                t.fecha_cierre = IF(? = 'Resuelto', NOW(), NULL)
            WHERE t.id_ticket = ? AND te.id_usuario = ? AND te.estado = 'Activo'
                AND t.estado <> 'Resuelto'");
        mysqli_stmt_bind_param($stmt, 'ssssii', $diagnostico, $resolucion, $estado, $estado, $idTicket, $idUsuario);
        mysqli_stmt_execute($stmt);
        $actualizados = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['mensaje_trabajo'] = $actualizados > 0
            ? ($accion === 'resolver' ? 'Ticket resuelto correctamente.' : 'Avance guardado.')
            : 'No hubo cambios. Comprueba que el ticket siga activo y asignado a tu cuenta.';
        header('Location: mis_ticketsT.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
        $error = 'No se pudo guardar el trabajo. Inténtalo nuevamente.';
    }
}

try {
    
    $idUsuario = (int) $_SESSION['id_usuario'];
    $consulta = "SELECT t.id_ticket, t.titulo, t.descripcion, t.prioridad, t.estado,
        t.fecha_creacion, t.diagnostico, t.resolucion, e.nombre AS equipo_nombre,
        tu.nombre AS tecnico_nombre, tu.apellido AS tecnico_apellido
        FROM ticket AS t
        INNER JOIN equipo AS e ON e.id_equipo = t.id_equipo
        LEFT JOIN tecnico AS te ON te.id_tecnico = t.id_tecnico
        LEFT JOIN usuario AS tu ON tu.id_usuario = te.id_usuario
        WHERE te.id_usuario = ? AND te.estado = 'Activo' AND tu.id_rol = 2 AND t.estado <> 'Resuelto'
        ORDER BY CASE t.prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 ELSE 3 END, t.fecha_creacion DESC, t.id_ticket DESC";
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
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=9" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=9" media="not all">
    <link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=2">
</head>
<body class="app-page">
    <header><nav class="navbar">
        <a href="index-tecnico.php" class="brand"><span>Nov</span><span>aris</span></a>
        <div class="modo-light theme-mode-control" data-tema="dark"><button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.31412 6.31412L5.5 5.5M17.6859 6.31412L18.5 5.5M6.31412 17.69L5.5 18.5001M17.6859 17.69L18.5 18.5001M21 12H20M16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button></div>
        <div class="modo-dark theme-mode-control" data-tema="light" hidden><button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.9 2.31c-.16-.41-.74-.41-.9 0l-.43 1.1a.75.75 0 0 1-.27.27l-1.09.43c-.41.16-.41.74 0 .9l1.09.43c.13.05.22.15.27.27l.43 1.1c.16.41.74.41.9 0l.43-1.1a.75.75 0 0 1 .27-.27l1.09-.43c.41-.16.41-.74 0-.9l-1.09-.43a.75.75 0 0 1-.27-.27l-.43-1.1Z" stroke="#1C274C"></path><path d="M21.25 12c0 5.11-4.14 9.25-9.25 9.25A9.25 9.25 0 1 1 12 2.75c.09 0 .1.12.03.16A6.5 6.5 0 0 0 15.5 14.75c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg></button></div>
        <a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><a href="../perfil.php" class="nav-cta">Mi Perfil</a>
            
    </nav></header>
    <div class="side-bar">
        <div class="home-link"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><a href="index-tecnico.php" class="side-link">Inicio</a></div>
        <div class="inventario-link active"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg><a href="mis_ticketsT.php" class="side-link">Mis tickets</a></div>
        <div class="mesa-ayuda-link"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg><a href="historial_trabajos.php" class="side-link">Historial de trabajos</a></div>
        <div class="reportes-link"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M8 11h8M8 15h5" stroke-linecap="round"/></svg><a href="equipos_tecnico.php" class="side-link">Equipos</a></div>
    </div>
    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
        <p>Revisa los tickets activos asignados a tu cuenta.</p>
    </div>

    <main class="help-page technician-ticket-page">
        <section class="ticket-list technician-tickets">
            <h2>Mis tickets activos</h2>
            <p>Registra el diagnóstico y la solución desde esta lista. Los resueltos aparecen en <a href="historial_trabajos.php">Historial de trabajos</a>.</p>
            <?php if ($mensaje !== ''): ?><div class="mensaje-exito"><?php echo escaparHTML($mensaje); ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="mensaje-error" role="alert"><?php echo escaparHTML($error); ?></div><?php endif; ?>
            <div class="technician-ticket-list">
                <?php if ($tickets === [] && $error === ''): ?>
                    <p class="ticket-empty">No tienes tickets activos asignados.</p>
                <?php endif; ?>
                <?php foreach ($tickets as $ticketItem): ?>
                    <article class="technician-ticket">
                        <div class="ticket-heading">
                            <br>
                            <div>
                                <h3><?php echo escaparHTML($ticketItem['titulo']); ?></h3>
                                <span class="ticket-number">Ticket #<?php echo (int) $ticketItem['id_ticket']; ?></span>
                                <br>
                            </div>
                            <div class="ticket-tags">
                                <span>Prioridad: <?php echo escaparHTML($ticketItem['prioridad']); ?></span>
                                <span><?php echo escaparHTML($ticketItem['estado']); ?></span>
                            </div>
                        </div>
                        <div class="ticket-meta">
                            <span><strong>Equipo:</strong> <?php echo escaparHTML($ticketItem['equipo_nombre']); ?></span>
                            <span><strong>Creado:</strong> <?php echo escaparHTML($ticketItem['fecha_creacion']); ?></span>
                        </div>
                        <p class="ticket-description"><?php echo nl2br(escaparHTML($ticketItem['descripcion'])); ?></p>
                        <form method="post" class="ticket-work-form">
                            <input type="hidden" name="id_ticket" value="<?php echo (int) $ticketItem['id_ticket']; ?>">
                            <div class="form-group"><label>Diagnóstico<textarea name="diagnostico" rows="3"><?php echo escaparHTML($error !== '' && (int) ($_POST['id_ticket'] ?? 0) === (int) $ticketItem['id_ticket'] ? (is_string($_POST['diagnostico'] ?? null) ? $_POST['diagnostico'] : '') : ($ticketItem['diagnostico'] ?? '')); ?></textarea></label></div>
                            <br>
                            <div class="form-group"><label>Solución<textarea name="resolucion" rows="3"><?php echo escaparHTML($error !== '' && (int) ($_POST['id_ticket'] ?? 0) === (int) $ticketItem['id_ticket'] ? (is_string($_POST['resolucion'] ?? null) ? $_POST['resolucion'] : '') : ($ticketItem['resolucion'] ?? '')); ?></textarea></label></div>
                            <div class="ticket-actions">
                                <button class="btn btn-secondary" name="accion" value="guardar_avance">Guardar avance</button>
                                <button class="btn btn-primary" name="accion" value="resolver">Resolver</button>
                            </div>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

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

                if (guardar) {
                    localStorage.setItem('novaris-tema', tema);
                }
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
