<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novaris</title>
    <link rel="stylesheet" href="../style.css?v=6">
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
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> Técnico Juan
        </div>
        <p>Gestiona tus tickets y revisa el estado de los equipos asignados.</p>
    </div>

    <section class="help-page">
        <div class="help-grid">
            <div class="help-form">
                <h2>Acciones rápidas</h2>
                <div class="acciones-botones">
                    <a href="mis_tickets.php" class="accion-botones">Ver tickets asignados</a>
                    <a href="historial_trabajos.php" class="accion-botones">Ver historial</a>
                    <a href="equipos_tecnico.php" class="accion-botones">Ver equipos</a>
                </div>
            </div>

            <div class="ticket-list">
                <h2>Resumen del día</h2>
                <p>Actualmente tienes 3 tickets activos y 2 equipos en mantenimiento.</p>
                <ul>
                    <li>Ticket #201 — Impresora sin respuesta</li>
                    <li>Ticket #202 — Acceso a la red</li>
                    <li>Equipo Lenovo — En revisión</li>
                </ul>
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
