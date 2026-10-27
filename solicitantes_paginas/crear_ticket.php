<?php
session_start();
require_once __DIR__ . '/../conexion.php';
if (!isset($_SESSION['id_usuario']) || (int) ($_SESSION['id_rol'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

$conexion = conectarBD();



$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$idUsuario = (int) $_SESSION['id_usuario'];
$equipos = [];
$error = '';
$idEquipo = 0;
$titulo = '';
$descripcion = '';
$prioridad = 'Media';

// Se cambia después de crear el ticket para evitar envíos duplicados.
if (!isset($_SESSION['token_crear_ticket'])) {
    $_SESSION['token_crear_ticket'] = bin2hex(random_bytes(32));
}

try {
    
    $stmt = mysqli_prepare($conexion, "SELECT DISTINCT e.id_equipo, e.nombre
        FROM historial_asignacion AS h
        INNER JOIN equipo AS e ON e.id_equipo = h.id_equipo
        WHERE h.id_usuario = ? AND h.fecha_fin IS NULL AND e.estado_equipo <> 'Baja'
        ORDER BY e.nombre");
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $equipos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEquipo = filter_var($_POST['id_equipo'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
        $descripcion = is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '';
        $prioridad = is_string($_POST['prioridad'] ?? null) ? $_POST['prioridad'] : '';
        $token = $_POST['token'] ?? '';

        if (!is_string($token) || !hash_equals($_SESSION['token_crear_ticket'], $token)) {
            throw new InvalidArgumentException('El formulario venció o ya fue enviado. Revisa los datos e inténtalo otra vez.');
        }
        if ($titulo === '' || $descripcion === '' || !in_array($prioridad, ['Baja', 'Media', 'Alta'], true)) {
            throw new InvalidArgumentException('Completa el título, la descripción y una prioridad válida.');
        }
        if (mb_strlen($titulo, 'UTF-8') > 100 || strlen($descripcion) > 65535) {
            throw new InvalidArgumentException('El título admite hasta 100 caracteres. La descripción no puede superar 65535 bytes.');
        }
        if (!$idEquipo) {
            throw new InvalidArgumentException('Selecciona uno de tus equipos.');
        }

        // Se comprueba la asignación otra vez al guardar, no solo al mostrar el formulario.
        $stmt = mysqli_prepare($conexion, "INSERT INTO ticket
            (titulo, descripcion, prioridad, estado, id_usuario, id_equipo)
            SELECT ?, ?, ?, 'Pendiente', ?, e.id_equipo
            FROM equipo AS e
            WHERE e.id_equipo = ? AND e.estado_equipo <> 'Baja'
              AND EXISTS (SELECT 1 FROM historial_asignacion AS h
                  WHERE h.id_equipo = e.id_equipo AND h.id_usuario = ? AND h.fecha_fin IS NULL)");
        mysqli_stmt_bind_param($stmt, 'sssiii', $titulo, $descripcion, $prioridad, $idUsuario, $idEquipo, $idUsuario);
        mysqli_stmt_execute($stmt);
        $creado = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        if ($creado !== 1) {
            throw new InvalidArgumentException('El equipo seleccionado ya no está asignado a tu cuenta o está dado de baja.');
        }
        $idTicket = mysqli_insert_id($conexion);
        $_SESSION['token_crear_ticket'] = bin2hex(random_bytes(32));
        $_SESSION['mensaje_ticket'] = 'Ticket creado correctamente. Está pendiente de asignación.';
        header('Location: mis_ticketsS.php');
        exit;
    }
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
} catch (mysqli_sql_exception $exception) {
    $error = 'No se pudo cargar o guardar el ticket. Inténtalo nuevamente.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear ticket - Novaris</title>
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
        <div class="inventario-link active">
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
        <p>Registra una solicitud de soporte para uno de tus equipos asignados.</p>
    </div>

    <main class="help-page">
        <div class="help-grid">
            <section class="help-form">
                <h2>Nuevo ticket</h2>
                <?php if ($error !== ''): ?><div class="mensaje-error" role="alert"><?php echo escaparHTML($error); ?></div><?php endif; ?>
                <?php if ($equipos === [] && $error === ''): ?><p>No tienes equipos asignados disponibles para crear tickets. Solicita la asignación al administrador.</p><?php endif; ?>
            <form method="post">
                <input type="hidden" name="token" value="<?php echo escaparHTML($_SESSION['token_crear_ticket']); ?>">
                <div class="form-group"><label for="equipo-afectado">Equipo afectado</label><select id="equipo-afectado" name="id_equipo" required>
                    <option value="">Seleccionar equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                        <option value="<?php echo (int) $equipo['id_equipo']; ?>"<?php echo (int) $idEquipo === (int) $equipo['id_equipo'] ? ' selected' : ''; ?>>
                            <?php echo escaparHTML($equipo['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select></div>
                <div class="form-group"><label for="ticket-titulo">Título</label><input id="ticket-titulo" name="titulo" type="text" required maxlength="100" value="<?php echo escaparHTML($titulo); ?>"></div>
                <div class="form-group"><label for="ticket-descripcion">Descripción</label><textarea id="ticket-descripcion" name="descripcion" rows="5" required><?php echo escaparHTML($descripcion); ?></textarea></div>
                <div class="form-group"><label for="ticket-prioridad">Prioridad</label><select id="ticket-prioridad" name="prioridad" required>
                    <?php foreach (['Baja', 'Media', 'Alta'] as $opcion): ?>
                        <option value="<?php echo $opcion; ?>"<?php echo $prioridad === $opcion ? ' selected' : ''; ?>><?php echo $opcion; ?></option>
                    <?php endforeach; ?>
                </select></div>
                <button type="submit" class="btn btn-primary"<?php echo $equipos === [] ? ' disabled' : ''; ?>>Crear ticket</button>
            </form>
            </section>

            <section class="ticket-list">
                <h2>Indicaciones</h2>
                <p>Describe el problema con el mayor detalle posible para agilizar el soporte.</p>
                <ul>
                    <li>Incluye el comportamiento observado.</li>
                    <li>Indica cuándo comenzó el problema.</li>
                    <li>Selecciona la prioridad adecuada.</li>
                </ul>
            </section>
        </div>
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
