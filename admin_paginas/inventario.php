<?php
require_once "../conexion.php";

$conexion = conectarBD();

$consulta = "
    SELECT 
        e.id_equipo,
        e.nombre,
        e.tipo,
        e.marca,
        e.modelo,
        e.numero_serie,
        e.estado_equipo,
        e.fecha_alta,
        a.ubicacion
    FROM equipo e
    INNER JOIN area a ON e.id_area = a.id_area
";

$resultado = mysqli_query($conexion, $consulta);
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
               
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["id_equipo"]); ?>"><?php echo escaparHTML($fila["id_equipo"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["nombre"]); ?>"><?php echo escaparHTML($fila["nombre"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["tipo"]); ?>"><?php echo escaparHTML($fila["tipo"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["marca"]); ?>"><?php echo escaparHTML($fila["marca"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["modelo"]); ?>"><?php echo escaparHTML($fila["modelo"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["numero_serie"]); ?>"><?php echo escaparHTML($fila["numero_serie"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["estado_equipo"]); ?>"><?php echo escaparHTML($fila["estado_equipo"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["fecha_alta"]); ?>"><?php echo escaparHTML($fila["fecha_alta"]); ?></div></td>
                         <td><div class="cell-content" title="<?php echo escaparHTML($fila["ubicacion"]); ?>"><?php echo escaparHTML($fila["ubicacion"]); ?></div></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
            
</body>
</html>
