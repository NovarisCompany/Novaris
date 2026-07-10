<?php
session_start();
require_once __DIR__ . "/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$nombreCompleto = trim(($_SESSION["nombre"] ?? "") . " " . ($_SESSION["apellido"] ?? ""));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novaris</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand">Novaris</a>
            <a href="index-admin.php"><img src="Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <div class="idioma">
                <select name="idioma" id="idioma">
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </select>
            </div>
            <a href="paginas/perfil.php" class="nav-cta">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link">
            <a href="index-admin.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <a href="paginas/inventario.php" class="side-link">Mis tickets</a>
        </div>
        <div class="mesa-ayuda-link">
            <a href="paginas/mesa-ayuda.php" class="side-link">Mesa de ayuda</a>
        </div>
        <div class="solicitudes-link">
            <a href="paginas/solicitudes.html" class="side-link">Inventario</a>
        </div>
        <div class="reportes-link">
            <a href="paginas/reportes.html" class="side-link">Historial de trabajos</a>
        </div>
      
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?>
        </div>

        <div class="info-resumen">
            <div class="tickets">
                <h3>Tickets en proceso:</h3>
                <p>6</p>
                <h4>Tickets pendientes:</h4>
                <p>3</p>
            </div>
            <div class="inventario">
                <h3>Inventario:</h3>
                <p>120</p>
                <h4>Equipos disponibles:</h4>
                <p>80</p>
            </div>
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
