(function () {
  const storageKey = 'pb_rotate_map_v1';

  function normalizeDeg(value) {
    const n = Number(value);
    if (!Number.isFinite(n)) return 0;
    const mod = ((Math.round(n) % 360) + 360) % 360;
    if (mod === 90 || mod === 180 || mod === 270) return mod;
    return 0;
  }

  function normalizePhotoKey(src) {
    if (!src) return '';
    try {
      // Use pathname (includes directories) to avoid key collisions when filenames repeat.
      const u = new URL(String(src), window.location.href);
      return decodeURIComponent(u.pathname || '');
    } catch (e) {
      return String(src);
    }
  }

  // Legacy key (basename only). Kept for backward-compatible reads.
  function normalizeLegacyPhotoKey(src) {
    if (!src) return '';
    try {
      const clean = String(src).split('#')[0].split('?')[0];
      const parts = clean.split('/');
      const last = parts[parts.length - 1] || clean;
      return decodeURIComponent(last);
    } catch (e) {
      return String(src);
    }
  }

  function readMap() {
    try {
      const raw = window.localStorage.getItem(storageKey);
      if (!raw) return {};
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return {};
      return parsed;
    } catch (e) {
      return {};
    }
  }

  function writeMap(map) {
    try {
      window.localStorage.setItem(storageKey, JSON.stringify(map || {}));
    } catch (e) {
      // ignore
    }
  }

  function getDeg(photoSrc) {
    const key = normalizePhotoKey(photoSrc);
    const legacyKey = normalizeLegacyPhotoKey(photoSrc);
    if (!key && !legacyKey) return 0;
    const map = readMap();
    if (key && Object.prototype.hasOwnProperty.call(map, key)) return normalizeDeg(map[key]);
    if (legacyKey && Object.prototype.hasOwnProperty.call(map, legacyKey)) return normalizeDeg(map[legacyKey]);
    return 0;
  }

  function setDeg(photoSrc, deg) {
    const key = normalizePhotoKey(photoSrc);
    if (!key) return;
    const map = readMap();
    map[key] = normalizeDeg(deg);
    writeMap(map);
  }

  function applyRotationToImg(imgEl, deg) {
    if (!imgEl) return;
    const d = normalizeDeg(deg);
    imgEl.style.transform = d ? `rotate(${d}deg)` : 'rotate(0deg)';
    imgEl.style.transformOrigin = 'center center';
    imgEl.style.transition = 'transform 120ms ease-out';
  }

  function applyToAllThumbnails(root = document) {
    const imgs = root.querySelectorAll('img.rotatable-photo[data-rotate-src]');
    imgs.forEach((img) => {
      const src = img.getAttribute('data-rotate-src');
      applyRotationToImg(img, getDeg(src));
    });
  }

  function initRotateButtons(root = document) {
    const buttons = root.querySelectorAll('button.photo-rotate-btn[data-photo-src]');
    buttons.forEach((btn) => {
      if (btn.__pbRotateBound) return;
      btn.__pbRotateBound = true;
      btn.addEventListener('click', () => {
        const src = btn.getAttribute('data-photo-src');
        const next = normalizeDeg(getDeg(src) + 90);
        setDeg(src, next);

        const wrapper = btn.closest('.photo-item') || btn.parentElement;
        const img = wrapper ? wrapper.querySelector('img.rotatable-photo[data-rotate-src]') : null;
        if (img) applyRotationToImg(img, next);

        try {
          window.dispatchEvent(
            new CustomEvent('pb:rotate-changed', {
              detail: { photoSrc: src, deg: next },
            })
          );
        } catch (e) {
          // ignore
        }
      });
    });
  }

  function init(root = document) {
    initRotateButtons(root);
    applyToAllThumbnails(root);
  }

  window.PB_ROTATE = {
    normalizeDeg,
    normalizePhotoKey,
    normalizeLegacyPhotoKey,
    getDeg,
    setDeg,
    applyRotationToImg,
    init,
  };
})();
