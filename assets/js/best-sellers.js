(function () {
  'use strict';

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function renderBestSellers(items) {
    var area = document.getElementById('best-sellers-area');
    if (!area) return;

    if (!Array.isArray(items) || items.length === 0) {
      area.innerHTML = '';
      return;
    }

    var html = '';
    items.forEach(function (item) {
      var title = item.title || '';
      var image = item.image ? '/' + item.image : '/assets/images/products/placeholder.jpg';
      var enquiryUrl = '/contact.html?product=' + encodeURIComponent(title);

      html +=
        '<div class="product-card home-product-card">' +
          '<div class="product-card__image">' +
            '<img src="' + escHtml(image) + '" alt="' + escHtml(title) + '" loading="lazy">' +
          '</div>' +
          '<div class="product-card__body">' +
            '<p class="product-card__name">' + escHtml(title) + '</p>' +
          '</div>' +
          '<div class="product-card__footer">' +
            '<a href="' + enquiryUrl + '" class="btn btn-outline">Enquire</a>' +
          '</div>' +
        '</div>';
    });

    area.innerHTML = html;
  }

  fetch('/assets/data/best-sellers.json?v=' + Date.now())
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(renderBestSellers)
    .catch(function () {
      renderBestSellers([]);
    });
}());
