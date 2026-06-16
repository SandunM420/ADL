(function () {
  'use strict';

  var cfg              = window.PAGE_CONFIG || {};
  var CATEGORY         = cfg.category         || '';
  var SUBCATEGORY      = cfg.subcategory       || null;
  var BADGE            = cfg.badge             || '';
  var PRODUCT_URL_BASE = cfg.productUrlPrefix  || '';

  var API_URL = '/api/products.php?category=' + encodeURIComponent(CATEGORY);
  if (SUBCATEGORY) {
    API_URL += '&subcategory=' + encodeURIComponent(SUBCATEGORY);
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function buildCard(product) {
    var imgSrc = product.image
      ? '/' + escHtml(product.image)
      : '/assets/images/products/placeholder.jpg';

    var countryHtml = product.country
      ? '<p class="product-card__country">' + escHtml(product.country) + '</p>'
      : '';

    var productUrl = PRODUCT_URL_BASE + '?id=' + encodeURIComponent(product.id);
    var enquiryUrl = '/contact.html?product=' + encodeURIComponent(product.name);

    return (
      '<article class="product-card">' +
        '<a href="' + productUrl + '" class="product-card__image" tabindex="-1" aria-hidden="true">' +
          '<img src="' + imgSrc + '" alt="' + escHtml(product.name) + '" loading="lazy" width="300" height="400">' +
        '</a>' +
        '<div class="product-card__body">' +
          '<span class="product-card__badge">' + escHtml(BADGE) + '</span>' +
          '<h2 class="product-card__name">' +
            '<a href="' + productUrl + '">' + escHtml(product.name) + '</a>' +
          '</h2>' +
          countryHtml +
        '</div>' +
        '<div class="product-card__footer">' +
          '<a href="' + enquiryUrl + '" class="btn btn-outline btn-full">Enquire Now</a>' +
        '</div>' +
      '</article>'
    );
  }

  function renderProducts(products) {
    var area = document.getElementById('product-area');
    if (!products.length) {
      area.innerHTML =
        '<div class="empty-state">' +
          '<p class="empty-state__title">No products available</p>' +
          '<p class="empty-state__text">This section is being updated. Please <a href="/contact.html">contact us</a> for enquiries.</p>' +
        '</div>';
      return;
    }
    var html = '<div class="product-grid">';
    for (var i = 0; i < products.length; i++) {
      html += buildCard(products[i]);
    }
    html += '</div>';
    area.innerHTML = html;
  }

  function renderError() {
    document.getElementById('product-area').innerHTML =
      '<div class="empty-state">' +
        '<p class="empty-state__title">Unable to load products</p>' +
        '<p class="empty-state__text">Please refresh the page. If the problem persists, <a href="/contact.html">contact us</a>.</p>' +
      '</div>';
  }

  fetch(API_URL)
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (data) {
      if (data && data.error) throw new Error(data.error);
      renderProducts(Array.isArray(data) ? data : []);
    })
    .catch(renderError);
}());
