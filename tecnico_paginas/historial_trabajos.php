<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de trabajos — Técnico</title>
    <link rel="stylesheet" href="../style.css?v=8">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-tecnico.php" class="brand">Novaris</a>
            <a href="index-tecnico.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <div class="idioma">
                <select name="idioma" id="idioma">
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </select>
            </div>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <a href="index-tecnico.php" class="side-link">Inicio</a>
        <a href="mis_tickets.php" class="side-link">Mis tickets</a>
        <a href="historial_trabajos.php" class="side-link">Historial de trabajos</a>
        <a href="equipos_tecnico.php" class="side-link">Equipos</a>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">Historial de trabajos</div>
        <p>Consulta los cierres y mantenimientos realizados previamente.</p>
    </div>

    <section class="help-page">
        <div class="ticket-list">
            <h2>Trabajos finalizados</h2>
            <div class="ticket-table-wrapper">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipo</th>
                            <th>Detalle</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>301</td>
                            <td>PC Dell</td>
                            <td>Actualización de driver</td>
                            <td>2026-07-08</td>
                        </tr>
                        <tr>
                            <td>302</td>
                            <td>Impresora HP</td>
                            <td>Recarga de tóner</td>
                            <td>2026-07-06</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

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
