<?php
// Carga el archivo de conexión y funciones auxiliares.
require_once __DIR__ . "/conexion.php";

// Variables para mostrar mensajes en el formulario.
$mensaje = "";
$error = "";
// Roles válidos para el registro.
$rolesPermitidos = [1, 2, 3];

// Intento de conexión a la base de datos.
try {
    $conexion = conectarBD();
} catch (mysqli_sql_exception $e) {
    die("Error de conexión con la base de datos.");
}

// Verifica si el formulario fue enviado vía POST.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtiene y limpia los datos enviados.
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contrasenaPlano = $_POST["contrasena"] ?? "";
    $telefono = trim($_POST["telefono"] ?? "");
    $idRol = (int) ($_POST["id_rol"] ?? 0);

    // Validación básica de los campos obligatorios.
    if ($nombre === "" || $apellido === "" || $email === "" || $contrasenaPlano === "" || $idRol === 0) {
        $error = "Completa todos los campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Verifica el formato del email.
        $error = "El email no tiene un formato válido.";
    } elseif (!in_array($idRol, $rolesPermitidos, true)) {
        // Verifica que el rol elegido sea uno permitido.
        $error = "El rol seleccionado no es válido.";
    } else {
        try {
            // Inicia la transacción para guardar datos relacionados.
            mysqli_begin_transaction($conexion);

            // Hashea la contraseña antes de guardarla.
            $contrasena = password_hash($contrasenaPlano, PASSWORD_DEFAULT);

            // Inserta el nuevo usuario en la tabla usuario.
            $consulta = "INSERT INTO usuario (nombre, apellido, email, contrasena, telefono, id_rol, estado_cuenta)
                         VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')";
            $stmt = mysqli_prepare($conexion, $consulta);
            mysqli_stmt_bind_param($stmt, "sssssi", $nombre, $apellido, $email, $contrasena, $telefono, $idRol);
            mysqli_stmt_execute($stmt);

            // Obtiene el ID generado del usuario insertado.
            $idUsuario = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            // Si el rol es Técnico (2), guarda también el registro en la tabla tecnico.
            if ($idRol === 2) {
                $especialidad = "Soporte informático";
                $stmtTecnico = mysqli_prepare($conexion, "INSERT INTO tecnico (id_usuario, especialidad) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmtTecnico, "is", $idUsuario, $especialidad);
                mysqli_stmt_execute($stmtTecnico);
                mysqli_stmt_close($stmtTecnico);
            }

            // Confirma la transacción cuando todo sale bien.
            mysqli_commit($conexion);
            $mensaje = "Usuario creado correctamente. Ya puedes iniciar sesión.";
        } catch (mysqli_sql_exception $e) {
            // Deshace los cambios si hay error en cualquiera de las consultas.
            mysqli_rollback($conexion);
            $error = $e->getCode() === 1062
                ? "Ya existe un usuario con ese email."
                : "Error al crear el usuario. Intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario - Novaris</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

<div class="container">
    <div class="left-panel">
        <div class="content">
            <h1>Crear usuario</h1>
            <p>Sistema de gestión de recursos tecnológicos, soporte informático e incidencias del área de TI.</p>
            <div class="footer">© 2026 Novaris. Todos los derechos reservados.</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="login-box">
            <h1>Complete los datos.</h1>

            <?php if ($mensaje !== ""): ?>
                <div class="success">
                    <?php echo escaparHTML($mensaje); ?>
                    <br>
                    <a href="login.php">Iniciar sesión</a>
                </div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="error"><?php echo escaparHTML($error); ?></div>
            <?php endif; ?>

            <form method="post" action="registrarse.php">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellido" placeholder="Apellido" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <select name="id_rol" id="boton-rol" required>
                    <option value="">Seleccione un rol</option>
                    <option value="1">Administrador</option>
                    <option value="2">Técnico</option>
                    <option value="3">Solicitante</option>
                </select>
                <button type="submit" id="registrar-btn">Crear usuario</button>
            </form>

            <div class="forgot"><a href="login.php">Ya tengo usuario</a></div>
        </div>
    </div>
</div>

</body>
</html>
