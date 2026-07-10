<?php
session_start();
require_once __DIR__ . "/conexion.php";

$mensaje = "";
$error = "";

try {
    $conexion = conectarBD();
} catch (mysqli_sql_exception $e) {
    die("Error de conexión con la base de datos.");
}

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$idUsuario = (int) ($_SESSION["id_usuario"] ?? 0);

$nombre = "";
$apellido = "";
$telefono = "";
$email = "";
$idRol = 0;
$fotoPerfil = "Imagenes/usuariofemenino.png";
$roles = [];

$consultaUsuario = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.telefono, u.id_rol, r.nombre_rol
                    FROM usuario AS u
                    LEFT JOIN roles AS r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = ?";
$stmtUsuario = mysqli_prepare($conexion, $consultaUsuario);
mysqli_stmt_bind_param($stmtUsuario, "i", $idUsuario);
mysqli_stmt_execute($stmtUsuario);
$resultadoUsuario = mysqli_stmt_get_result($stmtUsuario);
$usuarioActual = mysqli_fetch_assoc($resultadoUsuario);

if ($usuarioActual) {
    $nombre = $usuarioActual["nombre"] ?? "";
    $apellido = $usuarioActual["apellido"] ?? "";
    $telefono = $usuarioActual["telefono"] ?? "";
    $email = $usuarioActual["email"] ?? "";
    $idRol = (int) ($usuarioActual["id_rol"] ?? 0);
}

$consultaRoles = "SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol";
$resultadoRoles = mysqli_query($conexion, $consultaRoles);
while ($rol = mysqli_fetch_assoc($resultadoRoles)) {
    $roles[] = $rol;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellidos"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $confirmarContrasena = $_POST["confirmar_contrasena"] ?? "";
    $idRol = (int) ($_POST["id_rol"] ?? 0);
    $fotoPerfil = trim($_POST["foto_perfil"] ?? "Imagenes/usuariofemenino.png");

    if ($nombre === "" || $apellido === "" || $email === "") {
        $error = "Completa nombre, apellidos y email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no tiene un formato válido.";
    } elseif ($contrasena !== "" && $contrasena !== $confirmarContrasena) {
        $error = "Las contraseñas no coinciden.";
    } elseif ($idRol <= 0) {
        $error = "Selecciona un rol válido.";
    } else {
        try {
            mysqli_begin_transaction($conexion);

            if ($contrasena !== "") {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conexion, "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, id_rol = ?, contrasena = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmt, "ssssssi", $nombre, $apellido, $email, $telefono, $idRol, $hash, $idUsuario);
            } else {
                $stmt = mysqli_prepare($conexion, "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, id_rol = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmt, "sssssi", $nombre, $apellido, $email, $telefono, $idRol, $idUsuario);
            }

            mysqli_stmt_execute($stmt);
            mysqli_commit($conexion);

            $_SESSION["nombre"] = $nombre;
            $_SESSION["apellido"] = $apellido;
            $_SESSION["email"] = $email;
            $_SESSION["id_rol"] = $idRol;

            $mensaje = "Datos guardados correctamente.";
        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conexion);
            $error = "No se pudieron guardar los datos. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="perfil-page">

<div class="contenedor">

    <div class="perfil-izq">

        <div class="foto-perfil" id="foto-perfil">
            <img src="<?= escaparHTML($fotoPerfil) ?>" alt="Icono de perfil" class="icono-preview" id="icono-preview">
        </div>

        <div class="selector-iconos">
            <h3>Iconos predeterminados</h3>
            <p>Elige un icono para tu perfil</p>
            <div class="iconos-predeterminados">
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariofemenino.png" aria-label="Usuario femenino">
                    <img src="Imagenes/usuariofemenino.png" alt="Usuario femenino">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariomasculino.png" aria-label="Usuario masculino">
                    <img src="Imagenes/usuariomasculino.png" alt="Usuario masculino">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariofemeninodescafro.png" aria-label="Usuario femenino descafrado">
                    <img src="Imagenes/usuariofemeninodescafro.png" alt="Usuario femenino descafrado">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariomasculinodescafro.png" aria-label="Usuario masculino descafrado">
                    <img src="Imagenes/usuariomasculinodescafro.png" alt="Usuario masculino descafrado">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariofemeninolentes.png" aria-label="Usuario femenino con lentes">
                    <img src="Imagenes/usuariofemeninolentes.png" alt="Usuario femenino con lentes">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariomasculinolentes.png" aria-label="Usuario masculino con lentes">
                    <img src="Imagenes/usuariomasculinolentes.png" alt="Usuario masculino con lentes">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariofemeninopelirroja.png" aria-label="Usuario femenino pelirroja">
                    <img src="Imagenes/usuariofemeninopelirroja.png" alt="Usuario femenino pelirroja">
                </button>
                <button type="button" class="icono-opcion" data-image="Imagenes/usuariomasculinocalvo.png" aria-label="Usuario masculino calvo">
                    <img src="Imagenes/usuariomasculinocalvo.png" alt="Usuario masculino calvo">
                </button>
            </div>
        </div>

    </div>

    <div class="perfil-der">

        <div class="titulo">
            <span>Perfil</span>
        </div>

        <form method="post" action="perfil.php" id="form-perfil">
            <?php if ($mensaje !== ""): ?>
                <div class="mensaje-exito"><?= escaparHTML($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="mensaje-error"><?= escaparHTML($error) ?></div>
            <?php endif; ?>

            <div class="fila">
                <div class="grupo">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre" value="<?= escaparHTML($nombre) ?>">
                </div>

                <div class="grupo">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" placeholder="Apellidos" value="<?= escaparHTML($apellido) ?>">
                </div>
            </div>

            <div class="fila">
                <div class="grupo">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="telefono" placeholder="Teléfono" value="<?= escaparHTML($telefono) ?>">
                </div>

                <div class="grupo">
                    <label>Rol</label>
                    <select name="id_rol" id="id_rol">
                        <option value="">Seleccione un rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int) $rol["id_rol"] ?>" <?= ((int) $idRol === (int) $rol["id_rol"]) ? "selected" : "" ?>><?= escaparHTML($rol["nombre_rol"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div class="grupo">
                    <label>Email</label>
                    <input type="email" name="email" id="email" placeholder="you@email.com" value="<?= escaparHTML($email) ?>">
                </div>
            </div>

            <div class="fila">
                <div class="grupo">
                    <label>Contraseña</label>
                    <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña">
                </div>

                <div class="grupo">
                    <label>Verifica la contraseña</label>
                    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="Verificar contraseña">
                </div>
            </div>

            <input type="hidden" name="foto_perfil" id="foto-perfil-input" value="<?= escaparHTML($fotoPerfil) ?>">

            <div class="botones">
                <button type="submit" class="btn-guardar">
                    Guardar
                </button>

                <button type="button" class="btn-limpiar" id="btn-limpiar">
                    Limpiar
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    const formPerfil = document.getElementById('form-perfil');
    const preview = document.getElementById('icono-preview');
    const opciones = document.querySelectorAll('.icono-opcion');
    const inputFotoPerfil = document.getElementById('foto-perfil-input');
    const botonLimpiar = document.getElementById('btn-limpiar');

    opciones.forEach(btn => {
        btn.addEventListener('click', () => {
            const rutaImagen = btn.getAttribute('data-image');
            preview.src = rutaImagen;
            inputFotoPerfil.value = rutaImagen;
        });
    });

    botonLimpiar.addEventListener('click', () => {
        formPerfil.reset();
        preview.src = 'Imagenes/usuariofemenino.png';
        inputFotoPerfil.value = 'Imagenes/usuariofemenino.png';

        document.getElementById('nombre').value = '';
        document.getElementById('apellidos').value = '';
        document.getElementById('telefono').value = '';
        document.getElementById('email').value = '';
        document.getElementById('contrasena').value = '';
        document.getElementById('confirmar_contrasena').value = '';
        document.getElementById('id_rol').selectedIndex = 0;
    });
</script>

</body>
</html>