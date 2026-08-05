(function () {
    const temaOscuro = document.getElementById('tema-oscuro');
    const temaClaro = document.getElementById('tema-claro');
    const temaGuardado = localStorage.getItem('novaris-tema');
    const temaInicial = temaGuardado === 'light' ? 'light' : 'dark';

    function aplicarTema(tema, guardar) {
        const esClaro = tema === 'light';
        document.documentElement.dataset.theme = tema;
        temaOscuro.media = esClaro ? 'not all' : 'all';
        temaClaro.media = esClaro ? 'all' : 'not all';

        const controlOscuro = document.querySelector('[data-tema="dark"]');
        const controlClaro = document.querySelector('[data-tema="light"]');
        if (controlOscuro && controlClaro) {
            controlOscuro.hidden = esClaro;
            controlClaro.hidden = !esClaro;
        }

        if (guardar) {
            localStorage.setItem('novaris-tema', tema);
        }
    }

    window.cambiarTema = function (tema) {
        document.documentElement.classList.add('theme-ready');
        aplicarTema(tema, true);
    };

    aplicarTema(temaInicial, false);

    window.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.documentElement.classList.add('theme-ready');
        }, 1000);
    });
}());
