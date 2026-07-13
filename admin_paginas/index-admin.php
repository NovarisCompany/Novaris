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
            <a href="index-admin.php" class="brand">Novaris</a>
            <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
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
        <div class="home-link">
            <a href="index-admin.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <a href="inventario.php" class="side-link">Inventario</a>
        </div>
        <div class="mesa-ayuda-link">
            <a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a>
        </div>
        <div class="reportes-link">
            <a href="usuario.php" class="side-link">Usuarios</a>
        </div>
      
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> Administrador
        </div>

        <div class="info-resumen">
            <div class="tickets">
                <h3>Tickets en proceso:</h3>
                <p>6</p>
                <h4>Tickets pendientes:</h4>
                <p>3</p>
            </div>
            <div class="inventario">
                <h3>Usuarios activos:</h3>
                <p>20</p>
                <h4>Equipos registrados:</h4>
                <p>80</p>
            </div>
        </div>
    </div>

    <div class="ultimos_tickets">
        <h3>Últimos tickets:</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Problema con la impresora</td>
                    <td>En proceso</td>
                    <td>2024-06-01</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>No puedo acceder al correo</td>
                    <td>Pendiente</td>
                    <td>2024-06-02</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Problema con el mouse</td>
                    <td>Resuelto</td>
                    <td>2024-06-03</td>
                </tr>
            </tbody>
        </table>
        </div>
    <div class="acciones_rapidas">
        <h3>Acciones rápidas:</h3>
        <div class="acciones-botones">
            <a class="accion-botones">Registrar equipo </a>
            <a  class="accion-botones">Crear usuario</a>
            <a class="accion-botones">Asignar tecnico</a>
            <a class="accion-botones">Generar Reporte</a>
        </div>
    </div>

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
