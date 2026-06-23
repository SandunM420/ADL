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

    var productUrl = PRODUCT_URL_BASE + '?id=' + encodeURIComponent(product.id);
    var gradeTypeHtml = product.grape_type
      ? '<div class="product-card__detail"><span class="product-card__detail-label">Grape Type</span><span class="product-card__detail-value">' + escHtml(product.grape_type) + '</span></div>'
      : '';
    var alcoholHtml = product.alcohol
      ? '<div class="product-card__detail"><span class="product-card__detail-label">Alcohol</span><span class="product-card__detail-value">' + escHtml(product.alcohol) + '</span></div>'
      : '';
    var packSizeHtml = product.pack_size
      ? '<div class="product-card__detail"><span class="product-card__detail-label">Pack Size</span><span class="product-card__detail-value">' + escHtml(product.pack_size) + '</span></div>'
      : '';
    var tastingNoteHtml = product.description
      ? '<div class="product-card__detail"><span class="product-card__detail-label">Tasting Note</span><span class="product-card__detail-value">' + escHtml(product.description) + '</span></div>'
      : '';

    var flagHtml = window.getCountryFlag ? window.getCountryFlag(product.country, 'sm') : '';

    return (
      '<article class="product-card">' +
        (flagHtml ? '<div class="product-card__flag-wrap">' + flagHtml + '</div>' : '') +
        '<a href="' + productUrl + '" class="product-card__image" tabindex="-1" aria-hidden="true">' +
          '<img src="' + imgSrc + '" alt="' + escHtml(product.name) + '" loading="lazy" width="300" height="400">' +
        '</a>' +
        '<div class="product-card__body">' +
          '<h2 class="product-card__name">' +
            '<a href="' + productUrl + '">' + escHtml(product.name) + '</a>' +
          '</h2>' +
          gradeTypeHtml +
          alcoholHtml +
          packSizeHtml +
          tastingNoteHtml +
          '<a href="' + productUrl + '" class="btn btn-primary btn-full product-card__cta">Find Out More</a>' +
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
