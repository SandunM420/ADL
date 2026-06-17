(function () {
  'use strict';

  var NAV_HTML = [
    '<nav class="navbar" role="navigation" aria-label="Main navigation">',
    '  <div class="container">',
    '    <div class="navbar__inner">',

    '      <a href="/" class="navbar__logo"><img src="/assets/images/logo-admin.png" alt="Abeywardana Distributors"></a>',

    '      <ul class="navbar__nav" role="list">',
    '        <li class="nav-item">',
    '          <a href="/" class="nav-link">Home</a>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/wines/chile.html" class="nav-link" aria-haspopup="true">',
    '            Wines',
    '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>',
    '          </a>',
    '          <div class="nav-dropdown" role="menu">',
    '            <a href="/wines/chile.html" role="menuitem">Chile</a>',
    '            <a href="/wines/australia.html" role="menuitem">Australia</a>',
    '            <a href="/wines/south-africa.html" role="menuitem">South Africa</a>',
    '            <a href="/wines/spain.html" role="menuitem">Spain</a>',
    '          </div>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/champagne/france.html" class="nav-link" aria-haspopup="true">',
    '            Champagne',
    '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>',
    '          </a>',
    '          <div class="nav-dropdown" role="menu">',
    '            <a href="/champagne/france.html" role="menuitem">France</a>',
    '          </div>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/sparkling-wine/index.html" class="nav-link">Sparkling Wine</a>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/spirits/whiskey.html" class="nav-link" aria-haspopup="true">',
    '            Spirits',
    '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>',
    '          </a>',
    '          <div class="nav-dropdown" role="menu">',
    '            <a href="/spirits/whiskey.html" role="menuitem">Whiskey</a>',
    '            <a href="/spirits/rum.html" role="menuitem">Rum</a>',
    '            <a href="/spirits/gin.html" role="menuitem">Gin</a>',
    '            <a href="/spirits/vodka.html" role="menuitem">Vodka</a>',
    '            <a href="/spirits/brandy.html" role="menuitem">Brandy</a>',
    '            <a href="/spirits/liquor.html" role="menuitem">Liquor</a>',
    '          </div>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/about.html" class="nav-link">About Us</a>',
    '        </li>',

    '        <li class="nav-item">',
    '          <a href="/contact.html" class="btn btn-primary btn-sm">Contact Us</a>',
    '        </li>',
    '      </ul>',

    '      <button class="navbar__hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav">',
    '        <span></span>',
    '        <span></span>',
    '        <span></span>',
    '      </button>',

    '    </div>',
    '  </div>',
    '</nav>',

    '<div class="navbar__mobile" id="mobile-nav" aria-hidden="true">',
    '  <a href="/" class="mobile-nav-link">Home</a>',

    '  <details class="mobile-nav-group">',
    '    <summary>Wines <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></summary>',
    '    <div class="mobile-nav-sub">',
    '      <a href="/wines/chile.html">Chile</a>',
    '      <a href="/wines/australia.html">Australia</a>',
    '      <a href="/wines/south-africa.html">South Africa</a>',
    '      <a href="/wines/spain.html">Spain</a>',
    '    </div>',
    '  </details>',

    '  <details class="mobile-nav-group">',
    '    <summary>Champagne <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></summary>',
    '    <div class="mobile-nav-sub">',
    '      <a href="/champagne/france.html">France</a>',
    '    </div>',
    '  </details>',

    '  <a href="/sparkling-wine/index.html" class="mobile-nav-link">Sparkling Wine</a>',

    '  <details class="mobile-nav-group">',
    '    <summary>Spirits <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></summary>',
    '    <div class="mobile-nav-sub">',
    '      <a href="/spirits/whiskey.html">Whiskey</a>',
    '      <a href="/spirits/rum.html">Rum</a>',
    '      <a href="/spirits/gin.html">Gin</a>',
    '      <a href="/spirits/vodka.html">Vodka</a>',
    '      <a href="/spirits/brandy.html">Brandy</a>',
    '      <a href="/spirits/liquor.html">Liquor</a>',
    '    </div>',
    '  </details>',

    '  <a href="/about.html" class="mobile-nav-link">About Us</a>',
    '  <a href="/contact.html" class="mobile-nav-link">Contact Us</a>',
    '</div>'
  ].join('\n');

  function init() {
    var placeholder = document.getElementById('navbar-placeholder');
    if (!placeholder) return;

    var wrapper = document.createElement('div');
    wrapper.innerHTML = NAV_HTML;

    while (wrapper.firstChild) {
      placeholder.parentNode.insertBefore(wrapper.firstChild, placeholder);
    }
    placeholder.parentNode.removeChild(placeholder);

    setActiveLinks();
    bindMobileMenu();
  }

  function setActiveLinks() {
    var path = window.location.pathname;
    var links = document.querySelectorAll('.nav-link, .nav-dropdown a, .mobile-nav-sub a');
    for (var i = 0; i < links.length; i++) {
      if (links[i].getAttribute('href') === path) {
        links[i].classList.add('active');
      }
    }
  }

  function bindMobileMenu() {
    var hamburger = document.querySelector('.navbar__hamburger');
    var mobileNav = document.getElementById('mobile-nav');
    if (!hamburger || !mobileNav) return;

    hamburger.addEventListener('click', function () {
      var isOpen = mobileNav.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', String(isOpen));
      mobileNav.setAttribute('aria-hidden', String(!isOpen));
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
