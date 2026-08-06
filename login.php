<?php
session_start();
require_once __DIR__ . "/conexion.php";

$error = "";

try {
    $conexion = conectarBD();
} catch (mysqli_sql_exception $e) {
    die("Error al conectar con la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Por favor complete todos los datos.";
    } else {
        try {
            $consulta = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.contrasena, u.id_rol, r.nombre_rol
                         FROM usuario AS u
                         INNER JOIN roles AS r ON u.id_rol = r.id_rol
                         WHERE u.email = ?";

            $stmt = mysqli_prepare($conexion, $consulta);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            if ($usuario && password_verify($password, $usuario["contrasena"])) {
                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nombre"] = $usuario["nombre"];
                $_SESSION["apellido"] = $usuario["apellido"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["id_rol"] = $usuario["id_rol"];
                $_SESSION["rol"] = $usuario["nombre_rol"];

                if ($usuario["id_rol"] == 1) {
                    header("Location: admin_paginas/index-admin.php");
                } elseif ($usuario["id_rol"] == 2) {
                    header("Location: tecnico_paginas/index-tecnico.php");
                } else {
                    header("Location: solicitantes_paginas/index-solicitante.php");
                }

                exit;
            }

            $error = "Email o contraseña incorrectos.";
        } catch (mysqli_sql_exception $e) {
            $error = "Error al iniciar sesión.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novaris - Iniciar sesión</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

<div class="container">
    <div class="left-panel">
        <div class="content">
            <h1 class="typing">Bienvenido!</h1>
            <p>Soporte técnico y gestión de recursos informáticos.</p>
            <div class="footer">© 2026 Novaris. Todos los derechos reservados.</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="login-box">
            <h1>Bienvenido de nuevo!</h1>
            <p>¿No tienes un usuario? <a href="registrarse.php">Crea un usuario ahora</a></p>

            <?php if ($error !== ""): ?>
                <div class="error"><?php echo escaparHTML($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" id="login-btn">Iniciar sesión</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
