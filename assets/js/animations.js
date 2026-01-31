document.addEventListener('DOMContentLoaded', function () {
  var items = Array.prototype.slice.call(document.querySelectorAll('.animate-item'));
  items.forEach(function (el, index) {
    var d = 0;
    // Allow per-element delay via data-delay-ms or stagger classes
    if (el.dataset && el.dataset.delayMs) d = parseInt(el.dataset.delayMs, 10) || 0;
    else if (el.classList.contains('stagger-1')) d = 90;
    else if (el.classList.contains('stagger-2')) d = 180;
    else if (el.classList.contains('stagger-3')) d = 270;
    // Add a small index-based additional offset for consistent pacing
    var baseOffset = index * 90;
    setTimeout(function () { el.classList.add('enter'); }, d + baseOffset);
  });

  // Optional: animate appearance for toasts and dynamically inserted elements
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes && m.addedNodes.forEach(function (node) {
        if (node.nodeType === 1 && node.classList.contains('animate-item')) {
          // slight delay to allow CSS layout
          setTimeout(function () { node.classList.add('enter'); }, 40);
        }
      });
    });
  });
  observer.observe(document.body, { childList: true, subtree: true });
});
