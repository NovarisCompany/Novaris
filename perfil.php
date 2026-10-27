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

$paginaInicio = match ((int) ($_SESSION['id_rol'] ?? 0)) {
    1 => 'admin_paginas/index-admin.php',
    2 => 'tecnico_paginas/index-tecnico.php',
    3 => 'solicitantes_paginas/index-solicitante.php',
    default => 'login.php',
};

$nombre = "";
$apellido = "";
$telefono = "";
$email = "";
$fotoPerfil = "Imagenes/usuariofemenino.png";
$fotosPredeterminadas = [
    "Imagenes/usuariofemenino.png",
    "Imagenes/usuariomasculino.png",
    "Imagenes/usuariofemeninodescafro.png",
    "Imagenes/usuariomasculinodescafro.png",
    "Imagenes/usuariofemeninolentes.png",
    "Imagenes/usuariomasculinolentes.png",
    "Imagenes/usuariofemeninopelirroja.png",
    "Imagenes/usuariomasculinocalvo.png",
];

$consultaUsuario = "SELECT id_usuario, nombre, apellido, email, telefono, fotoperfil
                    FROM usuario
                    WHERE id_usuario = ?";
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
    $fotoPerfil = $usuarioActual["fotoperfil"] ?? "";
    if ($fotoPerfil === "" || !in_array($fotoPerfil, $fotosPredeterminadas, true)) {
        $fotoPerfil = $fotosPredeterminadas[0];
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellidos"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $confirmarContrasena = $_POST["confirmar_contrasena"] ?? "";
    $fotoPerfil = trim($_POST["foto_perfil"] ?? $fotosPredeterminadas[0]);

    if (!in_array($fotoPerfil, $fotosPredeterminadas, true)) {
        $fotoPerfil = $fotosPredeterminadas[0];
    }

    if ($nombre === "" || $apellido === "" || $email === "") {
        $error = "Completa nombre, apellidos y email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no tiene un formato válido.";
    } elseif ($contrasena !== "" && $contrasena !== $confirmarContrasena) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            mysqli_begin_transaction($conexion);

            if ($contrasena !== "") {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conexion, "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, contrasena = ?, fotoperfil = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmt, "ssssssi", $nombre, $apellido, $email, $telefono, $hash, $fotoPerfil, $idUsuario);
            } else {
                $stmt = mysqli_prepare($conexion, "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, fotoperfil = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmt, "sssssi", $nombre, $apellido, $email, $telefono, $fotoPerfil, $idUsuario);
            }

            mysqli_stmt_execute($stmt);
            mysqli_commit($conexion);

            $_SESSION["nombre"] = $nombre;
            $_SESSION["apellido"] = $apellido;
            $_SESSION["email"] = $email;
            $_SESSION["foto_perfil"] = $fotoPerfil;

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
    <title>Perfil de usuario - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylep.css">
</head>
<body class="perfil-page">
<a href="<?php echo escaparHTML($paginaInicio); ?>" class="btn-volver"><svg fill="#000000" height="200px" width="200px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <polygon points="513,216.6 158.5,216.6 316.1,59.1 197.9,59.1 1,256 197.9,452.9 316.1,452.9 158.5,295.4 513,295.4 "></polygon> </g></svg> Volver al inicio</a>

<div class="contenedor">

    <div class="perfil-izq">

        <div class="foto-perfil" id="foto-perfil">
            <img src="<?php echo escaparHTML($fotoPerfil); ?>" alt="Foto de perfil" class="icono-preview" id="icono-preview">
        </div>

        <div class="selector-iconos">
            <h3>Iconos predeterminados</h3>
            <p>Elige un icono para tu perfil</p>
            <div class="iconos-predeterminados">
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariofemenino.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariofemenino.png" aria-label="Usuario femenino">
                    <img src="Imagenes/usuariofemenino.png" alt="Usuario femenino">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariomasculino.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariomasculino.png" aria-label="Usuario masculino">
                    <img src="Imagenes/usuariomasculino.png" alt="Usuario masculino">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariofemeninodescafro.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariofemeninodescafro.png" aria-label="Usuario femenino descafrado">
                    <img src="Imagenes/usuariofemeninodescafro.png" alt="Usuario femenino descafrado">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariomasculinodescafro.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariomasculinodescafro.png" aria-label="Usuario masculino descafrado">
                    <img src="Imagenes/usuariomasculinodescafro.png" alt="Usuario masculino descafrado">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariofemeninolentes.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariofemeninolentes.png" aria-label="Usuario femenino con lentes">
                    <img src="Imagenes/usuariofemeninolentes.png" alt="Usuario femenino con lentes">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariomasculinolentes.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariomasculinolentes.png" aria-label="Usuario masculino con lentes">
                    <img src="Imagenes/usuariomasculinolentes.png" alt="Usuario masculino con lentes">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariofemeninopelirroja.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariofemeninopelirroja.png" aria-label="Usuario femenino pelirroja">
                    <img src="Imagenes/usuariofemeninopelirroja.png" alt="Usuario femenino pelirroja">
                </button>
                <button type="button" class="icono-opcion<?php echo $fotoPerfil === 'Imagenes/usuariomasculinocalvo.png' ? ' seleccionado' : ''; ?>" data-image="Imagenes/usuariomasculinocalvo.png" aria-label="Usuario masculino calvo">
                    <img src="Imagenes/usuariomasculinocalvo.png" alt="Usuario masculino calvo">
                </button>
            </div>
        </div>

    </div>

    <div class="perfil-der">

        <div class="titulo">
            <span>Perfil</span>
        </div>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-exito"><?php echo escaparHTML($mensaje); ?></div>
        <?php endif; ?>
        <?php if ($error !== ""): ?>
            <div class="mensaje-error"><?php echo escaparHTML($error); ?></div>
        <?php endif; ?>

        <form id="form-perfil" method="post" action="perfil.php">

            <div class="fila">
                <div class="grupo">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre" value="<?php echo escaparHTML($nombre); ?>" required>
                </div>

                <div class="grupo">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" placeholder="Apellidos" value="<?php echo escaparHTML($apellido); ?>" required>
                </div>
            </div>

            <div class="fila">
                <div class="grupo">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" placeholder="Teléfono" value="<?php echo escaparHTML($telefono); ?>">
                </div>
                <div class="grupo">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="you@email.com" value="<?php echo escaparHTML($email); ?>" required>
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

            <input type="hidden" name="foto_perfil" id="foto-perfil-input" value="<?php echo escaparHTML($fotoPerfil); ?>">

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
    const datosPerfil = <?php echo json_encode([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'telefono' => $telefono,
        'email' => $email,
        'foto' => $fotoPerfil,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    document.getElementById('nombre').value = datosPerfil.nombre;
    document.getElementById('apellidos').value = datosPerfil.apellido;
    document.getElementById('telefono').value = datosPerfil.telefono;
    document.getElementById('email').value = datosPerfil.email;
    preview.src = datosPerfil.foto;
    inputFotoPerfil.value = datosPerfil.foto;

    opciones.forEach(btn => {
        btn.addEventListener('click', () => {
            const rutaImagen = btn.getAttribute('data-image');
            preview.src = rutaImagen;
            inputFotoPerfil.value = rutaImagen;
            opciones.forEach(opcion => opcion.classList.remove('seleccionado'));
            btn.classList.add('seleccionado');
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
    });
</script>

</body>
</html>
