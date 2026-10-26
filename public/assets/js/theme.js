// public/assets/js/theme.js
(function() {
  'use strict';

  // Terima config dari server via data attribute atau inline script
  const config = window.SCHOOL_CONFIG || {};

  const root = document.documentElement;

  // Mapping config ke CSS variables
  const tokenMap = {
    school_name: '--school-name',           // dipakai di header/logo alt
    school_short_name: '--school-short-name',
    primary_color: '--color-primary',
    secondary_color: '--color-secondary',
    accent_color: '--color-accent',
    primary_hover: '--color-primary-hover',
    primary_light: '--color-primary-light',
    secondary_hover: '--color-secondary-hover',
    secondary_light: '--color-secondary-light',
    accent_hover: '--color-accent-hover',
    accent_light: '--color-accent-light',
    font_sans: '--font-sans',
    border_radius: '--radius-md',
    header_height: '--header-height',
    sidebar_width: '--sidebar-width',
  };

  Object.entries(tokenMap).forEach(([key, cssVar]) => {
    if (config[key]) {
      root.style.setProperty(cssVar, config[key]);
    }
  });

  // Logo handling
  if (config.school_logo) {
    root.style.setProperty('--school-logo', `url("${config.school_logo}")`);
    document.querySelectorAll('.header__logo, .school-logo').forEach(el => {
      if (el.tagName === 'IMG') {
        el.src = config.school_logo;
        el.alt = config.school_name || 'School Logo';
      } else {
        el.style.backgroundImage = `url("${config.school_logo}")`;
        el.style.backgroundSize = 'contain';
        el.style.backgroundRepeat = 'no-repeat';
        el.style.backgroundPosition = 'center';
      }
    });
  }

  // Favicon
  if (config.school_favicon) {
    let link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.rel = 'icon';
    link.href = config.school_favicon;
    document.head.appendChild(link);
  }

  // RTL
  if (config.rtl) {
    document.documentElement.dir = 'rtl';
    document.documentElement.lang = config.locale || 'ar';
  }

  // Theme (dark/light)
  if (config.theme) {
    document.documentElement.setAttribute('data-theme', config.theme);
  }

  // Expose untuk JS lain
  window.DesignSystem = { config, root };
})();
