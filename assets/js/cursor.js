/**
 * Custom Eased Trailing Cursor
 * Ilham Ramadhan Setiawan Portfolio
 */

(function () {
  'use strict';

  // Disable on touch / mobile devices
  if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
    return;
  }

  const cursor = document.getElementById('custom-cursor');
  const cursorText = document.getElementById('cursor-text');
  if (!cursor || !cursorText) return;

  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let cursorX = mouseX;
  let cursorY = mouseY;

  // Track mouse position
  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
  });

  // Smooth linear interpolation loop
  function render() {
    cursorX += (mouseX - cursorX) * 0.15;
    cursorY += (mouseY - cursorY) * 0.15;

    cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%)`;
    requestAnimationFrame(render);
  }
  requestAnimationFrame(render);

  // Attach hover listeners to elements with data-cursor attributes
  function initHoverStates() {
    const hoverElements = document.querySelectorAll('[data-cursor]');

    hoverElements.forEach((el) => {
      el.addEventListener('mouseenter', () => {
        const text = el.getAttribute('data-cursor') || 'LIHAT';
        cursorText.textContent = text;
        cursor.classList.add('active-hover');
      });

      el.addEventListener('mouseleave', () => {
        cursorText.textContent = '';
        cursor.classList.remove('active-hover');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initHoverStates);
  window.initCustomCursorHover = initHoverStates;
})();
