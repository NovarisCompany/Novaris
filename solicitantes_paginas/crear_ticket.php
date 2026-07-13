<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear ticket — Solicitante</title>
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
        <div id="titulo-informacion">Crear ticket</div>
        <p>Registra una solicitud de soporte para un equipo o acceso.</p>
    </div>

    <section class="help-page">
        <div class="help-grid">
            <form class="help-form" onsubmit="event.preventDefault(); alert('Maqueta: ticket simulado');">
                <h2>Nuevo ticket</h2>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" type="text" placeholder="Tu nombre">
                </div>
                <div class="form-group">
                    <label for="email">Correo</label>
                    <input id="email" type="email" placeholder="ejemplo@dominio.com">
                </div>
                <div class="form-group">
                    <label for="equipo">Equipo afectado</label>
                    <input id="equipo" type="text" placeholder="Ej. Laptop HP 14">
                </div>
                <div class="form-group">
                    <label for="prioridad">Prioridad</label>
                    <select id="prioridad" name="prioridad">
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <input id="asunto" type="text" placeholder="Breve descripción">
                </div>
                <div class="form-group">
                    <label for="mensaje">Detalle del problema</label>
                    <textarea id="mensaje" rows="6" placeholder="Describe tu problema"></textarea>
                </div>
                <button class="btn btn-primary">Enviar solicitud</button>
            </form>

            <div class="ticket-list">
                <h2>Indicaciones</h2>
                <p>Describe el problema con el mayor detalle posible para agilizar el soporte.</p>
                <ul>
                    <li>Incluye el nombre del equipo.</li>
                    <li>Indica si afecta el acceso a internet o programas.</li>
                    <li>Adjunta capturas si es necesario.</li>
                </ul>
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
