<?php
session_start();
require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
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
        <div class="usuarios-link">
            <a href="usuario.php" class="side-link">Usuarios</a>
        </div>
    </div>


         <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?>
        </div>
    </div>
        <div class="usuario-table">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Adrian Silva</td>
                        <td>adrian@novaris.com</td>
                        <td>Administrador</td>
                        <td>Activo</td>
                    </tr>
                    <tr>
                        <td>Eric Cuadra</td>
                        <td>eric@novaris.com</td>
                        <td>Técnico</td>
                        <td>Activo</td>
                    </tr>
                    <tr>
                        <td>Lucía Gómez</td>
                        <td>lucia@novaris.com</td>
                        <td>Solicitante</td>
                        <td>Pendiente</td>
                    </tr>
                    <tr>
                        <td>Martín Pérez</td>
                        <td>martin@novaris.com</td>
                        <td>Solicitante</td>
                        <td>Activo</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
            
</body>

    <script>
        const fechaFormateada = new Date().toLocaleDateString("es-ES", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        });

        document.getElementById("fechahoy").textContent = fechaFormateada;
    </script>
</html>
