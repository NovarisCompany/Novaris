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


    <div class="inventario-info">
        <h1>Inventario</h1>
        <div class="inventario-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Número de serie</th>
                        <th>Estado</th>
                        <th>Fecha de alta</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                    <tr>
                        <td><div class="cell-content" title="101">101</div></td>
                        <td><div class="cell-content" title="Equipo de cómputo">Equipo de cómputo</div></td>
                        <td><div class="cell-content" title="PC">PC</div></td>
                        <td><div class="cell-content" title="Dell">Dell</div></td>
                        <td><div class="cell-content" title="OptiPlex 3080">OptiPlex 3080</div></td>
                        <td><div class="cell-content" title="SN123456">SN123456</div></td>
                        <td><div class="cell-content" title="Activo">Activo</div></td>
                        <td><div class="cell-content" title="2025-01-10">2025-01-10</div></td>
                        <td><div class="cell-content" title="Oficina Central">Oficina Central</div></td>
                    </tr>
                    <tr>
                        <td><div class="cell-content" title="102">102</div></td>
                        <td><div class="cell-content" title="Impresora Laser">Impresora Laser</div></td>
                        <td><div class="cell-content" title="Impresora">Impresora</div></td>
                        <td><div class="cell-content" title="HP">HP</div></td>
                        <td><div class="cell-content" title="LaserJet M404">LaserJet M404</div></td>
                        <td><div class="cell-content" title="SN654321">SN654321</div></td>
                        <td><div class="cell-content" title="En mantenimiento">En mantenimiento</div></td>
                        <td><div class="cell-content" title="2024-11-02">2024-11-02</div></td>
                        <td><div class="cell-content" title="Planta Baja">Planta Baja</div></td>
                    </tr>
                </table>
            </table>
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
