/**
 * Analytics loader: Google Analytics 4 and Microsoft Clarity.
 * Both are off until an ID is filled in below, so nothing is requested from
 * Google or Microsoft with an empty or placeholder ID.
 *
 *   GA_ID       GA4 measurement ID, looks like G-XXXXXXXXXX
 *   CLARITY_ID  Clarity project ID, a short alphanumeric string
 *
 * Events go through window.track(name, params). No form contents or chat text
 * are ever sent. The chat log and the contact form carry data-clarity-mask so
 * Clarity recordings hide what visitors type and read there.
 */
(function () {
  'use strict';

  var GA_ID = '';
  var CLARITY_ID = '';

  window.dataLayer = window.dataLayer || [];
  function gtag() { window.dataLayer.push(arguments); }

  if (GA_ID) {
    var ga = document.createElement('script');
    ga.async = true;
    ga.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
    document.head.appendChild(ga);
    gtag('js', new Date());
    gtag('config', GA_ID);
  }

  if (CLARITY_ID) {
    (function (c, l, a, r, i, t, y) {
      c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
      t = l.createElement(r); t.async = 1; t.src = 'https://www.clarity.ms/tag/' + i;
      y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
    })(window, document, 'clarity', 'script', CLARITY_ID);
  }

  window.track = function (name, params) {
    if (GA_ID) { gtag('event', name, params || {}); }
    if (CLARITY_ID && typeof window.clarity === 'function') { window.clarity('event', name); }
  };

  // Which button sent the visitor to the contact form
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href="#contact"]');
    if (a) { window.track('cta_click', { cta_text: (a.textContent || '').trim().slice(0, 60) }); }
  });
})();
