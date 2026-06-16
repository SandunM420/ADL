(function () {
  'use strict';

  var ROTATE_INTERVAL_MS = 3000;

  function init() {
    var slides = document.querySelectorAll('.hero__bg');
    if (slides.length < 2) return;

    var activeIndex = 0;

    setInterval(function () {
      slides[activeIndex].classList.remove('is-active');
      activeIndex = (activeIndex + 1) % slides.length;
      slides[activeIndex].classList.add('is-active');
    }, ROTATE_INTERVAL_MS);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
