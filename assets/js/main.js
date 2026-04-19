/**
 * Genacom CRM — light enhancement (M38-friendly: no emoji chrome)
 */
(function () {
  var topbar = document.querySelector('.app-topbar');
  if (topbar) {
    window.addEventListener(
      'scroll',
      function () {
        topbar.classList.toggle('scrolled', window.scrollY > 8);
      },
      { passive: true }
    );
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -24px 0px' }
  );

  document.querySelectorAll('.fade-up').forEach(function (el) {
    observer.observe(el);
  });
})();
