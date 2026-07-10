<?php
require_once __DIR__ . '/../conexion.php';

$mensajeExito = '';
$mensajeError = '';
$nombre = '';
$email = '';
$asunto = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
        $mensajeError = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'Ingresa un correo electrónico válido.';
    } else {
        try {
            $conexion = conectarBD();
            $stmt = mysqli_prepare(
                $conexion,
                'INSERT INTO mesa_ayuda (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)'
            );

            mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $email, $asunto, $mensaje);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $mensajeExito = 'Solicitud enviada correctamente. Nuestro equipo revisará tu mensaje pronto.';
                $nombre = '';
                $email = '';
                $asunto = '';
                $mensaje = '';
            } else {
                $mensajeError = 'No se pudo guardar la solicitud. Intenta nuevamente.';
            }

            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
        } catch (mysqli_sql_exception $e) {
            $mensajeError = 'Error al conectar con la base de datos: ' . $e->getMessage();
        }
    }
}

$tickets = [];
try {
    $conexion = conectarBD();
    $resultado = mysqli_query(
        $conexion,
        'SELECT id, nombre, asunto, estado, creado_en FROM mesa_ayuda ORDER BY creado_en DESC LIMIT 20'
    );

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $tickets[] = $fila;
    }

    mysqli_free_result($resultado);
    mysqli_close($conexion);
} catch (mysqli_sql_exception $e) {
    if ($mensajeError === '') {
        $mensajeError = 'No se pudo cargar la lista de solicitudes. ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de ayuda</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index.php" class="brand">Novaris</a>
            <div class="idioma">
                <select name="idioma" id="idioma">
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </select>
            </div>
            <a href="#" class="nav-cta" id="perfil">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link">
            <a href="index.php" class="side-link" id="inicio">Inicio</a>
        </div>
        <div class="inventario-link">
            <a href="paginas/inventario.html" class="side-link" id="inventario">Inventario</a>
        </div>
        <div class="mesa-ayuda-link">
            <a href="mesa-ayuda.php" class="side-link" id="mesaAyuda">Mesa de ayuda</a>
        </div>
        <div class="solicitudes-link">
            <a href="paginas/solicitudes.html" class="side-link" id="solicitudes">Solicitudes de servicios</a>
        </div>
        <div class="reportes-link">
            <a href="paginas/reportes.html" class="side-link" id="reportes">Reportes</a>
        </div>
        <div class="configuracion-link">
            <a href="paginas/configuracion.html" class="side-link" id="configuracion">Configuración</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">Mesa de ayuda</div>
        <p>Completa el formulario para crear una solicitud de soporte. Tus mensajes se guardan en la base de datos y aparecen en esta misma página.</p>
    </div>

    <section class="help-page">
        <?php if ($mensajeError): ?>
            <div class="error"><?php echo escaparHTML($mensajeError); ?></div>
        <?php elseif ($mensajeExito): ?>
            <div class="success"><?php echo escaparHTML($mensajeExito); ?></div>
        <?php endif; ?>

        <div class="help-grid">
            <form method="post" class="help-form">
                <h2>Enviar solicitud de soporte</h2>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo escaparHTML($nombre); ?>" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo escaparHTML($email); ?>" placeholder="ejemplo@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <input type="text" id="asunto" name="asunto" value="<?php echo escaparHTML($asunto); ?>" placeholder="Breve descripción" required>
                </div>
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" placeholder="Describe tu problema" required><?php echo escaparHTML($mensaje); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>

            <div class="ticket-list">
                <h2>Últimas solicitudes</h2>
                <?php if (count($tickets) === 0): ?>
                    <p>No hay solicitudes registradas aún.</p>
                <?php else: ?>
                    <div class="ticket-table-wrapper">
                        <table class="ticket-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo escaparHTML($ticket['id']); ?></td>
                                        <td><?php echo escaparHTML($ticket['nombre']); ?></td>
                                        <td><?php echo escaparHTML($ticket['asunto']); ?></td>
                                        <td><?php echo escaparHTML($ticket['estado']); ?></td>
                                        <td><?php echo escaparHTML($ticket['creado_en']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        const fechaFormateada = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        document.getElementById('fechahoy').textContent = fechaFormateada;
    </script>
    <script src="idioma.js"></script>
</body>
</html>