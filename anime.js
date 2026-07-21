import { animate } from './node_modules/animejs/dist/modules/index.js';

document.addEventListener('DOMContentLoaded', () => {
  animate('.square', {
    y: { from: 0, to: '400px' },
    duration: 5000,
    ease: 'linear',
    loop: true,
  });
});

