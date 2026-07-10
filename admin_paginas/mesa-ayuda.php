<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de ayuda</title>
    <link rel="stylesheet" href="../style.css?v=6">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand">Novaris</a>
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
            <a href="index-admin.php" class="side-link" id="inicio">Inicio</a>
        </div>
        <div class="inventario-link">
            <a href="inventario.php" class="side-link" id="inventario">Inventario</a>
        </div>
        <div class="mesa-ayuda-link">
            <a href="mesa-ayuda.php" class="side-link" id="mesaAyuda">Mesa de ayuda</a>
        </div>
        <div class="solicitudes-link">
            <a href="solicitudes.php" class="side-link" id="solicitudes">Solicitudes de servicios</a>
        </div>
        <div class="reportes-link">
            <a href="reportes.php" class="side-link" id="reportes">Reportes</a>
        </div>
        <div class="configuracion-link">
            <a href="configuracion.php" class="side-link" id="configuracion">Configuración</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">Mesa de ayuda</div>
        <p>Completa el formulario para crear una solicitud de soporte. Tus mensajes se guardan en la base de datos y aparecen en esta misma página.</p>
    </div>

    <section class="help-page">


        <div class="help-grid">
            <form method="post" class="help-form">
                <h2>Enviar solicitud de soporte</h2>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <input type="text" id="asunto" name="asunto" placeholder="Breve descripción" required>
                </div>
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" placeholder="Describe tu problema" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>

            <div class="ticket-list">
                <h2>Últimas solicitudes</h2>
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
                                <tr>
                                    <td>1</td>
                                    <td>Juan Pérez</td>
                                    <td>Problema con impresora</td>
                                    <td>Abierto</td>
                                    <td>10/07/2026</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>María López</td>
                                    <td>Acceso al sistema</td>
                                    <td>En proceso</td>
                                    <td>09/07/2026</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Carlos Ruiz</td>
                                    <td>Error al iniciar sesión</td>
                                    <td>Cerrado</td>
                                    <td>08/07/2026</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    <script src="../paginas/idioma.js"></script>
</body>
</html>