<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novaris — Solicitante</title>
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
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> Solicitante
        </div>
        <p>Gestiona tus solicitudes y revisa el estado de tus equipos de forma rápida.</p>
    </div>

    <section class="help-page">
        <div class="help-grid">
            <div class="help-form">
                <h2>Acciones rápidas</h2>
                <div class="acciones-botones">
                    <a href="crear_ticket.php" class="accion-botones">Crear nuevo ticket</a>
                    <a href="mis_equipos.php" class="accion-botones">Ver mis equipos</a>
                </div>
            </div>

            <div class="ticket-list">
                <h2>Resumen del día</h2>
                <p>Actualmente tienes 2 tickets activos y 1 equipo en mantenimiento.</p>
                <ul>
                    <li>Ticket #104 — Impresora sin respuesta</li>
                    <li>Ticket #105 — Acceso a la red</li>
                    <li>Equipo HP EliteBook — En revisión</li>
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
