<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$solicitudes = [
    ['id' => 101, 'nombre' => 'Juan Pérez', 'asunto' => 'No puedo acceder al correo', 'estado' => 'Pendiente', 'fecha' => '2026-06-12'],
    ['id' => 102, 'nombre' => 'María García', 'asunto' => 'Impresora sin conexión', 'estado' => 'En proceso', 'fecha' => '2026-06-11'],
    ['id' => 103, 'nombre' => 'Carlos López', 'asunto' => 'Actualización de equipo', 'estado' => 'Resuelto', 'fecha' => '2026-06-10'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de ayuda - Novaris</title>
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=7" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=7" media="not all">
    <link rel="stylesheet" href="admin-theme.css?v=1">
</head>
<body class="app-page">

    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand"><span>Nov</span><span>aris</span></a>
            <div class="modo-light theme-mode-control" data-tema="dark">
                <button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.31412 6.31412L5.5 5.5M17.6859 6.31412 18.5 5.5M6.31412 17.69 5.5 18.5001M17.6859 17.69 18.5 18.5001M21 12H20M16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>
            <div class="modo-dark theme-mode-control" data-tema="light" hidden>
                <button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.9 2.31c-.16-.41-.74-.41-.9 0l-.43 1.1a.75.75 0 0 1-.27.27l-1.09.43c-.41.16-.22.15-.27.27l-.43 1.1c-.16.41-.74.41-.9 0l-.43-1.1a.75.75 0 0 0-.27-.27l-1.09-.43c-.41-.16-.41-.74 0-.9l1.09-.43a.75.75 0 0 0 .27-.27l.43-1.1Z" stroke="#1C274C"></path><path d="M21.25 12c0 5.11-4.14 9.25-9.25 9.25A9.25 9.25 0 1 1 12 2.75c.09 0 .1.12.03.16A6.5 6.5 0 0 0 15.5 14.75c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg>
                </button>
            </div>
            <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <a href="index-admin.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <a href="inventario.php" class="side-link">Inventario</a>
        </div>
        <div class="mesa-ayuda-link active">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg>
            <a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a>
        </div>
        <div class="reportes-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.3a3.5 3.5 0 0 1 0 6.4M17 14.2a4.5 4.5 0 0 1 3 4.3V20"/></svg>
            <a href="usuario.php" class="side-link">Usuarios</a>
        </div>
    </div>
<div class="informacion">
    <span id="fechahoy"></span>
    <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
</div>
<main class="help-page">
    <div class="help-grid">
        <section class="help-form">
            <h2>Nueva solicitud</h2>
            <p>Simulación de formulario para registrar una solicitud de soporte.</p>
            <div class="form-group"><label>Nombre</label><input type="text" value="Administrador" readonly></div>
            <div class="form-group"><label>Correo electrónico</label><input type="email" value="admin@novaris.com" readonly></div>
            <div class="form-group"><label>Asunto</label><input type="text" value="Ejemplo de solicitud" readonly></div>
            <div class="form-group"><label>Detalle</label><textarea rows="5" readonly>Describe aquí el problema que necesita atención.</textarea></div>
            <button type="button" class="btn btn-primary">Enviar solicitud</button>
        </section>
        <section class="ticket-list">
            <h2>Solicitudes recientes</h2>
            <div class="ticket-table-wrapper"><table class="ticket-table"><thead><tr><th>ID</th><th>Nombre</th><th>Asunto</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
            <?php foreach ($solicitudes as $solicitud): ?>
                <tr><td><?php echo (int) $solicitud['id']; ?></td><td><?php echo escaparHTML($solicitud['nombre']); ?></td><td><?php echo escaparHTML($solicitud['asunto']); ?></td><td><?php echo escaparHTML($solicitud['estado']); ?></td><td><?php echo escaparHTML($solicitud['fecha']); ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </section>
    </div>
</main>
<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
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
