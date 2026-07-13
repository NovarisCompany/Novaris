<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis tickets — Técnico</title>
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
        <div id="titulo-informacion">Mis tickets</div>
        <p>Revisa y gestiona los tickets asignados a tu cuenta.</p>
    </div>

    <section class="help-page">
        <div class="ticket-list">
            <h2>Tickets activos</h2>
            <div class="ticket-table-wrapper">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>Asunto</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>201</td>
                            <td>María López</td>
                            <td>Impresora sin respuesta</td>
                            <td>Alta</td>
                            <td>En proceso</td>
                        </tr>
                        <tr>
                            <td>202</td>
                            <td>Carlos Ruiz</td>
                            <td>Acceso a red</td>
                            <td>Media</td>
                            <td>Pendiente</td>
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
