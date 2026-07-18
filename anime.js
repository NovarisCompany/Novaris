import { animate } from './node_modules/animejs/dist/modules/index.js';

document.addEventListener('DOMContentLoaded', () => {
  animate('.square', {
    y: { from: 0, to: '400px' },
    duration: 5000,
    ease: 'linear',
    loop: true,
  });
});

(function initScrollAnimations() {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );
    let cardIndex = 0;
    document.querySelectorAll('.fade-up, .producto-card, .producto-card-reverse').forEach(el => {
        if (el.classList.contains('producto-card') || el.classList.contains('producto-card-reverse')) {
            el.style.transitionDelay = (cardIndex * 0.07) + 's';
            cardIndex++;
        }
        observer.observe(el);
    });
})();

(function initCarousel() {
    const slides = ['c-1', 'c-2', 'c-3'];
    let current = 0;
    let timer;
    function advance() {
        current = (current + 1) % slides.length;
        const input = document.getElementById(slides[current]);
        if (input) input.checked = true;
    }
    function resetTimer() {
        clearInterval(timer);
        timer = setInterval(advance, 3000);
    }
    resetTimer();
    document.querySelectorAll('.dot, .nav-arrow').forEach(el => {
        el.addEventListener('click', resetTimer);
    });
})();

const boton = document.querySelector('.btn-main');
if (boton) {
    boton.addEventListener('click', function () {
        boton.classList.add('btn-animado');
        setTimeout(() => boton.classList.remove('btn-animado'), 400);
    });
}

document.addEventListener('DOMContentLoaded', () => {

    const toggle   = document.getElementById('chat-toggle');
    const widget   = document.getElementById('chat-widget');
    const closeBtn = document.getElementById('chat-close');
    const messages = document.getElementById('chat-messages');
    const input    = document.getElementById('chat-input');
    const sendBtn  = document.getElementById('chat-send');

    if (!toggle || !widget || !messages || !input || !closeBtn || !sendBtn) return;

    let chatHistory = [];

    toggle.addEventListener('click', () => {
        widget.classList.add('open');
        input.focus();
    });

    closeBtn.addEventListener('click', () => widget.classList.remove('open'));

    document.querySelectorAll('.suggestion').forEach(btn => {
        btn.addEventListener('click', () => {
            sendMessage(btn.dataset.text);
            const sugg = document.getElementById('chat-suggestions');
            if (sugg) sugg.style.display = 'none';
        });
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) sendMessage();
    });
    sendBtn.addEventListener('click', () => sendMessage());

    async function sendMessage(text) {
        const msg = (text || input.value).trim();
        if (!msg) return;

        input.value = '';
        addBubble(msg, 'user');
        chatHistory.push({ role: 'user', content: msg });

        const typingEl = addTyping();

        try {
            const response = await fetch('/proyecto-final/api/chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ history: chatHistory })
            });

            const data = await response.json();
            typingEl.remove();

            if (data.error) throw new Error(data.error);

            addBubble(data.reply, 'bot');
            chatHistory.push({ role: 'assistant', content: data.reply });

            if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);

        } catch (err) {
            typingEl.remove();
            addBubble('Uy, hubo un problema al conectar. Intentá de nuevo en un momento.', 'bot');
            console.error('Error chatbot:', err);
        }
    }

    function addBubble(text, role) {
        const div = document.createElement('div');
        div.className = 'msg ' + role;
        div.textContent = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function addTyping() {
        const div = document.createElement('div');
        div.className = 'msg typing-indicator';
        div.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }
});