<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}
$idUsuario = (int) $_SESSION['id_usuario'];
$equipos = [];
$error = '';
$titulo = '';
$descripcion = '';
$prioridad = 'Media';
$idEquipoSeleccionado = 0;

try {
    $conexion = conectarBD();
    $consultaEquipos = 'SELECT e.id_equipo, e.nombre, e.marca, e.modelo
                        FROM historial_asignacion AS h
                        INNER JOIN equipo AS e ON e.id_equipo = h.id_equipo
                        WHERE h.id_usuario = ? AND h.fecha_fin IS NULL
                        ORDER BY e.nombre';
    $stmt = mysqli_prepare($conexion, $consultaEquipos);
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $equipos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    http_response_code(500);
    exit('No se pudieron cargar los equipos asignados.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEquipoSeleccionado = (int) ($_POST['id_equipo'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $prioridad = $_POST['prioridad'] ?? 'Media';
    $prioridadesPermitidas = ['Baja', 'Media', 'Alta'];

    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud expiró. Recarga la página e inténtalo nuevamente.';
    } elseif ($titulo === '' || $descripcion === '' || $idEquipoSeleccionado <= 0) {
        $error = 'Completa todos los campos obligatorios.';
    } elseif (!in_array($prioridad, $prioridadesPermitidas, true)) {
        $error = 'La prioridad seleccionada no es válida.';
    } else {
        try {
            $consultaAsignacion = 'SELECT 1 FROM historial_asignacion WHERE id_usuario = ? AND id_equipo = ? AND fecha_fin IS NULL LIMIT 1';
            $stmt = mysqli_prepare($conexion, $consultaAsignacion);
            mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idEquipoSeleccionado);
            mysqli_stmt_execute($stmt);
            $equipoAsignado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$equipoAsignado) {
                $error = 'Solo puedes crear tickets para equipos asignados a tu cuenta.';
            } else {
                $consultaTicket = 'INSERT INTO ticket (titulo, descripcion, prioridad, id_usuario, id_equipo) VALUES (?, ?, ?, ?, ?)';
                $stmt = mysqli_prepare($conexion, $consultaTicket);
                mysqli_stmt_bind_param($stmt, 'sssii', $titulo, $descripcion, $prioridad, $idUsuario, $idEquipoSeleccionado);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $_SESSION['mensaje_ticket'] = 'Ticket creado correctamente.';
                header('Location: crear_ticket.php');
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            $error = 'No se pudo crear el ticket. Intenta nuevamente.';
        }
    }
}

$mensajeExito = $_SESSION['mensaje_ticket'] ?? '';
unset($_SESSION['mensaje_ticket']);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Crear ticket - Novaris</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=8" media="all"><link id="tema-claro" rel="stylesheet" href="../style_light.css?v=8" media="not all"><link rel="stylesheet" href="../admin_paginas/admin-theme.css?v=1"><script src="solicitante-theme.js"></script></head>
<body class="app-page"><header><nav class="navbar"><a href="index-solicitante.php" class="brand">Novaris</a><a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a><div class="modo-light theme-mode-control" data-tema="dark"><button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.3 6.3 5.5 5.5M17.7 6.3 18.5 5.5M6.3 17.7 5.5 18.5M17.7 17.7 18.5 18.5M21 12H20M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button></div><div class="modo-dark theme-mode-control" data-tema="light" hidden><button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21.25 12a9.25 9.25 0 1 1-9.25-9.25c.1 0 .12.13.03.17a6.5 6.5 0 0 0 3.47 11.83c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg></button></div><a href="../perfil.php" class="nav-cta">Mi perfil</a></nav></header><div class="side-bar"><a href="index-solicitante.php" class="side-link">Inicio</a><a href="crear_ticket.php" class="side-link">Crear ticket</a><a href="mis_equipos.php" class="side-link">Mis equipos</a></div><div class="informacion"><span id="fechahoy"></span><div id="titulo-informacion">Crear ticket</div><p>Registra una solicitud de soporte para uno de tus equipos asignados.</p></div>
<main class="help-page"><div class="help-grid"><section class="help-form"><?php if ($error !== ''): ?><div class="error"><?php echo escaparHTML($error); ?></div><?php endif; ?><?php if ($mensajeExito !== ''): ?><div class="success"><?php echo escaparHTML($mensajeExito); ?></div><?php endif; ?><h2>Nuevo ticket</h2><?php if ($equipos === []): ?><p>No tienes equipos asignados. Solicita la asignación de un equipo antes de crear un ticket.</p><?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo escaparHTML(generarTokenCsrf()); ?>"><div class="form-group"><label for="id_equipo">Equipo afectado</label><select id="id_equipo" name="id_equipo" required><?php foreach ($equipos as $equipo): ?><option value="<?php echo (int) $equipo['id_equipo']; ?>"<?php echo $idEquipoSeleccionado === (int) $equipo['id_equipo'] ? ' selected' : ''; ?>><?php echo escaparHTML($equipo['nombre'] . ' — ' . trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))); ?></option><?php endforeach; ?></select></div><div class="form-group"><label for="prioridad">Prioridad</label><select id="prioridad" name="prioridad"><?php foreach (['Baja', 'Media', 'Alta'] as $opcion): ?><option value="<?php echo $opcion; ?>"<?php echo $prioridad === $opcion ? ' selected' : ''; ?>><?php echo $opcion; ?></option><?php endforeach; ?></select></div><div class="form-group"><label for="titulo">Asunto</label><input id="titulo" name="titulo" type="text" value="<?php echo escaparHTML($titulo); ?>" required maxlength="100"></div><div class="form-group"><label for="descripcion">Detalle del problema</label><textarea id="descripcion" name="descripcion" rows="6" required><?php echo escaparHTML($descripcion); ?></textarea></div><button type="submit" class="btn btn-primary">Enviar solicitud</button></form><?php endif; ?></section><section class="ticket-list"><h2>Indicaciones</h2><p>Describe el problema con el mayor detalle posible para agilizar el soporte.</p><ul><li>Incluye el comportamiento observado.</li><li>Indica si afecta el acceso a internet o programas.</li><li>Adjunta capturas por el canal indicado si son necesarias.</li></ul></section></div></main>
<script>document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });</script></body></html>
