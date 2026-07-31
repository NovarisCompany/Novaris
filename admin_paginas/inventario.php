<?php
require_once __DIR__ . '/../conexion.php';

exigirAcceso(1);

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$equipos = [];
$error = '';

try {
    $conexion = conectarBD();
    $resultado = mysqli_query(
        $conexion,
        'SELECT e.id_equipo, e.nombre, e.tipo, e.marca, e.modelo, e.numero_serie, e.estado_equipo, e.fecha_alta,
                a.nombre_area, a.ubicacion
         FROM equipo AS e
         INNER JOIN area AS a ON a.id_area = e.id_area
         ORDER BY e.id_equipo DESC'
    );
    $equipos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $error = 'No se pudo cargar el inventario.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=6">
</head>
<body class="app-page">
<header><nav class="navbar">
    <a href="index-admin.php" class="brand">Novaris</a>
    <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
    <a href="../perfil.php" class="nav-cta">Mi perfil</a>
</nav></header>
<div class="side-bar">
    <div class="home-link"><a href="index-admin.php" class="side-link">Inicio</a></div>
    <div class="inventario-link"><a href="inventario.php" class="side-link">Inventario</a></div>
    <div class="mesa-ayuda-link"><a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a></div>
    <div class="usuarios-link"><a href="usuario.php" class="side-link">Usuarios</a></div>
</div>
<div class="informacion">
    <span id="fechahoy"></span>
    <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
</div>
<main class="inventario-info">
    <h1>Inventario</h1>
    <?php if ($error !== ''): ?>
        <div class="error"><?php echo escaparHTML($error); ?></div>
    <?php elseif ($equipos === []): ?>
        <p>No hay equipos registrados.</p>
    <?php else: ?>
        <div class="inventario-table"><table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Marca</th><th>Modelo</th><th>Número de serie</th><th>Estado</th><th>Fecha de alta</th><th>Ubicación</th></tr></thead>
            <tbody>
            <?php foreach ($equipos as $equipo): ?>
                <tr>
                    <td><div class="cell-content"><?php echo (int) $equipo['id_equipo']; ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['nombre']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['tipo']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['marca']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['modelo']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['numero_serie']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['estado_equipo']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['fecha_alta']); ?></div></td>
                    <td><div class="cell-content"><?php echo escaparHTML($equipo['nombre_area'] . ' - ' . $equipo['ubicacion']); ?></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</main>
<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>
</body>
</html>
