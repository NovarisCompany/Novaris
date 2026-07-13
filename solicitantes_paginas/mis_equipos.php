<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis equipos — Solicitante</title>
    <link rel="stylesheet" href="../style.css?v=8">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-solicitante.php" class="brand">Novaris</a>
            <a href="index-solicitante.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
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
        <a href="index-solicitante.php" class="side-link">Inicio</a>
        <a href="crear_ticket.php" class="side-link">Crear ticket</a>
        <a href="mis_equipos.php" class="side-link">Mis equipos</a>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">Mis equipos</div>
        <p>Consulta el estado actual de los equipos asignados a tu cuenta.</p>
    </div>

    <section class="help-page">
        <div class="ticket-list">
            <h2>Equipos asignados</h2>
            <div class="ticket-table-wrapper">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Última revisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Laptop HP 14</td>
                            <td>HP</td>
                            <td>Operativo</td>
                            <td>2026-07-08</td>
                        </tr>
                        <tr>
                            <td>Monitor Dell 24</td>
                            <td>Dell</td>
                            <td>En mantenimiento</td>
                            <td>2026-07-05</td>
                        </tr>
                        <tr>
                            <td>Teclado inalámbrico</td>
                            <td>Logitech</td>
                            <td>Operativo</td>
                            <td>2026-07-02</td>
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
