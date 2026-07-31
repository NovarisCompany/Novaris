<?php
require_once __DIR__ . '/conexion.php';

exigirAcceso();

$mensaje = '';
$error = '';
$idUsuario = (int) $_SESSION['id_usuario'];
$avatares = [
    'Imagenes/usuariofemenino.png' => 'Usuario femenino',
    'Imagenes/usuariomasculino.png' => 'Usuario masculino',
    'Imagenes/usuariofemeninodescafro.png' => 'Usuario femenino descafrado',
    'Imagenes/usuariomasculinodescafro.png' => 'Usuario masculino descafrado',
    'Imagenes/usuariofemeninolentes.png' => 'Usuario femenino con lentes',
    'Imagenes/usuariomasculinolentes.png' => 'Usuario masculino con lentes',
    'Imagenes/usuariofemeninopelirroja.png' => 'Usuario femenino pelirroja',
    'Imagenes/usuariomasculinocalvo.png' => 'Usuario masculino calvo',
];
$avatarPredeterminado = array_key_first($avatares);

try {
    $conexion = conectarBD();
    $consulta = 'SELECT u.nombre, u.apellido, u.email, u.telefono, u.fotoperfil, r.nombre_rol
                 FROM usuario AS u
                 INNER JOIN roles AS r ON r.id_rol = u.id_rol
                 WHERE u.id_usuario = ?';
    $stmtUsuario = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmtUsuario, 'i', $idUsuario);
    mysqli_stmt_execute($stmtUsuario);
    $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUsuario));
    mysqli_stmt_close($stmtUsuario);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    http_response_code(500);
    exit('No se pudo cargar el perfil.');
}

if (!$usuario) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$nombre = $usuario['nombre'];
$apellido = $usuario['apellido'];
$telefono = $usuario['telefono'] ?? '';
$email = $usuario['email'];
$nombreRol = $usuario['nombre_rol'];
$fotoPerfil = isset($avatares[$usuario['fotoperfil'] ?? ''])
    ? $usuario['fotoperfil']
    : $avatarPredeterminado;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';
    $fotoPerfil = $_POST['foto_perfil'] ?? $avatarPredeterminado;

    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud expiró. Recarga la página e inténtalo nuevamente.';
    } elseif ($nombre === '' || $apellido === '' || $email === '') {
        $error = 'Completa nombre, apellidos y email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } elseif (!isset($avatares[$fotoPerfil])) {
        $error = 'El avatar seleccionado no es válido.';
    } elseif ($contrasena !== '' && strlen($contrasena) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($contrasena !== $confirmarContrasena) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            if ($contrasena !== '') {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $consulta = 'UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, fotoperfil = ?, contrasena = ? WHERE id_usuario = ?';
                $stmt = mysqli_prepare($conexion, $consulta);
                mysqli_stmt_bind_param($stmt, 'ssssssi', $nombre, $apellido, $email, $telefono, $fotoPerfil, $hash, $idUsuario);
            } else {
                $consulta = 'UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, fotoperfil = ? WHERE id_usuario = ?';
                $stmt = mysqli_prepare($conexion, $consulta);
                mysqli_stmt_bind_param($stmt, 'sssssi', $nombre, $apellido, $email, $telefono, $fotoPerfil, $idUsuario);
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['email'] = $email;
            $mensaje = 'Datos guardados correctamente.';
        } catch (mysqli_sql_exception $exception) {
            $error = $exception->getCode() === 1062
                ? 'Ya existe un usuario con ese email.'
                : 'No se pudieron guardar los datos. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - Novaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="perfil-page">
<div class="contenedor">
    <div class="perfil-izq">
        <div class="foto-perfil">
            <img src="<?php echo escaparHTML($fotoPerfil); ?>" alt="Avatar de perfil" class="icono-preview" id="icono-preview">
        </div>
        <div class="selector-iconos">
            <h3>Iconos predeterminados</h3>
            <p>Elige un icono para tu perfil</p>
            <div class="iconos-predeterminados">
                <?php foreach ($avatares as $rutaAvatar => $etiquetaAvatar): ?>
                    <button type="button" class="icono-opcion" data-image="<?php echo escaparHTML($rutaAvatar); ?>" aria-label="<?php echo escaparHTML($etiquetaAvatar); ?>">
                        <img src="<?php echo escaparHTML($rutaAvatar); ?>" alt="<?php echo escaparHTML($etiquetaAvatar); ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="perfil-der">
        <div class="titulo"><span>Perfil</span></div>
        <?php if ($error !== ''): ?>
            <div class="error"><?php echo escaparHTML($error); ?></div>
        <?php endif; ?>
        <?php if ($mensaje !== ''): ?>
            <div class="success"><?php echo escaparHTML($mensaje); ?></div>
        <?php endif; ?>
        <form id="form-perfil" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo escaparHTML(generarTokenCsrf()); ?>">
            <div class="fila">
                <div class="grupo">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre" value="<?php echo escaparHTML($nombre); ?>" required maxlength="50">
                </div>
                <div class="grupo">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" placeholder="Apellidos" value="<?php echo escaparHTML($apellido); ?>" required maxlength="50">
                </div>
            </div>
            <div class="fila">
                <div class="grupo">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" placeholder="Teléfono" value="<?php echo escaparHTML($telefono); ?>" maxlength="20">
                </div>
                <div class="grupo">
                    <label for="rol">Rol</label>
                    <input type="text" id="rol" value="<?php echo escaparHTML($nombreRol); ?>" readonly>
                </div>
            </div>
            <div class="fila">
                <div class="grupo">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="you@email.com" value="<?php echo escaparHTML($email); ?>" required maxlength="100">
                </div>
            </div>
            <div class="fila">
                <div class="grupo">
                    <label for="contrasena">Contraseña nueva</label>
                    <input type="password" name="contrasena" id="contrasena" placeholder="Dejar en blanco para conservar" minlength="8" autocomplete="new-password">
                </div>
                <div class="grupo">
                    <label for="confirmar_contrasena">Verifica la contraseña</label>
                    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="Verificar contraseña" autocomplete="new-password">
                </div>
            </div>
            <input type="hidden" name="foto_perfil" id="foto-perfil-input" value="<?php echo escaparHTML($fotoPerfil); ?>">
            <div class="botones">
                <button type="submit" class="btn-guardar">Guardar</button>
                <button type="button" class="btn-limpiar" id="btn-restaurar">Restaurar</button>
            </div>
        </form>
    </div>
</div>
<script>
    const formPerfil = document.getElementById('form-perfil');
    const preview = document.getElementById('icono-preview');
    const inputFotoPerfil = document.getElementById('foto-perfil-input');

    document.querySelectorAll('.icono-opcion').forEach((boton) => {
        boton.addEventListener('click', () => {
            preview.src = boton.dataset.image;
            inputFotoPerfil.value = boton.dataset.image;
        });
    });

    document.getElementById('btn-restaurar').addEventListener('click', () => {
        formPerfil.reset();
        preview.src = inputFotoPerfil.value;
    });
</script>
</body>
</html>
