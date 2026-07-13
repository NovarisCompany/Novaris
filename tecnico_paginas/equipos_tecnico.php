<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos — Técnico</title>
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
        <div id="titulo-informacion">Equipos</div>
        <p>Maqueta para revisar el inventario y el estado de equipos asignados.</p>
    </div>

    <section class="help-page">
        <div class="ticket-list">
            <h2>Listado de equipos</h2>
            <div class="ticket-table-wrapper">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Portátil Lenovo</td>
                            <td>Operativo</td>
                            <td>Oficina 1</td>
                            <td>Sin incidencias</td>
                        </tr>
                        <tr>
                            <td>Impresora HP</td>
                            <td>Mantenimiento</td>
                            <td>Recepción</td>
                            <td>Se requiere revisión</td>
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
