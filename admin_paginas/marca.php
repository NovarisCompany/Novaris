<?php
session_start();
require_once __DIR__ . '/../conexion.php';
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

$conexion = conectarBD();

$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));



$mensaje = "";
$error = "";
$estadosMarca = ['Activa', 'Inactiva'];

function obtenerRegistro(mysqli $conexion, string $tabla, string $idCampo, int $id): ?array
{
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `$tabla` WHERE `$idCampo` = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $registro = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $registro ?: null;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'guardar_marca') {
            $idMarca = (int) ($_POST['id_marca'] ?? 0);
            $nombreMarca = trim($_POST['nombre_marca'] ?? '');
            $modeloMarca = trim($_POST['modelo'] ?? '');
            $tipoMarca = trim($_POST['tipo'] ?? '');
            $componentes = trim($_POST['componentes'] ?? '');
            $estadoMarca = $_POST['estado'] ?? 'Activa';

            if ($nombreMarca === '' || !in_array($estadoMarca, $estadosMarca, true)) {
                throw new InvalidArgumentException('El nombre de la marca y un estado válido son obligatorios.');
            }

            if ($idMarca > 0) {
                $stmt = mysqli_prepare($conexion, "UPDATE marca SET nombre_marca = ?, modelo = ?, tipo = ?, componentes = ?, estado = ? WHERE id_marca = ?");
                mysqli_stmt_bind_param($stmt, "sssssi", $nombreMarca, $modeloMarca, $tipoMarca, $componentes, $estadoMarca, $idMarca);
                $mensaje = 'Marca actualizada correctamente.';
            } else {
                $stmt = mysqli_prepare($conexion, "INSERT INTO marca (nombre_marca, modelo, tipo, componentes, estado) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssss", $nombreMarca, $modeloMarca, $tipoMarca, $componentes, $estadoMarca);
                $mensaje = 'Marca creada correctamente.';
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($accion === 'dar_baja_marca') {
            $idMarca = (int) ($_POST['id_marca'] ?? 0);
            if ($idMarca <= 0) {
                throw new InvalidArgumentException('Marca inválida.');
            }
            $stmt = mysqli_prepare($conexion, "UPDATE marca SET estado = 'Inactiva' WHERE id_marca = ?");
            mysqli_stmt_bind_param($stmt, "i", $idMarca);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $mensaje = 'Marca dada de baja correctamente.';
        }
    }
} catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
} catch (mysqli_sql_exception $e) {
    $codigoError = (int) $e->getCode();
    $error = match ($codigoError) {
        1062 => 'Ya existe un registro con ese valor único.',
        1451 => 'No se puede eliminar: el registro está siendo utilizado por otros datos.',
        1452 => 'La relación seleccionada no existe.',
        default => 'No se pudo completar la operación. Verifica los datos e intenta nuevamente.',
    };
}

$marcas = mysqli_fetch_all(mysqli_query($conexion, "SELECT id_marca, nombre_marca, modelo, tipo, componentes, estado FROM marca ORDER BY nombre_marca"), MYSQLI_ASSOC);
$marcaEditar = isset($_GET['editar_marca']) ? obtenerRegistro($conexion, 'marca', 'id_marca', (int) $_GET['editar_marca']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Novaris</title>
    <link id="tema-oscuro" rel="stylesheet" href="../style_dark.css?v=7" media="all">
    <link id="tema-claro" rel="stylesheet" href="../style_light.css?v=7" media="not all">
    <link rel="stylesheet" href="admin-theme.css?v=1">
</head>
<body class="app-page">

    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand"><span>Nov</span><span>aris</span></a>
            <div class="modo-light theme-mode-control" data-tema="dark">
                <button class="boton-light theme-button" type="button" onclick="cambiarTema('light')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.31412 6.31412L5.5 5.5M17.6859 6.31412 18.5 5.5M6.31412 17.69 5.5 18.5001M17.6859 17.69 18.5 18.5001M21 12H20M16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>
            <div class="modo-dark theme-mode-control" data-tema="light" hidden>
                <button class="boton-dark theme-button" type="button" onclick="cambiarTema('dark')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.9 2.31c-.16-.41-.74-.41-.9 0l-.43 1.1a.75.75 0 0 1-.27.27l-1.09.43c-.41.16-.22.15-.27.27l-.43 1.1c-.16.41-.74.41-.9 0l-.43-1.1a.75.75 0 0 0-.27-.27l-1.09-.43c-.41-.16-.41-.74 0-.9l1.09-.43a.75.75 0 0 0 .27-.27l.43-1.1Z" stroke="#1C274C"></path><path d="M21.25 12c0 5.11-4.14 9.25-9.25 9.25A9.25 9.25 0 1 1 12 2.75c.09 0 .1.12.03.16A6.5 6.5 0 0 0 15.5 14.75c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17.03Z" fill="#1C274C"></path></svg>
                </button>
            </div>
            <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
            
        </nav>
    </header>

 <div class="side-bar">
         <div class="home-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <a href="index-admin.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <a href="inventario.php" class="side-link">Inventario</a>
        </div>
        <div class="marca-link active">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M7.0498 7.0498H7.0598M10.5118 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V10.5118C3 11.2455 3 11.6124 3.08289 11.9577C3.15638 12.2638 3.27759 12.5564 3.44208 12.8249C3.6276 13.1276 3.88703 13.387 4.40589 13.9059L9.10589 18.6059C10.2939 19.7939 10.888 20.388 11.5729 20.6105C12.1755 20.8063 12.8245 20.8063 13.4271 20.6105C14.112 20.388 14.7061 19.7939 15.8941 18.6059L18.6059 15.8941C19.7939 14.7061 20.388 14.112 20.6105 13.4271C20.8063 12.8245 20.8063 12.1755 20.6105 11.5729C20.388 10.888 19.7939 10.2939 18.6059 9.10589L13.9059 4.40589C13.387 3.88703 13.1276 3.6276 12.8249 3.44208C12.5564 3.27759 12.2638 3.15638 11.9577 3.08289C11.6124 3 11.2455 3 10.5118 3ZM7.5498 7.0498C7.5498 7.32595 7.32595 7.5498 7.0498 7.5498C6.77366 7.5498 6.5498 7.32595 6.5498 7.0498C6.5498 6.77366 6.77366 6.5498 7.0498 6.5498C7.32595 6.5498 7.5498 6.77366 7.5498 7.0498Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
            <a href="marca.php" class="side-link">Marca</a>
        </div>
        <div class="mesa-ayuda-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg>
            <a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a>
        </div>
        <div class="reportes-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.3a3.5 3.5 0 0 1 0 6.4M17 14.2a4.5 4.5 0 0 1 3 4.3V20"/></svg>
            <a href="usuario.php" class="side-link">Usuarios</a>
        </div>
        <div class="tecnicos-link">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M4.87171 1.58811C4.18324 1.9184 4.34293 2.75714 4.79289 3.20711L9.08579 7.5L7.5 9.08579L3.20711 4.79289C2.75711 4.3429 1.90944 4.19971 1.58419 4.87995C0.985604 6.1319 0.769329 7.54124 0.975579 8.9287C1.23113 10.6478 2.11708 12.2104 3.46105 13.3124C4.80502 14.4144 6.51089 14.977 8.24676 14.8908C8.89048 14.8588 9.52124 14.7385 10.1228 14.537L17.8787 22.2929C19.0503 23.4645 20.9497 23.4645 22.1213 22.2929L22.2929 22.1213C23.4645 20.9497 23.4645 19.0503 22.2929 17.8787L14.537 10.1228C14.7395 9.51835 14.8601 8.8844 14.8913 8.2375C14.9753 6.50151 14.4104 4.79638 13.3066 3.45387C12.2028 2.11135 10.6391 1.22746 8.91964 0.974169C7.53192 0.769745 6.12286 0.987876 4.87171 1.58811ZM12.0945 10.5087C12.5356 9.7995 12.793 8.98448 12.834 8.13802C12.8932 6.91285 12.4946 5.70946 11.7156 4.76198C10.9366 3.8145 9.83297 3.19069 8.61946 3.01193C8.21216 2.95193 7.80225 2.94351 7.39883 2.98462L10.5 6.08579C11.2811 6.86684 11.281 8.13317 10.5 8.91421L8.91421 10.5C8.13317 11.281 6.86684 11.281 6.08579 10.5L2.98467 7.39888C2.94336 7.8044 2.95209 8.21647 3.01295 8.62584C3.1933 9.8391 3.81856 10.9419 4.76707 11.7196C5.71557 12.4974 6.91949 12.8944 8.14458 12.8336C8.98967 12.7916 9.80314 12.5338 10.511 12.093L11.9946 13.5766L11.9924 13.5782L19.2929 20.8787C19.6834 21.2692 20.3166 21.2692 20.7071 20.8787L20.8787 20.7071C21.2692 20.3166 21.2692 19.6834 20.8787 19.2929L12.0945 10.5087Z" fill="#0F0F0F"></path> </g></svg>
            <a href="tecnico_a.php" class="side-link">Técnicos</a>
        </div>
        <div class="areas-link">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15.9894 4.9502L16.52 4.42014L16.52 4.42014L15.9894 4.9502ZM8.73845 19.429L8.20785 19.9591L8.73845 19.429ZM4.62176 15.3081L5.15236 14.7781L4.62176 15.3081ZM17.567 14.9943L17.3032 14.2922L17.567 14.9943ZM15.6499 15.7146L15.9137 16.4167L15.6499 15.7146ZM8.33227 8.38177L7.62805 8.12375H7.62805L8.33227 8.38177ZM9.02673 6.48636L9.73095 6.74438L9.02673 6.48636ZM5.84512 10.6735L6.04445 11.3965H6.04445L5.84512 10.6735ZM7.30174 10.1351L6.86354 9.52646L6.86354 9.52646L7.30174 10.1351ZM7.6759 9.79038L8.24673 10.2768H8.24673L7.6759 9.79038ZM14.2511 16.3805L14.7421 16.9475L14.7421 16.9475L14.2511 16.3805ZM13.3807 18.2012L12.6575 18.0022V18.0022L13.3807 18.2012ZM13.917 16.7466L13.3076 16.3094L13.3076 16.3094L13.917 16.7466ZM2.71854 12.7552L1.96855 12.76V12.76L2.71854 12.7552ZM2.93053 11.9521L2.28061 11.5778H2.28061L2.93053 11.9521ZM11.3053 21.3431L11.3064 20.5931H11.3064L11.3053 21.3431ZM12.0933 21.1347L11.7216 20.4833L11.7216 20.4833L12.0933 21.1347ZM21.9652 12.3049L22.6983 12.4634L21.9652 12.3049ZM11.6973 2.03606L11.8589 2.76845L11.6973 2.03606ZM22.3552 10.6303C22.1511 10.2699 21.6934 10.1433 21.333 10.3475C20.9726 10.5516 20.846 11.0093 21.0502 11.3697L22.3552 10.6303ZM18.006 8.03006C18.2988 8.3231 18.7737 8.32334 19.0667 8.0306C19.3597 7.73786 19.36 7.26298 19.0672 6.96994L18.006 8.03006ZM9.26905 18.8989L5.15236 14.7781L4.09116 15.8382L8.20785 19.9591L9.26905 18.8989ZM17.3032 14.2922L15.3861 15.0125L15.9137 16.4167L17.8308 15.6964L17.3032 14.2922ZM9.03649 8.63979L9.73095 6.74438L8.32251 6.22834L7.62805 8.12375L9.03649 8.63979ZM6.04445 11.3965C6.75591 11.2003 7.29726 11.0625 7.73995 10.7438L6.86354 9.52646C6.6906 9.65097 6.46608 9.72428 5.64578 9.95044L6.04445 11.3965ZM7.62805 8.12375C7.3351 8.92332 7.24345 9.14153 7.10507 9.30391L8.24673 10.2768C8.60048 9.86175 8.78237 9.33337 9.03649 8.63979L7.62805 8.12375ZM7.73995 10.7438C7.92704 10.6091 8.09719 10.4523 8.24673 10.2768L7.10507 9.30391C7.03377 9.38757 6.95268 9.46229 6.86354 9.52646L7.73995 10.7438ZM15.3861 15.0125C14.697 15.2714 14.1717 15.4571 13.7601 15.8135L14.7421 16.9475C14.9029 16.8082 15.1193 16.7152 15.9137 16.4167L15.3861 15.0125ZM14.1038 18.4001C14.3291 17.5813 14.4022 17.3569 14.5263 17.1838L13.3076 16.3094C12.9903 16.7517 12.853 17.2919 12.6575 18.0022L14.1038 18.4001ZM13.7601 15.8135C13.5904 15.9605 13.4385 16.1269 13.3076 16.3094L14.5263 17.1838C14.5888 17.0968 14.6612 17.0175 14.7421 16.9475L13.7601 15.8135ZM5.15236 14.7781C4.50623 14.1313 4.06806 13.691 3.78374 13.3338C3.49842 12.9753 3.46896 12.8201 3.46852 12.7505L1.96855 12.76C1.97223 13.3422 2.26135 13.8297 2.6101 14.2679C2.95984 14.7073 3.47123 15.2176 4.09116 15.8382L5.15236 14.7781ZM5.64578 9.95044C4.80056 10.1835 4.10403 10.3743 3.58304 10.5835C3.06349 10.792 2.57124 11.0732 2.28061 11.5778L3.58045 12.3264C3.61507 12.2663 3.717 12.146 4.14187 11.9755C4.56531 11.8055 5.16345 11.6394 6.04445 11.3965L5.64578 9.95044ZM3.46852 12.7505C3.46758 12.6016 3.50623 12.4553 3.58045 12.3264L2.28061 11.5778C2.07362 11.9372 1.96593 12.3452 1.96855 12.76L3.46852 12.7505ZM8.20785 19.9591C8.83172 20.5836 9.34472 21.0987 9.78654 21.4506C10.2271 21.8015 10.718 22.0922 11.3042 22.0931L11.3064 20.5931C11.237 20.593 11.0815 20.5644 10.7211 20.2773C10.3619 19.9912 9.91931 19.5499 9.26905 18.8989L8.20785 19.9591ZM12.6575 18.0022C12.4133 18.8897 12.2463 19.4924 12.0752 19.9188C11.9034 20.3467 11.7822 20.4487 11.7216 20.4833L12.4651 21.7861C12.9741 21.4956 13.2573 21.0004 13.4672 20.4775C13.6777 19.9532 13.8695 19.2516 14.1038 18.4001L12.6575 18.0022ZM11.3042 22.0931C11.7113 22.0937 12.1115 21.9879 12.4651 21.7861L11.7216 20.4833C11.5951 20.5555 11.452 20.5933 11.3064 20.5931L11.3042 22.0931ZM17.8308 15.6964C19.1922 15.1849 20.2941 14.773 21.0771 14.3384C21.8719 13.8973 22.5084 13.3416 22.6983 12.4634L21.2322 12.1464C21.178 12.3968 21.0002 12.6655 20.3492 13.0268C19.6865 13.3946 18.7113 13.7632 17.3032 14.2922L17.8308 15.6964ZM16.52 4.42014C15.4841 3.3832 14.6481 2.54353 13.9246 2.00638C13.1909 1.46165 12.4175 1.10912 11.5357 1.30367L11.8589 2.76845C12.1086 2.71335 12.4278 2.7633 13.0305 3.21075C13.6434 3.66579 14.3877 4.40801 15.4588 5.48026L16.52 4.42014ZM9.73095 6.74438C10.2526 5.32075 10.6162 4.33403 10.9813 3.66315C11.3403 3.00338 11.6091 2.82357 11.8589 2.76845L11.5357 1.30367C10.6541 1.49819 10.1006 2.14332 9.6637 2.94618C9.23286 3.73793 8.82695 4.85154 8.32251 6.22834L9.73095 6.74438ZM21.0502 11.3697C21.2515 11.7251 21.2745 11.9507 21.2322 12.1464L22.6983 12.4634C22.8404 11.8064 22.6796 11.2027 22.3552 10.6303L21.0502 11.3697ZM15.4588 5.48026L18.006 8.03006L19.0672 6.96994L16.52 4.42014L15.4588 5.48026Z" fill="#1C274C"></path> <path d="M1.4694 21.4697C1.17666 21.7627 1.1769 22.2376 1.46994 22.5304C1.76298 22.8231 2.23786 22.8229 2.5306 22.5298L1.4694 21.4697ZM7.18383 17.8719C7.47657 17.5788 7.47633 17.1039 7.18329 16.8112C6.89024 16.5185 6.41537 16.5187 6.12263 16.8117L7.18383 17.8719ZM2.5306 22.5298L7.18383 17.8719L6.12263 16.8117L1.4694 21.4697L2.5306 22.5298Z" fill="#1C274C"></path> </g></svg>
            <a href="areas.php" class="side-link">Áreas</a>
        </div>
         <div class="tickets-link">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M6 4.5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z"/><path d="M8 9h8M8 13h8M8 17h5" stroke-linecap="round"/></svg>
            <a href="tickets.php" class="side-link">Mis tickets</a>
        </div>
    </div>
<div class="informacion">
    <span id="fechahoy"></span>
    <div id="titulo-informacion"><strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?></div>
</div>

<main class="inventario-info">
    <?php if ($mensaje !== ''): ?><div class="mensaje-exito"><?php echo escaparHTML($mensaje); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="mensaje-error"><?php echo escaparHTML($error); ?></div><?php endif; ?>

    <section class="inventario-acciones">
        <h1>Marca</h1>

        <section class="formularrio-inventario">
            <h2><?php echo $marcaEditar ? 'Modificar marca' : 'Alta de marca'; ?></h2>
            <form method="post">
                <input type="hidden" name="accion" value="guardar_marca">
                <input type="hidden" name="id_marca" value="<?php echo (int) ($marcaEditar['id_marca'] ?? 0); ?>">
                <div class="form-group"><label for="marca-nombre">Nombre</label><input id="marca-nombre" name="nombre_marca" type="text" required value="<?php echo escaparHTML($marcaEditar['nombre_marca'] ?? ''); ?>"></div>
                <div class="form-group"><label for="marca-modelo">Modelo</label><input id="marca-modelo" name="modelo" type="text" value="<?php echo escaparHTML($marcaEditar['modelo'] ?? ''); ?>"></div>
                <div class="form-group"><label for="marca-tipo">Tipo</label><input id="marca-tipo" name="tipo" type="text" value="<?php echo escaparHTML($marcaEditar['tipo'] ?? ''); ?>"></div>
                <div class="form-group"><label for="marca-componentes">Componentes</label><input id="marca-componentes" name="componentes" type="text" value="<?php echo escaparHTML($marcaEditar['componentes'] ?? ''); ?>"></div>
                <div class="form-group"><label for="marca-estado">Estado</label><select id="marca-estado" name="estado" required><?php foreach ($estadosMarca as $estado): ?><option value="<?php echo escaparHTML($estado); ?>"<?php echo ($marcaEditar['estado'] ?? 'Activa') === $estado ? ' selected' : ''; ?>><?php echo escaparHTML($estado); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="btn btn-primary"><?php echo $marcaEditar ? 'Guardar cambios' : 'Crear marca'; ?></button>
                <?php if ($marcaEditar): ?><a class="btn" href="marca.php">Cancelar</a><?php endif; ?>
            </form>
        </section>

        <section>
            <h2>Consulta de marcas</h2>
            <div class="usuario-table"><table><thead><tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Tipo</th><th>Componentes</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php foreach ($marcas as $marca): ?><tr><td><?php echo (int) $marca['id_marca']; ?></td><td><?php echo escaparHTML($marca['nombre_marca']); ?></td><td><?php echo escaparHTML($marca['modelo'] ?? ''); ?></td><td><?php echo escaparHTML($marca['tipo'] ?? ''); ?></td><td><?php echo escaparHTML($marca['componentes'] ?? ''); ?></td><td><?php echo escaparHTML($marca['estado']); ?></td><td><a class="btn" href="?editar_marca=<?php echo (int) $marca['id_marca']; ?>">Modificar</a><?php if ($marca['estado'] !== 'Inactiva'): ?><form method="post" style="display:inline"><input type="hidden" name="accion" value="dar_baja_marca"><input type="hidden" name="id_marca" value="<?php echo (int) $marca['id_marca']; ?>"><button type="submit" class="btn" onclick="return confirm('¿Dar de baja esta marca?');">Dar de baja</button></form><?php endif; ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </section>

       
    </section>
</main>
</body>

<script>
document.getElementById('fechahoy').textContent = new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
</script>

    <script>
        (function () {
            const temaOscuro = document.getElementById('tema-oscuro');
            const temaClaro = document.getElementById('tema-claro');
            const temaGuardado = localStorage.getItem('novaris-tema');
            const temaInicial = temaGuardado === 'light' ? 'light' : 'dark';

            function aplicarTema(tema, guardar = true) {
                const esClaro = tema === 'light';
                document.documentElement.dataset.theme = tema;
                temaOscuro.media = esClaro ? 'not all' : 'all';
                temaClaro.media = esClaro ? 'all' : 'not all';
                document.querySelector('[data-tema="dark"]').hidden = esClaro;
                document.querySelector('[data-tema="light"]').hidden = !esClaro;

                if (guardar) {
                    localStorage.setItem('novaris-tema', tema);
                }
            }

            window.cambiarTema = function (tema) {
                document.documentElement.classList.add('theme-ready');
                aplicarTema(tema);
            };

            document.addEventListener('DOMContentLoaded', function () {
                aplicarTema(temaInicial, false);
                setTimeout(function () {
                    document.documentElement.classList.add('theme-ready');
                }, 1000);
            });
        }());
    </script>
</body>
</html>
