// Hides the green offer strip after 30 November 2026 (SA time), even if the page is served from cache.
document.addEventListener('DOMContentLoaded', function () {
  if (Date.now() >= Date.parse('2026-12-01T00:00:00+02:00')) {
    var strip = document.getElementById('wasc-offer-strip');
    if (strip) strip.remove();
  }
});
