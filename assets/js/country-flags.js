(function () {
  'use strict';

  var COUNTRY_ISO = {
    'chile': 'cl', 'australia': 'au', 'south africa': 'za', 'spain': 'es',
    'france': 'fr', 'italy': 'it', 'portugal': 'pt', 'germany': 'de',
    'argentina': 'ar', 'new zealand': 'nz', 'usa': 'us', 'united states': 'us',
    'scotland': 'gb', 'ireland': 'ie', 'england': 'gb', 'united kingdom': 'gb',
    'jamaica': 'jm', 'cuba': 'cu', 'mexico': 'mx', 'russia': 'ru',
    'netherlands': 'nl', 'japan': 'jp', 'greece': 'gr', 'sweden': 'se',
    'denmark': 'dk', 'finland': 'fi', 'norway': 'no', 'india': 'in',
    'sri lanka': 'lk', 'brazil': 'br', 'peru': 'pe', 'canada': 'ca',
  };

  /**
   * Returns an <img> tag for the country flag, or empty string if no match.
   * size: 'sm' (28px, for card badges) | 'md' (48px, for detail pages)
   */
  // flagcdn.com only supports these exact widths: 20, 40, 80, 160
  // sm: display 28px → fetch w40 (1x) / w80 (2x)
  // md: display 48px → fetch w80 (1x) / w160 (2x)
  window.getCountryFlag = function (country, size) {
    if (!country) return '';
    var code = COUNTRY_ISO[country.toLowerCase().trim()];
    if (!code) return '';
    var isMd   = size === 'md';
    var w      = isMd ? 48 : 28;
    var src    = 'https://flagcdn.com/' + (isMd ? 'w80' : 'w40') + '/' + code + '.png';
    var srcset = 'https://flagcdn.com/' + (isMd ? 'w160' : 'w80') + '/' + code + '.png 2x';
    return '<img class="country-flag country-flag--' + (size || 'sm') + '" ' +
           'src="' + src + '" srcset="' + srcset + '" ' +
           'alt="' + country + ' flag" width="' + w + '" loading="lazy">';
  };
}());
