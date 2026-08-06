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
        const navbar = document.querySelector('.navbar');
        if (navbar && !document.querySelector('[data-tema="dark"]')) {
            navbar.insertAdjacentHTML('afterbegin', '<div class="modo-light theme-mode-control" data-tema="dark"><button class="boton-light theme-button" type="button" onclick="cambiarTema(\'light\')" title="Cambiar a modo claro" aria-label="Cambiar a modo claro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3V4M12 20V21M4 12H3M6.3 6.3 5.5 5.5M17.7 6.3 18.5 6.3M6.3 17.7 5.5 18.5M17.7 17.7 18.5 18.5M21 12H20M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button></div><div class="modo-dark theme-mode-control" data-tema="light" hidden><button class="boton-dark theme-button" type="button" onclick="cambiarTema(\'dark\')" title="Cambiar a modo oscuro" aria-label="Cambiar a modo oscuro"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21.25 12a9.25 9.25 0 1 1-9.25-9.25c.1 0 .12.13.03.17a6.5 6.5 0 0 0 3.47 11.83c2.09 0 3.92-1.11 4.93-2.78.05-.08.17-.04.17-.03Z" fill="#1C274C"></path></svg></button></div>');
        }
        aplicarTema(temaInicial, false);
        setTimeout(function () {
            document.documentElement.classList.add('theme-ready');
        }, 1000);
    });
}());
