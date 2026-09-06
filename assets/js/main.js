/**
 * Main Application Script
 * Ilham Ramadhan Setiawan Portfolio
 */

(function () {
  'use strict';

  // Preloader Dismiss
  function initPreloader() {
    const preloader = document.getElementById('preloader');
    const progressBar = document.getElementById('preloader-progress');
    
    if (!preloader) return;

    let progress = 0;
    const interval = setInterval(() => {
      progress += Math.floor(Math.random() * 25) + 10;
      if (progressBar) progressBar.style.width = Math.min(progress, 100) + '%';

      if (progress >= 100) {
        clearInterval(interval);
        setTimeout(() => {
          preloader.classList.add('loaded');
        }, 300);
      }
    }, 60);
  }

  // Mobile Overlay Menu Toggle
  function initMobileMenu() {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const overlay = document.getElementById('mobile-overlay');
    
    if (!toggleBtn || !overlay) return;

    toggleBtn.addEventListener('click', () => {
      const isActive = overlay.classList.contains('active');
      if (isActive) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      } else {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });

    const mobileLinks = overlay.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      });
    });
  }

  // Client-side Work Category Filtering
  function initCategoryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.work-grid .project-card');

    if (filterBtns.length === 0 || projectCards.length === 0) return;

    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const category = (btn.getAttribute('data-filter') || 'ALL').toUpperCase();

        projectCards.forEach(card => {
          const cardCat = (card.getAttribute('data-category') || '').toUpperCase();
          if (category === 'ALL' || category === 'SEMUA' || cardCat === category || cardCat.includes(category)) {
            card.style.display = 'block';
            card.style.opacity = '1';
          } else {
            card.style.opacity = '0';
            setTimeout(() => {
              card.style.display = 'none';
            }, 300);
          }
        });
      });
    });
  }

  // Back To Top Handler
  function initBackToTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) return;

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initPreloader();
    initMobileMenu();
    initCategoryFilter();
    initBackToTop();
  });
})();
