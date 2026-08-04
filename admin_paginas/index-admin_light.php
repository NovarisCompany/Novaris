<?php
session_start();
require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
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
    <link rel="stylesheet" href="../style_light.css?v=7">

</head>
<body class="app-page">
    <header>
        <nav class="navbar">
            <a href="index-admin.php" class="brand"><span>Nov</span><span>aris</span></a>
        <div class="traductor">
            <button
                 type="button"
                 class="btn-idioma"
                 id="btn-idioma"
                 aria-label="Cambiar idioma"
                 aria-expanded="false"
                 aria-controls="menu-idiomas"
            >
               <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6.15407 7.30116C7.52877 5.59304 9.63674 4.5 12 4.5C12.365 4.5 12.7238 4.52607 13.0748 4.57644L13.7126 5.85192L11.2716 8.2929L8.6466 8.6679L7.36009 9.95441L6.15407 7.30116ZM5.2011 8.82954C4.75126 9.79256 4.5 10.8669 4.5 12C4.5 15.6945 7.17133 18.7651 10.6878 19.3856L11.0989 18.7195L8.8147 15.547L10.3741 13.5256L9.63268 13.1549L6.94027 13.6036L6.41366 11.4972L5.2011 8.82954ZM7.95559 11.4802L8.05962 11.8964L9.86722 11.5951L11.3726 12.3478L14.0824 11.9714L18.9544 14.8135C19.3063 13.9447 19.5 12.995 19.5 12C19.5 8.93729 17.6642 6.30336 15.033 5.13856L15.5377 6.1481L11.9787 9.70711L9.35371 10.0821L7.95559 11.4802ZM18.2539 16.1414C16.9774 18.0652 14.8369 19.366 12.3859 19.4902L12.9011 18.6555L10.6853 15.578L12.0853 13.7632L13.7748 13.5286L18.2539 16.1414ZM12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3Z" fill="#080341"></path> </g></svg>
            </button>

            <div class="menu-idiomas" id="menu-idiomas" hidden>
                 <button type="button" data-idioma="es">Español</button>
                 <button type="button" data-idioma="en">English</button>
                 <button type="button" data-idioma="pt">Português</button>
                 <button type="button" data-idioma="fr">Français</button>
                 <button type="button" data-idioma="it">Italiano</button>
                 <button type="button" data-idioma="de">Deutsch</button>
            </div>

            <div id="traductor-google" aria-hidden="true"></div>
        </div>
            <a href="index-admin.php"><img src="../Imagenes/logo.png" alt="Logo de Novaris" class="logo"></a>
            <a href="../perfil.php" class="nav-cta">Mi Perfil</a>
        </nav>
    </header>

    <div class="side-bar">
        <div class="home-link active">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <a href="index-admin.php" class="side-link">Inicio</a>
        </div>
        <div class="inventario-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <a href="inventario.php" class="side-link">Inventario</a>
        </div>
        <div class="mesa-ayuda-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H8l-4 2v-5.18A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-linecap="round"/></svg>
            <a href="mesa-ayuda.php" class="side-link">Mesa de ayuda</a>
        </div>
        <div class="reportes-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.3a3.5 3.5 0 0 1 0 6.4M17 14.2a4.5 4.5 0 0 1 3 4.3V20"/></svg>
            <a href="usuario.php" class="side-link">Usuarios</a>
        </div>
    </div>

    <div class="informacion">
        <span id="fechahoy"></span>
        <div id="titulo-informacion">
            <strong>Bienvenido,</strong> <?php echo escaparHTML($nombreCompleto); ?>
        </div>
    </div>

        
        <div class="info-resumen">
            <div class="tickets">
                <h3>Tickets en proceso:</h3>
                <p>6</p>
                <h4>Tickets pendientes:</h4>
                <p>3</p>
            </div>
            <div class="inventario">
                <h3>Usuarios activos:</h3>
                <p>20</p>
                <h4>Equipos registrados:</h4>
                <p>80</p>
            </div>
        </div>

    <div class="ultimos_tickets">
        <h3>Últimos tickets:</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Problema con la impresora</td>
                    <td>En proceso</td>
                    <td>2024-06-01</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>No puedo acceder al correo</td>
                    <td>Pendiente</td>
                    <td>2024-06-02</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Problema con el mouse</td>
                    <td>Resuelto</td>
                    <td>2024-06-03</td>
                </tr>
            </tbody>
        </table>
        </div>
    <div class="acciones_rapidas">
        <div class="acciones-botones">
            <a class="accion-botones">Registrar equipo </a>
            <a  class="accion-botones">Crear usuario</a>
            <a class="accion-botones">Asignar tecnico</a>
            <a class="accion-botones">Generar Reporte</a>
        </div>
    </div>

    <script type="module" src="../anime.js"></script>

    <script>
        const fechaFormateada = new Date().toLocaleDateString("es-ES", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        });

        document.getElementById("fechahoy").textContent = fechaFormateada;
</script>
<script>
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'es',
        includedLanguages: 'es,en,pt,fr,it,de',
        autoDisplay: false
    }, 'traductor-google');
}

const traductor = document.querySelector('.traductor');
const botonIdioma = document.getElementById('btn-idioma');
const menuIdiomas = document.getElementById('menu-idiomas');

botonIdioma.addEventListener('click', () => {
    const abrirMenu = menuIdiomas.hidden;

    menuIdiomas.hidden = !abrirMenu;
    botonIdioma.setAttribute('aria-expanded', String(abrirMenu));
});

document.querySelectorAll('[data-idioma]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const idioma = boton.dataset.idioma;
        const selectorGoogle = document.querySelector('.goog-te-combo');

        if (!selectorGoogle) {
            console.warn('Google Translate todavía no está listo.');
            return;
        }

        selectorGoogle.value = idioma === 'es' ? '' : idioma;
        selectorGoogle.dispatchEvent(new Event('change'));

        menuIdiomas.hidden = true;
        botonIdioma.setAttribute('aria-expanded', 'false');
    });
});

document.addEventListener('click', (evento) => {
    if (!traductor.contains(evento.target)) {
        menuIdiomas.hidden = true;
        botonIdioma.setAttribute('aria-expanded', 'false');
    }
});
</script>

<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</html>
