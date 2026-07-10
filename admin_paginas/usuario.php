<?php
require_once "../conexion.php";
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
        <div class="solicitudes-link">
            <a href="solicitudes.php" class="side-link">Solicitudes de servicios</a>
        </div>
        <div class="reportes-link">
            <a href="reportes.php" class="side-link">Reportes</a>
        </div>
    </div>


    <div class="usuario-info">
        <h1>Usuarios</h1>
        <p>Administración de cuentas registradas en el sistema.</p>
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
</html>
