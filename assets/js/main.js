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
    const closeBtn = document.getElementById('mobile-overlay-close');
    const overlay = document.getElementById('mobile-overlay');
    
    if (!overlay) return;

    function openMenu() {
      overlay.classList.add('active');
      if (toggleBtn) toggleBtn.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      overlay.classList.remove('active');
      if (toggleBtn) toggleBtn.classList.remove('active');
      document.body.style.overflow = '';
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const isActive = overlay.classList.contains('active');
        if (isActive) {
          closeMenu();
        } else {
          openMenu();
        }
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', closeMenu);
    }

    const mobileLinks = overlay.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', closeMenu);
    });
  }

  // Spoiler Video Players (Google Drive & MP4)
  function initVideoPlayers() {
    const videoCards = document.querySelectorAll('.video-player-card');
    videoCards.forEach(card => {
      const type = card.getAttribute('data-video-type');
      const overlay = card.querySelector('.video-interactive-overlay');
      const playBtn = card.querySelector('.custom-play-pause-btn');
      const btnText = card.querySelector('.btn-text');
      const btnIcon = card.querySelector('.btn-icon');
      const mp4Video = card.querySelector('video');

      if (!overlay) return;

      let isPlaying = false;

      function handleToggle(e) {
        if (e) e.stopPropagation();

        if (type === 'mp4' && mp4Video) {
          if (mp4Video.paused) {
            mp4Video.muted = false;
            mp4Video.play();
            isPlaying = true;
            overlay.classList.add('active-playing');
            if (btnText) btnText.textContent = 'JEDA VIDEO';
            if (btnIcon) btnIcon.innerHTML = '&#10074;&#10074;';
          } else {
            mp4Video.pause();
            isPlaying = false;
            overlay.classList.remove('active-playing');
            if (btnText) btnText.textContent = 'PUTAR & UNMUTE';
            if (btnIcon) btnIcon.innerHTML = '&#9658;';
          }
        } else {
          // Google Drive video embed
          if (!isPlaying) {
            isPlaying = true;
            overlay.classList.add('active-playing');
            if (btnText) btnText.textContent = 'JEDA / KELUAR';
            if (btnIcon) btnIcon.innerHTML = '&#10074;&#10074;';
          } else {
            isPlaying = false;
            overlay.classList.remove('active-playing');
            if (btnText) btnText.textContent = 'PUTAR / JEDA VIDEO';
            if (btnIcon) btnIcon.innerHTML = '&#9658;';
          }
        }
      }

      overlay.addEventListener('click', handleToggle);
      if (playBtn) playBtn.addEventListener('click', handleToggle);

      if (mp4Video) {
        mp4Video.addEventListener('pause', () => {
          overlay.classList.remove('active-playing');
          if (btnText) btnText.textContent = 'PUTAR VIDEO';
          if (btnIcon) btnIcon.innerHTML = '&#9658;';
        });
      }
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
    initVideoPlayers();
    initCategoryFilter();
    initBackToTop();
  });
})();
