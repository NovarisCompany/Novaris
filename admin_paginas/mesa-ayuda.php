<?php
require_once __DIR__ . '/../conexion.php';

$mensajeExito = '';
$mensajeError = '';
$nombre = '';
$email = '';
$asunto = '';
$mensaje = '';
$nombreCompleto = trim(($_SESSION["nombre"] ?? "") . " " . ($_SESSION["apellido"] ?? ""));


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
    <link rel="stylesheet" href="../style.css?v=1">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand">Novaris</a>
            <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <div class="idioma">
                <select name="idioma" id="idioma">
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </select>
            </div>
            <a href="../perfil.php" class="nav-cta" id="perfil">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link"><a href="../index.php" class="side-link" id="inicio">Inicio</a></div>
        <div class="inventario-link"><a href="inventario.php" class="side-link" id="inventario">Inventario</a></div>
        <div class="mesa-ayuda-link"><a href="mesa-ayuda.php" class="side-link" id="mesaAyuda">Mesa de ayuda</a></div>
        <div class="usuarios-link"><a href="usuario.php" class="side-link" id="usuarios">Usuarios</a></div>
    </div>

     <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">Mesa de ayuda</div>
        <p>Completa el formulario para crear una solicitud de soporte</p>
    </div>

    <section class="help-page">
        <?php if ($mensajeError): ?>
            <div class="error"><?php echo htmlspecialchars($mensajeError); ?></div>
        <?php elseif ($mensajeExito): ?>
            <div class="success"><?php echo htmlspecialchars($mensajeExito); ?></div>
        <?php endif; ?>

        <div class="help-grid">
            <form method="post" class="help-form">
                <h2>Enviar solicitud de soporte</h2>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="ejemplo@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <input type="text" id="asunto" name="asunto" value="<?php echo htmlspecialchars($asunto); ?>" placeholder="Breve descripción" required>
                </div>
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" placeholder="Describe tu problema" required><?php echo htmlspecialchars($mensaje); ?></textarea>
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
                                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['estado']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['creado_en']); ?></td>
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
        const fechaFormateada = new Date().toLocaleDateString("es-ES", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        });

        document.getElementById("fechahoy").textContent = fechaFormateada;
    </script>

</body>
</html>