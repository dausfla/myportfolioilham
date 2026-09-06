/**
 * Video Player & Fullscreen Photo Lightbox
 * Ilham Ramadhan Setiawan Portfolio
 */

(function () {
  'use strict';

  // Custom Video Player Controls
  function initVideoPlayers() {
    const videoContainers = document.querySelectorAll('.video-player-container');

    videoContainers.forEach(container => {
      const video = container.querySelector('video');
      const overlay = container.querySelector('.video-controls-overlay');
      const playBtn = container.querySelector('.play-trigger-btn');

      if (!video || !overlay) return;

      overlay.addEventListener('click', () => {
        if (video.paused) {
          video.play();
          container.classList.add('playing');
          overlay.style.opacity = '0';
          overlay.style.pointerEvents = 'none';
        }
      });

      video.addEventListener('click', () => {
        if (!video.paused) {
          video.pause();
          container.classList.remove('playing');
          overlay.style.opacity = '1';
          overlay.style.pointerEvents = 'auto';
        }
      });

      video.addEventListener('ended', () => {
        container.classList.remove('playing');
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
      });
    });
  }

  // Fullscreen Photo Lightbox
  function initLightbox() {
    const lightboxModal = document.getElementById('lightbox-modal');
    const lightboxImg = document.getElementById('lightbox-image');
    const lightboxClose = document.getElementById('lightbox-close');
    const lightboxPrev = document.getElementById('lightbox-prev');
    const lightboxNext = document.getElementById('lightbox-next');
    const lightboxCaption = document.getElementById('lightbox-caption');

    if (!lightboxModal || !lightboxImg) return;

    const galleryFrames = Array.from(document.querySelectorAll('.gallery-image-frame'));
    let currentIndex = 0;

    function openLightbox(index) {
      if (index < 0 || index >= galleryFrames.length) return;
      currentIndex = index;

      const frame = galleryFrames[currentIndex];
      const img = frame.querySelector('img');
      const captionText = frame.getAttribute('data-caption') || img?.getAttribute('alt') || '';

      if (img) {
        lightboxImg.src = frame.getAttribute('data-full-src') || img.src;
        if (lightboxCaption) {
          lightboxCaption.textContent = captionText;
        }
        lightboxModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeLightbox() {
      lightboxModal.classList.remove('active');
      document.body.style.overflow = '';
    }

    function showPrev() {
      if (galleryFrames.length === 0) return;
      currentIndex = (currentIndex - 1 + galleryFrames.length) % galleryFrames.length;
      openLightbox(currentIndex);
    }

    function showNext() {
      if (galleryFrames.length === 0) return;
      currentIndex = (currentIndex + 1) % galleryFrames.length;
      openLightbox(currentIndex);
    }

    galleryFrames.forEach((frame, index) => {
      frame.addEventListener('click', () => openLightbox(index));
    });

    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    if (lightboxPrev) lightboxPrev.addEventListener('click', showPrev);
    if (lightboxNext) lightboxNext.addEventListener('click', showNext);

    lightboxModal.addEventListener('click', (e) => {
      if (e.target === lightboxModal) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!lightboxModal.classList.contains('active')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') showPrev();
      if (e.key === 'ArrowRight') showNext();
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initVideoPlayers();
    initLightbox();
  });
})();
