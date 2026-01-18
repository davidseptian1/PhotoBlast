@extends('layouts.app')
@section('content')
@php
  $isListOrPrint = Request::routeIs('list-photo') || Request::routeIs('print-photo');
  $bgKey = Request::routeIs('print-photo') ? 'print-photo' : 'list-photo';
@endphp
<section class="content @if($isListOrPrint) listphoto-bg @endif" @if($isListOrPrint) style="background-image: url('{{ \App\Models\PageBackground::url($bgKey, 'img/bgkamera.png') }}');" @endif>
  @include('layouts.nav')
  <div class="chooseTitle">
    <h1>SELECT</h1>
    <h1>SELECT</h1>
    <h1>SELECT</h1>
    <h1>SELECT</h1>
  </div>
  <div class="tagline">
    <span style="color: #cfd4da">YOUR </span>
    <span style="font-weight: bolder; color: white;">@if(Request::routeIs('print-photo')) {{ $limit ?? 3 }} @endif PHOTO @if(Request::routeIs('list-photo')) FRAME @endif</span>
  </div>
  <section id="list-photo">
    @if(Request::routeIs('list-photo'))
      @php
        $layoutKey = (string)($layout_key ?? 'layout2');
        $layoutNumber = match($layoutKey) {
          'layout1' => 1,
          'layout2' => 2,
          'layout3' => 3,
          'layout4' => 4,
          default => null,
        };
      @endphp

      <div class="listphoto-shell">
        <div class="listphoto-grid">
          <div class="listphoto-card">
            <div style="opacity:0.9; font-family: 'Bungee'; font-size:16px; margin-bottom: 6px;">Pilihan Layout {{ $layoutNumber ?? '-' }}</div>
            @php
              $layoutCount = (int) ($layout_count ?? 0);
              $modeLabel = null;
              if (($layoutKey ?? '') === 'layout2') {
                $modeLabel = ($layoutCount >= 4) ? 'Mode: 2×2 (4 foto)' : 'Mode: 2×1 (2 foto)';
              } elseif ($layoutCount > 0) {
                $modeLabel = 'Capture: '.$layoutCount.' foto';
              }
            @endphp
            @if($modeLabel)
              <div style="display:inline-flex; align-items:center; gap:8px; padding:6px 10px; margin-bottom:10px; border-radius:999px; background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.22); color: rgba(255,255,255,0.92); font-size:12px;">
                <span style="width:8px; height:8px; border-radius:999px; background: rgba(249,115,22,0.95); display:inline-block;"></span>
                <span>{{ $modeLabel }}</span>
              </div>
            @endif

            <div id="pbLayoutHint" style="display:none; padding:10px 12px; margin-bottom: 12px; border-radius: 12px; border: 1px solid rgba(249,115,22,0.55); background: rgba(249,115,22,0.10); color: rgba(255,255,255,0.92); font-size: 12px; line-height: 1.35;">
              Frame ini butuh <b>4 foto (2×2)</b>, tapi mode kamu masih <b>2 foto (2×1)</b>. Pilih <b>Layout 2 (2×2)</b> di halaman layout supaya kamera capture 4x.
              <a href="{{ route('tempcollage.index') }}" class="formbutton" style="display:inline-block; margin-left:10px; padding:6px 10px; border-radius: 10px; min-width: unset; font-size: 12px;">PILIH LAYOUT</a>
            </div>
            <h2>Pilih Frame</h2>
            <div style="opacity:0.78; font-size:13px; margin-bottom:12px;">Frames tersedia: {{ is_array($frames ?? null) ? count($frames) : 0 }}</div>

            <form method="POST" action="{{ route('camera.frame') }}">
              @csrf
              <button type="submit" class="formbutton" name="frame" value="" style="width:100%; padding: 10px 14px; border-radius: 14px; border: 2px solid rgba(255,255,255,0.85); background: rgba(255,255,255,0.08); cursor:pointer;">NO FRAME</button>

              <div style="height:12px;"></div>

              @if(empty($frames ?? []))
                <div style="color:#fff; opacity:0.9;">Belum ada frame untuk layout ini. Tambahkan di halaman admin.</div>
              @else
                <div class="frame-grid" aria-label="Daftar frame">
                  @foreach(($frames ?? []) as $frame)
                    <button type="submit" name="frame" value="{{ $frame['id'] ?? $frame['file'] }}" class="frame-btn @if(!empty($frameUrl) && $frameUrl === $frame['url']) is-selected @endif" aria-label="Pilih frame">
                      <img src="{{ $frame['url'] }}" alt="Frame" />
                    </button>
                  @endforeach
                </div>
              @endif
            </form>

            <div style="opacity:0.75; font-size: 12px; margin-top: 12px;">Frame yang tampil di sini berasal dari upload admin untuk layout ini.</div>
          </div>

          <div class="listphoto-card">
            <h2>Preview</h2>
            @if(empty($photoUrls ?? []))
              <div style="opacity:0.85;">Foto belum ditemukan. Pastikan sudah mengambil foto di kamera.</div>
            @endif
            <div style="display:flex; align-items:flex-start; gap:10px; flex-wrap:wrap; overflow:hidden;">
              <div class="pb-preview-controls" style="display:flex; flex-direction:column; gap:10px; align-items:center; justify-content:center; flex: 0 0 200px;">
                <div class="pb-preview-label" style="margin-top:2px;">Preview</div>
                <div style="display:flex; gap:8px; align-items:center; justify-content:center;">
                  <button id="globalZoomOutBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">-</button>
                  <button id="globalResetZoomBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">Reset</button>
                  <button id="globalZoomInBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">+</button>
                </div>
                <div class="pb-preview-label">View: <span id="globalZoomPct">100%</span></div>
                <div class="pb-zoom-indicator" role="progressbar" aria-label="Preview zoom indicator" aria-valuemin="50" aria-valuemax="250" style="width:100%; height:8px; background: rgba(255,255,255,0.12); border-radius: 999px; overflow:hidden; border: 1px solid rgba(255,255,255,0.18);">
                  <span id="globalZoomIndicator" style="display:block; height:100%; width:50%; background: linear-gradient(90deg, rgba(59,130,246,0.95), rgba(249,115,22,0.95));"></span>
                </div>

                <div style="width:100%; height:1px; background: rgba(255,255,255,0.12); margin: 2px 0;"></div>
                <div class="pb-preview-label">Selected Photo</div>
                <div style="display:flex; gap:8px; align-items:center; justify-content:center; width:100%;">
                  <button id="zoomOutBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">-</button>
                  <button id="resetZoomBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">Reset</button>
                  <button id="zoomInBtn" class="formbutton" type="button" style="padding:6px 10px; border-radius: 12px; min-width: unset;">+</button>
                </div>
                <div class="pb-preview-label">Zoom: <span id="zoomPct">100%</span></div>
                <div class="pb-zoom-indicator" role="progressbar" aria-label="Selected photo zoom indicator" aria-valuemin="20" aria-valuemax="300" style="width:100%; height:8px; background: rgba(255,255,255,0.12); border-radius: 999px; overflow:hidden; border: 1px solid rgba(255,255,255,0.18);">
                  <span id="zoomIndicator" style="display:block; height:100%; width:33%; background: linear-gradient(90deg, rgba(34,197,94,0.95), rgba(59,130,246,0.95));"></span>
                </div>

                <div class="pb-preview-label" @if(Request::routeIs('list-photo')) style="display:none;" @endif>Filter</div>
                <select id="filterSelect" class="formbutton" @if(Request::routeIs('list-photo')) style="display:none;" @else style="padding:8px 10px; border-radius: 12px; min-width: unset; width:100%; background: rgba(255,255,255,0.08); color:#fff; border:2px solid rgba(255,255,255,0.28);" @endif>
                  <option value="none" style="background:#0b1220; color:#fff;">NONE</option>
                  <option value="bw" style="background:#0b1220; color:#fff;">B&W</option>
                  <option value="sepia" style="background:#0b1220; color:#fff;">SEPIA</option>
                  <option value="vivid" style="background:#0b1220; color:#fff;">VIVID</option>
                  <option value="soft" style="background:#0b1220; color:#fff;">SOFT</option>
                  <option value="cool" style="background:#0b1220; color:#fff;">COOL</option>
                  <option value="warm" style="background:#0b1220; color:#fff;">WARM</option>
                </select>

                <div style="width:100%; height:1px; background: rgba(255,255,255,0.12); margin: 2px 0; @if(Request::routeIs('list-photo')) display:none; @endif"></div>
                <div style="color:#cfd4da; font-size:12px; white-space:nowrap; @if(Request::routeIs('list-photo')) display:none; @endif">Sticker</div>
                <div id="stickerPicker" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:center; width:100%; @if(Request::routeIs('list-photo')) display:none; @endif">
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="star" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/star.svg') }}" alt="Star" style="width:26px; height:26px; display:block;" />
                  </button>
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="heart" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/heart.svg') }}" alt="Heart" style="width:26px; height:26px; display:block;" />
                  </button>
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="sparkle" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/sparkle.svg') }}" alt="Sparkle" style="width:26px; height:26px; display:block;" />
                  </button>
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="crown" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/crown.svg') }}" alt="Crown" style="width:26px; height:26px; display:block;" />
                  </button>
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="glasses" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/glasses.svg') }}" alt="Glasses" style="width:26px; height:26px; display:block;" />
                  </button>
                  <button type="button" class="formbutton sticker-btn" data-sticker-id="speech" style="padding:6px 8px; border-radius:12px; min-width: unset;">
                    <img src="{{ asset('img/stickers/speech.svg') }}" alt="Speech" style="width:26px; height:26px; display:block;" />
                  </button>
                </div>
                <div style="display:flex; gap:8px; justify-content:center; width:100%; @if(Request::routeIs('list-photo')) display:none; @endif">
                  <button id="stickerRemoveBtn" type="button" class="formbutton" style="padding:6px 10px; border-radius:12px; min-width: unset;">REMOVE</button>
                  <button id="stickerSizeDownBtn" type="button" class="formbutton" style="padding:6px 10px; border-radius:12px; min-width: unset;">-</button>
                  <button id="stickerSizeUpBtn" type="button" class="formbutton" style="padding:6px 10px; border-radius:12px; min-width: unset;">+</button>
                </div>

                <button id="swapModeBtn" class="formbutton" type="button" style="padding:12px 14px; border-radius: 14px; min-width: unset; font-size: 16px; letter-spacing: 0.3px;">MOVE</button>
                <div style="color:#cfd4da; font-size:11px; opacity:0.85; text-align:center; line-height:1.25;">
                  Pilih foto di bawah, klik <b>MOVE</b>, lalu klik slot di preview
                </div>
              </div>
              <div style="flex:1; min-width: 320px; display:flex; justify-content:center; align-items:flex-start; overflow:hidden;">
                <canvas id="previewCanvas" width="600" height="846" class="preview-canvas" style="max-width:100%; height:auto; touch-action:none;"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="listphoto-card">
          <h2 style="margin-bottom: 12px;">Pilih Foto untuk Retake</h2>
          <form id="retakeForm" action="{{ route('retake-photo') }}" method="post">
            @csrf
            @foreach(collect($photos)->chunk(5) as $photoschunk)
              <div class="photo-group">
                @foreach($photoschunk as $photo)
                  <div class="photo-item">
                    <input type="checkbox" name="photos[]" class="checkbox-limit" id="{{ $photo }}" value="{{ $photo }}">
                    @php $photoSrc = asset(str_replace('public', 'storage', $photo)); @endphp
                    <label for="{{ $photo }}"><img class="rotatable-photo" data-rotate-src="{{ $photoSrc }}" src="{{ $photoSrc }}" alt=""></label>
                  </div>
                @endforeach
              </div>
            @endforeach
            <div class="formgroup">
              <div style="display:flex; gap:40px; align-items:center; justify-content:center; width:100%;">
                <button type="submit" class="formbutton">RETAKE</button>
                <a id="nextBtn" href="{{ route('print-photo') }}" class="formbutton">NEXT</a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <script src="{{ asset('js/rotate.js') }}?v={{ filemtime(public_path('js/rotate.js')) }}"></script>
      <script>
        const previewLayoutKey = {!! json_encode($layout_key ?? 'layout2') !!};
        const previewLayoutCount = {!! json_encode((int)($layout_count ?? 0)) !!};
        const previewPhotos = {!! json_encode($photoUrls ?? []) !!};
        const previewFrameUrl = {!! json_encode($frameUrl ?? null) !!};
        const previewFrameConfig = {!! json_encode($frameConfig ?? null) !!};
        const previewEmail = {!! json_encode($email ?? null) !!};
        const saveCollageUrl = {!! json_encode(route('save-collage')) !!};
        const globalGapConfig = {!! json_encode(['row_gap_ratio' => (float) ($global_row_gap_ratio ?? config('photoblast.row_gap_ratio', 0.012))]) !!};
        const padding = 40;

        // Global preview (view) zoom: scales the entire canvas rendering.
        let previewViewScale = 1.0;

        function normalizeRotateDeg(value) {
          return (window.PB_ROTATE && typeof window.PB_ROTATE.normalizeDeg === 'function')
            ? window.PB_ROTATE.normalizeDeg(value)
            : 0;
        }

        function getPhotoRotateDeg(photoSrc) {
          return (window.PB_ROTATE && typeof window.PB_ROTATE.getDeg === 'function')
            ? window.PB_ROTATE.getDeg(photoSrc)
            : 0;
        }

        function getGridGapPx(canvasWidth, canvasHeight) {
          const cfg = previewFrameConfig || {};
          const ratio = (typeof cfg.grid_gap_ratio === 'number') ? cfg.grid_gap_ratio : 0.020;
          const base = Math.min(canvasWidth, canvasHeight);
          return Math.max(0, Math.round(base * ratio));
        }

        function getRowGapPx(canvasWidth, canvasHeight) {
          const cfg = globalGapConfig || {};
          const ratio = (typeof cfg.row_gap_ratio === 'number') ? cfg.row_gap_ratio : null;
          if (ratio === null) return getGridGapPx(canvasWidth, canvasHeight);
          const base = Math.min(canvasWidth, canvasHeight);
          return Math.max(0, Math.round(base * Math.max(0, Math.min(0.060, ratio))));
        }

        function getCells(layoutKey, canvasWidth, canvasHeight) {
          const colGap = getGridGapPx(canvasWidth, canvasHeight);
          const rowGap = getRowGapPx(canvasWidth, canvasHeight);
          const innerW = canvasWidth - padding * 2;
          const innerH = canvasHeight - padding * 2;
          if (layoutKey === 'layout1') {
            // Full-bleed A6 for layout1
            return [{ x: 0, y: 0, w: canvasWidth, h: canvasHeight }];
          }
          if (layoutKey === 'layout2') {
            // Layout2 supports 2-photo (2×1) and 4-photo (2×2) variants.
            const want4 = (typeof previewLayoutCount === 'number' && previewLayoutCount >= 4);
            const cols = 2, rows = want4 ? 2 : 1;
            const cellW = (innerW - colGap * (cols - 1)) / cols;
            const cellH = (innerH - rowGap * (rows - 1)) / rows;
            const top = padding;
            const left = padding;
            const cells = [];
            for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++) {
              const slot = r * cols + c;
              cells.push({
                x: left + c * (cellW + colGap),
                y: top + r * (cellH + rowGap),
                w: cellW,
                h: cellH,
                row: r,
                col: c,
                photoIndex: slot,
              });
            }
            return cells;
          }
          if (layoutKey === 'layout3') {
            // layout3: 2 kolom × 3 baris => 6 foto unik (slot 0..5)
            const cols = 2, rows = 3;
            const cellW = (innerW - colGap * (cols - 1)) / cols;
            const cellH = (innerH - rowGap * (rows - 1)) / rows;
            const cells = [];
            for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++) {
              const slot = r * cols + c;
              cells.push({ x: padding + c * (cellW + colGap), y: padding + r * (cellH + rowGap), w: cellW, h: cellH, photoIndex: slot });
            }
            return cells;
          }
          // layout4
          const cols = 2, rows = 3;
          const cellW = (innerW - colGap * (cols - 1)) / cols;
          const cellH = (innerH - rowGap * (rows - 1)) / rows;
          const cells = [];
          for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++) {
            cells.push({ x: padding + c * (cellW + colGap), y: padding + r * (cellH + rowGap), w: cellW, h: cellH });
          }
          return cells;
        }

        function roundedRectPath(ctx, x, y, w, h, r) {
          const radius = Math.max(0, Math.min(r, w / 2, h / 2));
          ctx.beginPath();
          ctx.moveTo(x + radius, y);
          ctx.arcTo(x + w, y, x + w, y + h, radius);
          ctx.arcTo(x + w, y + h, x, y + h, radius);
          ctx.arcTo(x, y + h, x, y, radius);
          ctx.arcTo(x, y, x + w, y, radius);
          ctx.closePath();
        }

        function computeContainRect(iw, ih, x, y, w, h) {
          const scale = Math.min(w / iw, h / ih);
          const dw = iw * scale;
          const dh = ih * scale;
          const dx = x + (w - dw) / 2;
          const dy = y + (h - dh) / 2;
          return { dx, dy, dw, dh };
        }

        function computeCoverRect(iw, ih, x, y, w, h) {
          const scale = Math.max(w / iw, h / ih);
          const dw = iw * scale;
          const dh = ih * scale;
          const dx = x + (w - dw) / 2;
          const dy = y + (h - dh) / 2;
          return { dx, dy, dw, dh };
        }

        function computeCoverRectAnchored(iw, ih, x, y, w, h, ax = 0.5, ay = 0.5) {
          const scale = Math.max(w / iw, h / ih);
          const dw = iw * scale;
          const dh = ih * scale;
          const clamp = (v) => Math.max(0, Math.min(1, v));
          const dx = x + (w - dw) * clamp(ax);
          const dy = y + (h - dh) * clamp(ay);
          return { dx, dy, dw, dh };
        }

        function drawImageRotated(ctx, img, dx, dy, dw, dh, rotateDeg) {
          const deg = normalizeRotateDeg(rotateDeg);
          if (!deg) {
            ctx.drawImage(img, dx, dy, dw, dh);
            return;
          }
          const rad = (deg * Math.PI) / 180;
          ctx.save();
          ctx.translate(dx + dw / 2, dy + dh / 2);
          ctx.rotate(rad);
          ctx.drawImage(img, -dw / 2, -dh / 2, dw, dh);
          ctx.restore();
        }

        // per-photo preview zoom & pan (1.0 = 100%)
        const previewUserScaleByKey = new Map();
        const previewUserPanByKey = new Map(); // key -> {x,y} in canvas px
        const previewUserFilterByKey = new Map(); // key -> filter id
        const previewUserStickerBySlot = new Map(); // slotIndex(photoIndex) -> {id,x,y,s}
        let previewSelectedPhotoSrc = (Array.isArray(previewPhotos) && previewPhotos.length) ? previewPhotos[0] : null;
        let previewSelectedPhotoKey = null;
        let previewSelectedCellIndex = null;
        let previewSelectedPhotoIndex = null;

        const __PB_FILTER_STORAGE_KEY = 'pb_filters_v1';
        const __PB_STICKER_STORAGE_KEY = 'pb_stickers_slots_v1';
        const __PB_STICKER_STORAGE_KEY_LEGACY = 'pb_stickers_v1';
        const __PB_FILTER_PRESETS = {
          none: { label: 'NONE', css: 'none' },
          bw: { label: 'B&W', css: 'grayscale(1)' },
          sepia: { label: 'SEPIA', css: 'sepia(1)' },
          vivid: { label: 'VIVID', css: 'contrast(1.15) saturate(1.35)' },
          soft: { label: 'SOFT', css: 'brightness(1.08) contrast(1.05) saturate(1.08)' },
          cool: { label: 'COOL', css: 'hue-rotate(190deg) saturate(1.25) contrast(1.03)' },
          warm: { label: 'WARM', css: 'hue-rotate(-15deg) saturate(1.25) contrast(1.03)' },
        };

        function __normalizeLegacyBasenameKey(srcOrKey) {
          if (!srcOrKey) return '';
          try {
            const clean = String(srcOrKey).split('#')[0].split('?')[0];
            const parts = clean.split('/');
            return decodeURIComponent(parts[parts.length - 1] || clean);
          } catch (e) {
            return String(srcOrKey);
          }
        }

        function __buildBasenameToStableKeyIndex() {
          const index = new Map();
          (Array.isArray(previewPhotos) ? previewPhotos : []).forEach((src) => {
            const base = String(__normalizeLegacyBasenameKey(src) || '').toLowerCase();
            const stable = normalizePhotoKeyForZoom(src);
            if (!base || !stable) return;
            if (!index.has(base)) index.set(base, new Set());
            index.get(base).add(stable);
          });
          return index;
        }

        function __loadFilterStore() {
          try {
            const raw = localStorage.getItem(__PB_FILTER_STORAGE_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return;
            const baseIndex = __buildBasenameToStableKeyIndex();
            let needsSave = false;
            Object.keys(parsed).forEach((k) => {
              const v = parsed[k];
              if (typeof v !== 'string' || !__PB_FILTER_PRESETS[v]) return;

              // Stable keys (pathname or URL)
              if (typeof k === 'string' && (k.includes('/') || k.startsWith('http'))) {
                const stableKey = normalizePhotoKeyForZoom(k);
                if (stableKey) previewUserFilterByKey.set(stableKey, v);
                if (stableKey !== k) needsSave = true;
                return;
              }

              // Legacy basename-only keys
              const base = String(__normalizeLegacyBasenameKey(k) || '').toLowerCase();
              const candidates = baseIndex.get(base);
              if (candidates && candidates.size === 1) {
                const stableKey = Array.from(candidates)[0];
                previewUserFilterByKey.set(stableKey, v);
              }
              // If ambiguous or not found, drop to prevent cross-photo coupling.
              needsSave = true;
            });

            if (needsSave) __saveFilterStore();
          } catch (e) {
            // ignore
          }
        }

        function __saveFilterStore() {
          try {
            const obj = {};
            previewUserFilterByKey.forEach((v, k) => {
              if (typeof k === 'string' && k && typeof v === 'string' && __PB_FILTER_PRESETS[v]) obj[k] = v;
            });
            localStorage.setItem(__PB_FILTER_STORAGE_KEY, JSON.stringify(obj));
          } catch (e) {
            // ignore
          }
        }

        function __loadStickerStore() {
          // Primary: slot-based store
          try {
            const raw = localStorage.getItem(__PB_STICKER_STORAGE_KEY);
            if (raw) {
              const parsed = JSON.parse(raw);
              if (parsed && typeof parsed === 'object') {
                Object.keys(parsed).forEach((k) => {
                  const slot = Number(k);
                  if (!Number.isFinite(slot) || slot < 0) return;
                  const v = parsed[k];
                  if (!v || typeof v !== 'object') return;
                  const id = (typeof v.id === 'string') ? v.id : null;
                  const x = (typeof v.x === 'number' && isFinite(v.x)) ? v.x : 0.5;
                  const y = (typeof v.y === 'number' && isFinite(v.y)) ? v.y : 0.5;
                  const s = (typeof v.s === 'number' && isFinite(v.s)) ? v.s : 0.35;
                  if (!id) return;
                  previewUserStickerBySlot.set(slot, { id, x: __clamp(x, 0, 1), y: __clamp(y, 0, 1), s: __clamp(s, 0.10, 0.90) });
                });
              }
              return;
            }
          } catch (e) {
            // ignore
          }

          // Migration: legacy per-photo store => slot-based, using current previewPhotos order.
          try {
            const legacyRaw = localStorage.getItem(__PB_STICKER_STORAGE_KEY_LEGACY);
            if (!legacyRaw) return;
            const parsed = JSON.parse(legacyRaw);
            if (!parsed || typeof parsed !== 'object') return;

            let migrated = false;
            const files = Array.isArray(previewPhotos) ? previewPhotos : [];
            for (let i = 0; i < files.length; i++) {
              const src = files[i];
              const stableKey = normalizePhotoKeyForZoom(src);
              const legacyKey = __normalizeLegacyBasenameKey(src);
              const v = (stableKey && parsed[stableKey]) ? parsed[stableKey] : (legacyKey && parsed[legacyKey] ? parsed[legacyKey] : null);
              if (!v || typeof v !== 'object') continue;
              const id = (typeof v.id === 'string') ? v.id : null;
              const x = (typeof v.x === 'number' && isFinite(v.x)) ? v.x : 0.5;
              const y = (typeof v.y === 'number' && isFinite(v.y)) ? v.y : 0.5;
              const s = (typeof v.s === 'number' && isFinite(v.s)) ? v.s : 0.35;
              if (!id) continue;
              previewUserStickerBySlot.set(i, { id, x: __clamp(x, 0, 1), y: __clamp(y, 0, 1), s: __clamp(s, 0.10, 0.90) });
              migrated = true;
            }

            if (migrated) __saveStickerStore();
          } catch (e) {
            // ignore
          }
        }

        function __saveStickerStore() {
          try {
            const obj = {};
            previewUserStickerBySlot.forEach((v, slot) => {
              if (typeof slot !== 'number' || !isFinite(slot) || slot < 0) return;
              if (!v || typeof v !== 'object' || typeof v.id !== 'string') return;
              obj[String(slot)] = { id: v.id, x: __clamp(v.x, 0, 1), y: __clamp(v.y, 0, 1), s: __clamp(v.s, 0.10, 0.90) };
            });
            localStorage.setItem(__PB_STICKER_STORAGE_KEY, JSON.stringify(obj));
          } catch (e) {
            // ignore
          }
        }

        function getSlotSticker(slotIndex) {
          const slot = Number(slotIndex);
          if (!Number.isFinite(slot) || slot < 0) return null;
          const v = previewUserStickerBySlot.get(slot);
          if (!v || typeof v !== 'object' || typeof v.id !== 'string') return null;
          return { id: v.id, x: __clamp(v.x, 0, 1), y: __clamp(v.y, 0, 1), s: __clamp(v.s, 0.10, 0.90) };
        }

        function setSlotSticker(slotIndex, stickerObjOrNull, opts) {
          const persist = !(opts && opts.persist === false);
          const slot = Number(slotIndex);
          if (!Number.isFinite(slot) || slot < 0) return;
          if (!stickerObjOrNull) {
            previewUserStickerBySlot.delete(slot);
            if (persist) __saveStickerStore();
            return;
          }
          const id = (typeof stickerObjOrNull.id === 'string') ? stickerObjOrNull.id : null;
          if (!id) return;
          const x = (typeof stickerObjOrNull.x === 'number') ? stickerObjOrNull.x : 0.5;
          const y = (typeof stickerObjOrNull.y === 'number') ? stickerObjOrNull.y : 0.5;
          const s = (typeof stickerObjOrNull.s === 'number') ? stickerObjOrNull.s : 0.35;
          previewUserStickerBySlot.set(slot, { id, x: __clamp(x, 0, 1), y: __clamp(y, 0, 1), s: __clamp(s, 0.10, 0.90) });
          if (persist) __saveStickerStore();
        }

        const __PB_STICKER_IDS = ['star','heart','sparkle','crown','glasses','speech'];
        const __PB_STICKER_IMG_CACHE = new Map(); // id -> HTMLImageElement
        const __PB_STICKER_PROMISE_CACHE = new Map(); // id -> Promise<HTMLImageElement|null>
        const __PB_STICKER_BASE_URL = {!! json_encode(asset('img/stickers')) !!};

        function __getStickerUrl(id) {
          return String(__PB_STICKER_BASE_URL).replace(/\/$/, '') + '/' + encodeURIComponent(id) + '.svg';
        }

        function __getStickerImg(id) {
          if (!id) return Promise.resolve(null);
          if (__PB_STICKER_IMG_CACHE.has(id)) return Promise.resolve(__PB_STICKER_IMG_CACHE.get(id));
          if (__PB_STICKER_PROMISE_CACHE.has(id)) return __PB_STICKER_PROMISE_CACHE.get(id);
          const p = loadImage(__getStickerUrl(id)).then((img) => {
            __PB_STICKER_IMG_CACHE.set(id, img);
            return img;
          }).catch(() => null);
          __PB_STICKER_PROMISE_CACHE.set(id, p);
          return p;
        }

        function __drawSticker(ctx, stickerImg, rx, ry, rw, rh, sticker) {
          if (!stickerImg || !sticker) return;
          const size = Math.max(8, Math.round(Math.min(rw, rh) * (sticker.s || 0.35)));
          const x = rx + rw * (sticker.x ?? 0.5);
          const y = ry + rh * (sticker.y ?? 0.5);
          ctx.save();
          ctx.filter = 'none';
          ctx.globalAlpha = 1.0;
          ctx.translate(x, y);
          ctx.drawImage(stickerImg, -size / 2, -size / 2, size, size);
          ctx.restore();
        }

        function getPhotoFilterId(photoSrc) {
          if (!photoSrc) return 'none';
          const key = normalizePhotoKeyForZoom(photoSrc);
          const v = previewUserFilterByKey.get(key);
          return (typeof v === 'string' && __PB_FILTER_PRESETS[v]) ? v : 'none';
        }

        function getPhotoFilterCss(photoSrc) {
          const id = getPhotoFilterId(photoSrc);
          return (__PB_FILTER_PRESETS[id] && __PB_FILTER_PRESETS[id].css) ? __PB_FILTER_PRESETS[id].css : 'none';
        }

        function setPhotoFilterId(photoSrc, filterId) {
          if (!photoSrc) return;
          const key = normalizePhotoKeyForZoom(photoSrc);
          const id = (typeof filterId === 'string' && __PB_FILTER_PRESETS[filterId]) ? filterId : 'none';
          previewUserFilterByKey.set(key, id);
          __saveFilterStore();
        }

        function normalizePhotoKeyForZoom(src) {
          if (!src) return '';
          try {
            const u = new URL(String(src), window.location.href);
            return decodeURIComponent(u.pathname || '');
          } catch (e) {
            try {
              const clean = String(src).split('#')[0].split('?')[0];
              const parts = clean.split('/');
              const last = parts[parts.length - 1] || clean;
              return decodeURIComponent(last);
            } catch (e2) {
              return String(src);
            }
          }
        }

        function __clamp(v, min, max) {
          return Math.max(min, Math.min(max, v));
        }

        function getPhotoUserScale(photoSrc) {
          if (!photoSrc) return 1.0;
          const key = normalizePhotoKeyForZoom(photoSrc);
          const v = previewUserScaleByKey.get(key);
          return (typeof v === 'number' && isFinite(v)) ? v : 1.0;
        }

        function setPhotoUserScale(photoSrc, v) {
          if (!photoSrc) return;
          const key = normalizePhotoKeyForZoom(photoSrc);
          previewUserScaleByKey.set(key, __clamp(v, 0.2, 3.0));
        }

        function getPhotoUserPan(photoSrc) {
          if (!photoSrc) return { x: 0, y: 0 };
          const key = normalizePhotoKeyForZoom(photoSrc);
          const v = previewUserPanByKey.get(key);
          if (!v || typeof v !== 'object') return { x: 0, y: 0 };
          const x = (typeof v.x === 'number' && isFinite(v.x)) ? v.x : 0;
          const y = (typeof v.y === 'number' && isFinite(v.y)) ? v.y : 0;
          return { x, y };
        }

        function setPhotoUserPan(photoSrc, pan) {
          if (!photoSrc) return;
          const key = normalizePhotoKeyForZoom(photoSrc);
          const x = (pan && typeof pan.x === 'number' && isFinite(pan.x)) ? pan.x : 0;
          const y = (pan && typeof pan.y === 'number' && isFinite(pan.y)) ? pan.y : 0;
          previewUserPanByKey.set(key, { x, y });
        }

        function resetPhotoUserPan(photoSrc) {
          if (!photoSrc) return;
          setPhotoUserPan(photoSrc, { x: 0, y: 0 });
        }

        function __findFirstCellIndexForPhoto(photoSrc) {
          if (!photoSrc) return null;
          const key = normalizePhotoKeyForZoom(photoSrc);
          if (Array.isArray(__pbPreviewHitCells) && __pbPreviewHitCells.length) {
            const hit = __pbPreviewHitCells.find((c) => c && c.photoSrc && normalizePhotoKeyForZoom(c.photoSrc) === key);
            if (hit && typeof hit.cellIndex === 'number') return hit.cellIndex;
          }
          if (Array.isArray(previewPhotos) && previewPhotos.length) {
            const idx = previewPhotos.findIndex((src) => normalizePhotoKeyForZoom(src) === key);
            if (idx >= 0) return idx;
          }
          return null;
        }

        function __findPhotoIndexForPhoto(photoSrc) {
          if (!photoSrc) return null;
          const key = normalizePhotoKeyForZoom(photoSrc);
          if (Array.isArray(previewPhotos) && previewPhotos.length) {
            const idx = previewPhotos.findIndex((src) => normalizePhotoKeyForZoom(src) === key);
            return idx >= 0 ? idx : null;
          }
          return null;
        }

        function setSelectedPhoto(photoSrc, slotIndexOrOpts) {
          if (!photoSrc) return;
          previewSelectedPhotoSrc = photoSrc;
          previewSelectedPhotoKey = normalizePhotoKeyForZoom(photoSrc) || null;
          const cellIndex = (typeof slotIndexOrOpts === 'number')
            ? slotIndexOrOpts
            : (slotIndexOrOpts && typeof slotIndexOrOpts.cellIndex === 'number' ? slotIndexOrOpts.cellIndex : null);
          const photoIndex = (slotIndexOrOpts && typeof slotIndexOrOpts.photoIndex === 'number') ? slotIndexOrOpts.photoIndex : null;
          previewSelectedCellIndex = (cellIndex !== null) ? cellIndex : __findFirstCellIndexForPhoto(photoSrc);
          previewSelectedPhotoIndex = (photoIndex !== null) ? photoIndex : __findPhotoIndexForPhoto(photoSrc);
          const zoomPct = document.getElementById('zoomPct');
          if (zoomPct) zoomPct.textContent = Math.round(getPhotoUserScale(previewSelectedPhotoSrc) * 100) + '%';
        }

        function drawContain(ctx, img, x, y, w, h, rotateDeg = 0, userScale = 1.0, userPan = { x: 0, y: 0 }) {
          const iw = img.naturalWidth || img.width;
          const ih = img.naturalHeight || img.height;
          const deg = normalizeRotateDeg(rotateDeg);
          const iwEff = (deg === 90 || deg === 270) ? ih : iw;
          const ihEff = (deg === 90 || deg === 270) ? iw : ih;
          const base = computeContainRect(iwEff, ihEff, x, y, w, h);
          // apply user scale: scale around center of slot
          const scaledDw = base.dw * userScale;
          const scaledDh = base.dh * userScale;
          // allow pan both when image is larger (crop) or smaller (letterbox)
          const maxPanX = Math.abs(scaledDw - w) / 2;
          const maxPanY = Math.abs(scaledDh - h) / 2;
          const panX = __clamp(userPan?.x ?? 0, -maxPanX, maxPanX);
          const panY = __clamp(userPan?.y ?? 0, -maxPanY, maxPanY);
          const scaledDx = x + (w - scaledDw) / 2 + panX;
          const scaledDy = y + (h - scaledDh) / 2 + panY;
          drawImageRotated(ctx, img, scaledDx, scaledDy, scaledDw, scaledDh, deg);
        }

        function drawCover(ctx, img, x, y, w, h, rotateDeg = 0, userScale = 1.0, userPan = { x: 0, y: 0 }) {
          const iw = img.naturalWidth || img.width;
          const ih = img.naturalHeight || img.height;
          const deg = normalizeRotateDeg(rotateDeg);
          const iwEff = (deg === 90 || deg === 270) ? ih : iw;
          const ihEff = (deg === 90 || deg === 270) ? iw : ih;
          const base = computeCoverRect(iwEff, ihEff, x, y, w, h);
          const dw = base.dw * userScale;
          const dh = base.dh * userScale;
          const maxPanX = Math.abs(dw - w) / 2;
          const maxPanY = Math.abs(dh - h) / 2;
          const panX = __clamp(userPan?.x ?? 0, -maxPanX, maxPanX);
          const panY = __clamp(userPan?.y ?? 0, -maxPanY, maxPanY);
          const dx = x + (w - dw) / 2 + panX;
          const dy = y + (h - dh) / 2 + panY;
          drawImageRotated(ctx, img, dx, dy, dw, dh, deg);
        }

        function drawCoverAnchored(ctx, img, x, y, w, h, rotateDeg = 0, ax = 0.5, ay = 0.5) {
          const iw = img.naturalWidth || img.width;
          const ih = img.naturalHeight || img.height;
          const deg = normalizeRotateDeg(rotateDeg);
          const iwEff = (deg === 90 || deg === 270) ? ih : iw;
          const ihEff = (deg === 90 || deg === 270) ? iw : ih;
          const { dx, dy, dw, dh } = computeCoverRectAnchored(iwEff, ihEff, x, y, w, h, ax, ay);
          drawImageRotated(ctx, img, dx, dy, dw, dh, deg);
        }

        function drawPhotoWithBorder(ctx, img, x, y, w, h, rotateDeg = 0, userScale = 1.0, userPan = { x: 0, y: 0 }) {
          const cfg = previewFrameConfig || {};
          const slot = Math.min(w, h);

          // Make the photo a bit smaller inside the slot
          const padRatio = (typeof cfg.pad_ratio === 'number') ? cfg.pad_ratio : 0.070;
          const pad = Math.max(10, Math.round(slot * padRatio));
          const rx = x + pad;
          const ry = y + pad;
          const rw = Math.max(1, w - pad * 2);
          const rh = Math.max(1, h - pad * 2);

          // Photo (contain)
          const iw = img.naturalWidth || img.width;
          const ih = img.naturalHeight || img.height;
          const deg = normalizeRotateDeg(rotateDeg);
          const iwEff = (deg === 90 || deg === 270) ? ih : iw;
          const ihEff = (deg === 90 || deg === 270) ? iw : ih;
          const base = computeContainRect(iwEff, ihEff, rx, ry, rw, rh);
          const cfgScale = (typeof cfg.scale === 'number') ? cfg.scale : 1.000;
          const offsetX = (typeof cfg.offset_x === 'number') ? cfg.offset_x : 0.000;
          const offsetY = (typeof cfg.offset_y === 'number') ? cfg.offset_y : 0.000;
          const finalScale = cfgScale * userScale;
          const dw = base.dw * finalScale;
          const dh = base.dh * finalScale;
          const maxPanX = Math.abs(dw - rw) / 2;
          const maxPanY = Math.abs(dh - rh) / 2;
          const panX = __clamp(userPan?.x ?? 0, -maxPanX, maxPanX);
          const panY = __clamp(userPan?.y ?? 0, -maxPanY, maxPanY);
          const dx = rx + (rw - dw) / 2 + (offsetX * rw) + panX;
          const dy = ry + (rh - dh) / 2 + (offsetY * rh) + panY;

          // Border follows actual drawn photo area (no letterbox space)
          const borderW = Math.max(3, Math.round(slot * 0.012));
          const bx = dx + borderW / 2;
          const by = dy + borderW / 2;
          const bw = Math.max(1, dw - borderW);
          const bh = Math.max(1, dh - borderW);
          const radius = Math.max(10, Math.round(Math.min(bw, bh) * 0.07));

          // Clip photo to rounded corners so it never shows outside the border
          ctx.save();
          roundedRectPath(ctx, bx, by, bw, bh, radius);
          ctx.clip();
          drawImageRotated(ctx, img, dx, dy, dw, dh, deg);
          ctx.restore();

          // Border on top
          ctx.save();
          ctx.lineWidth = borderW;
          ctx.strokeStyle = 'rgba(255,255,255,0.92)';
          roundedRectPath(ctx, bx, by, bw, bh, radius);
          ctx.stroke();
          ctx.restore();
        }

        function loadImage(src) {
          return new Promise((resolve, reject) => {
            const img = new Image();
            // Keep same-origin to avoid CORS issues when APP_URL differs from current host
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
          });
        }

        // Image caches to avoid re-loading on every render (prevents flicker during drag).
        const __PB_PHOTO_IMG_CACHE = new Map(); // src -> HTMLImageElement
        const __PB_PHOTO_PROMISE_CACHE = new Map(); // src -> Promise<HTMLImageElement|null>
        const __PB_FRAME_IMG_CACHE = new Map(); // url -> HTMLImageElement
        const __PB_FRAME_PROMISE_CACHE = new Map(); // url -> Promise<HTMLImageElement|null>

        function __getPhotoImg(src) {
          if (!src) return Promise.resolve(null);
          if (__PB_PHOTO_IMG_CACHE.has(src)) return Promise.resolve(__PB_PHOTO_IMG_CACHE.get(src));
          if (__PB_PHOTO_PROMISE_CACHE.has(src)) return __PB_PHOTO_PROMISE_CACHE.get(src);
          const p = loadImage(src).then((img) => {
            __PB_PHOTO_IMG_CACHE.set(src, img);
            return img;
          }).catch(() => null);
          __PB_PHOTO_PROMISE_CACHE.set(src, p);
          return p;
        }

        function __getFrameImg(url) {
          if (!url) return Promise.resolve(null);
          if (__PB_FRAME_IMG_CACHE.has(url)) return Promise.resolve(__PB_FRAME_IMG_CACHE.get(url));
          if (__PB_FRAME_PROMISE_CACHE.has(url)) return __PB_FRAME_PROMISE_CACHE.get(url);
          const p = loadImage(url).then((img) => {
            __PB_FRAME_IMG_CACHE.set(url, img);
            return img;
          }).catch(() => null);
          __PB_FRAME_PROMISE_CACHE.set(url, p);
          return p;
        }

        function isBackgroundFrame(url) {
          if (!url) return false;
          const clean = String(url).split('?')[0].toLowerCase();
          return clean.endsWith('.jpg') || clean.endsWith('.jpeg');
        }

        function estimateHasAlpha(img) {
          const iw = img.naturalWidth || img.width;
          const ih = img.naturalHeight || img.height;
          const w = Math.max(1, Math.min(220, iw));
          const h = Math.max(1, Math.min(220, ih));
          const c = document.createElement('canvas');
          c.width = w;
          c.height = h;
          const cctx = c.getContext('2d', { willReadFrequently: true });
          cctx.clearRect(0, 0, w, h);
          cctx.drawImage(img, 0, 0, w, h);
          const data = cctx.getImageData(0, 0, w, h).data;
          let nonOpaque = 0;
          const stride = 16 * 4;
          for (let i = 3; i < data.length; i += stride) {
            if (data[i] < 250) nonOpaque++;
          }
          const sampledTotal = Math.ceil(data.length / stride);
          const ratio = sampledTotal ? nonOpaque / sampledTotal : 0;
          return ratio > 0.01;
        }

        const __windowRectCache = Object.create(null);

        function __isNearWhite(r, g, b, a) {
          // Treat transparent pixels as "window" too (common in PNG frames with holes).
          // Keep near-white support for frames that use white placeholders.
          if (a <= 40) return true;
          if (a < 220) return false;
          return r >= 245 && g >= 245 && b >= 245;
        }

        function __roundedRectPath(ctx, x, y, w, h, r) {
          const radius = Math.max(0, Math.min(r || 0, Math.min(w, h) / 2));
          ctx.beginPath();
          if (radius <= 0) {
            ctx.rect(x, y, w, h);
            return;
          }
          if (typeof ctx.roundRect === 'function') {
            ctx.roundRect(x, y, w, h, radius);
            return;
          }
          const rr = radius;
          ctx.moveTo(x + rr, y);
          ctx.arcTo(x + w, y, x + w, y + h, rr);
          ctx.arcTo(x + w, y + h, x, y + h, rr);
          ctx.arcTo(x, y + h, x, y, rr);
          ctx.arcTo(x, y, x + w, y, rr);
          ctx.closePath();
        }

        function __estimateRadiusRatioFromMask(minX, minY, maxX, maxY, w, h, data) {
          const boxW = (maxX - minX + 1);
          const boxH = (maxY - minY + 1);
          const minSide = Math.max(1, Math.min(boxW, boxH));
          const maxR = Math.max(0, Math.floor(minSide / 2));

          function isWhiteAt(x, y) {
            if (x < 0 || y < 0 || x >= w || y >= h) return false;
            const i = (y * w + x) * 4;
            return __isNearWhite(data[i], data[i + 1], data[i + 2], data[i + 3]);
          }

          function whiteScore2x2(x, y, sx, sy) {
            let score = 0;
            if (isWhiteAt(x, y)) score++;
            if (isWhiteAt(x + sx, y)) score++;
            if (isWhiteAt(x, y + sy)) score++;
            if (isWhiteAt(x + sx, y + sy)) score++;
            return score;
          }

          function diagRadius(cornerX, cornerY, sx, sy) {
            for (let d = 0; d <= maxR; d++) {
              const x = cornerX + sx * d;
              const y = cornerY + sy * d;
              if (whiteScore2x2(x, y, sx, sy) >= 3) return d;
            }
            return 0;
          }

          const tl = diagRadius(minX, minY, 1, 1);
          const tr = diagRadius(maxX, minY, -1, 1);
          const bl = diagRadius(minX, maxY, 1, -1);
          const br = diagRadius(maxX, maxY, -1, -1);
          const r = Math.min(tl, tr, bl, br);
          const stable = (r <= 1) ? 0 : r; // ignore tiny AA corners
          return Math.max(0, Math.min(0.5, stable / minSide));
        }

        function detectWhiteWindowsFromFrame(img, maxCount) {
          try {
            const iw = img.naturalWidth || img.width;
            const ih = img.naturalHeight || img.height;
            if (!iw || !ih) return null;

            // Downscale for faster detection
            const targetW = Math.max(180, Math.min(520, iw));
            const scale = targetW / iw;
            const w = Math.max(1, Math.round(iw * scale));
            const h = Math.max(1, Math.round(ih * scale));

            const c = document.createElement('canvas');
            c.width = w;
            c.height = h;
            const cctx = c.getContext('2d', { willReadFrequently: true });
            cctx.clearRect(0, 0, w, h);
            cctx.drawImage(img, 0, 0, w, h);
            const data = cctx.getImageData(0, 0, w, h).data;

            const visited = new Uint8Array(w * h);
            const queue = new Int32Array(w * h);
            const comps = [];

            for (let p = 0; p < w * h; p++) {
              if (visited[p]) continue;
              const i = p * 4;
              if (!__isNearWhite(data[i], data[i + 1], data[i + 2], data[i + 3])) continue;

              let qh = 0, qt = 0;
              queue[qt++] = p;
              visited[p] = 1;

              let minX = w, minY = h, maxX = 0, maxY = 0;
              let area = 0;
              let touchesBorder = false;

              while (qh < qt) {
                const idx = queue[qh++];
                const x = idx % w;
                const y = (idx / w) | 0;
                area++;

                if (x < minX) minX = x;
                if (y < minY) minY = y;
                if (x > maxX) maxX = x;
                if (y > maxY) maxY = y;

                if (x === 0 || y === 0 || x === (w - 1) || y === (h - 1)) touchesBorder = true;

                // 4-neighbors
                if (x > 0) {
                  const n = idx - 1;
                  if (!visited[n]) {
                    const ni = n * 4;
                    if (__isNearWhite(data[ni], data[ni + 1], data[ni + 2], data[ni + 3])) {
                      visited[n] = 1;
                      queue[qt++] = n;
                    }
                  }
                }
                if (x + 1 < w) {
                  const n = idx + 1;
                  if (!visited[n]) {
                    const ni = n * 4;
                    if (__isNearWhite(data[ni], data[ni + 1], data[ni + 2], data[ni + 3])) {
                      visited[n] = 1;
                      queue[qt++] = n;
                    }
                  }
                }
                if (y > 0) {
                  const n = idx - w;
                  if (!visited[n]) {
                    const ni = n * 4;
                    if (__isNearWhite(data[ni], data[ni + 1], data[ni + 2], data[ni + 3])) {
                      visited[n] = 1;
                      queue[qt++] = n;
                    }
                  }
                }
                if (y + 1 < h) {
                  const n = idx + w;
                  if (!visited[n]) {
                    const ni = n * 4;
                    if (__isNearWhite(data[ni], data[ni + 1], data[ni + 2], data[ni + 3])) {
                      visited[n] = 1;
                      queue[qt++] = n;
                    }
                  }
                }
              }

              // Filter tiny blobs
              // Also ignore blobs touching the border (usually the outside transparent background).
              if (!touchesBorder && area >= Math.max(400, Math.round(w * h * 0.002))) {
                comps.push({ minX, minY, maxX, maxY, area });
              }
            }

            if (comps.length < 1) return null;
            comps.sort((a, b) => b.area - a.area);
            const want = Math.max(1, Math.min(12, Number(maxCount) || 1));
            const top = comps.slice(0, want);

            // Sort into reading order: top-to-bottom, left-to-right
            top.sort((a, b) => (a.minY - b.minY) || (a.minX - b.minX));

            // Convert to normalized rects (0..1) based on original image size
            const rects = top.map((c) => {
              const x0 = c.minX / w;
              const y0 = c.minY / h;
              const x1 = (c.maxX + 1) / w;
              const y1 = (c.maxY + 1) / h;
              const rr = __estimateRadiusRatioFromMask(c.minX, c.minY, c.maxX, c.maxY, w, h, data);
              return {
                x: Math.max(0, Math.min(1, x0)),
                y: Math.max(0, Math.min(1, y0)),
                w: Math.max(0, Math.min(1, x1 - x0)),
                h: Math.max(0, Math.min(1, y1 - y0)),
                r: rr,
              };
            });

            return rects;
          } catch (e) {
            return null;
          }
        }

        function getWindowRects(frameUrl, frameImg, maxCount) {
          if (!frameUrl || !frameImg) return null;
          const key = `${frameUrl}|${maxCount}|v2`;
          if (__windowRectCache[key]) return __windowRectCache[key];
          const rects = detectWhiteWindowsFromFrame(frameImg, maxCount);
          __windowRectCache[key] = rects;
          return rects;
        }

        function __pickBestThreeWindowRects(rects) {
          if (!Array.isArray(rects) || rects.length < 3) return rects;
          // Prefer the 3 biggest windows by area; this filters out tiny artifacts.
          const sorted = rects
            .map((r) => ({ r, a: Math.max(0, (r?.w || 0) * (r?.h || 0)) }))
            .sort((a, b) => b.a - a.a);
          return sorted.slice(0, 3).map((x) => x.r);
        }

        function windowRectsToCells(rects, canvasWidth, canvasHeight, layoutKey) {
          const cells = [];
          if (!Array.isArray(rects) || !rects.length) return cells;

          // layout3 may have variant frames:
          // - 6 windows (2x3) => 6 photos
          // - 3 windows (3x1 or 1x3) => 3 photos
          // Auto-detect orientation by comparing spread in X vs Y.
          if (layoutKey === 'layout3' && rects.length >= 3 && rects.length < 6) {
            try {
              const best3 = __pickBestThreeWindowRects(rects);
              const det = best3.slice(0, 3).map((r, idx) => ({
                idx,
                r,
                cx: (r.x + r.w / 2),
                cy: (r.y + r.h / 2),
              }));
              const xs = det.map(d => d.cx);
              const ys = det.map(d => d.cy);
              const rangeX = (Math.max(...xs) - Math.min(...xs));
              const rangeY = (Math.max(...ys) - Math.min(...ys));
              const vertical = rangeY >= rangeX;
              det.sort((a, b) => vertical ? (a.cy - b.cy) : (a.cx - b.cx));
              for (let slot = 0; slot < det.length; slot++) {
                const rr = det[slot].r;
                cells.push({
                  x: rr.x * canvasWidth,
                  y: rr.y * canvasHeight,
                  w: rr.w * canvasWidth,
                  h: rr.h * canvasHeight,
                  r: typeof rr.r === 'number' ? rr.r : 0,
                  photoIndex: slot,
                });
              }
              return cells;
            } catch (e) {
              // fall back below
            }
          }

          // For layout3 we expect 3 rows × 2 columns.
          // Robust mapping: match detected rects to the expected grid slots by nearest center.
          // This prevents cases where detection order interleaves rows (making row1/row2 share the same photo).
          if (layoutKey === 'layout3' && rects.length >= 6) {
            try {
              const expected = getCells('layout3', canvasWidth, canvasHeight);
              if (Array.isArray(expected) && expected.length >= 6) {
                const exp = expected.slice(0, 6).map((c, idx) => ({
                  idx,
                  cx: (c.x + c.w / 2),
                  cy: (c.y + c.h / 2),
                }));
                const det = rects.slice(0, 6).map((r, di) => ({
                  di,
                  r,
                  cx: (r.x + r.w / 2) * canvasWidth,
                  cy: (r.y + r.h / 2) * canvasHeight,
                }));

                // Build all pairs and greedy-match by shortest distance (good enough for 6 items).
                const pairs = [];
                for (let i = 0; i < det.length; i++) {
                  for (let j = 0; j < exp.length; j++) {
                    const dx = det[i].cx - exp[j].cx;
                    const dy = det[i].cy - exp[j].cy;
                    pairs.push({ di: det[i].di, ei: exp[j].idx, d: dx * dx + dy * dy });
                  }
                }
                pairs.sort((a, b) => a.d - b.d);
                const usedDet = new Set();
                const usedExp = new Set();
                const assign = new Map(); // expectedIdx -> rect
                for (const p of pairs) {
                  if (usedDet.has(p.di) || usedExp.has(p.ei)) continue;
                  usedDet.add(p.di);
                  usedExp.add(p.ei);
                  assign.set(p.ei, rects[p.di]);
                  if (assign.size >= 6) break;
                }

                if (assign.size >= 6) {
                  for (let slot = 0; slot < 6; slot++) {
                    const rr = assign.get(slot);
                    if (!rr) continue;
                    cells.push({
                      x: rr.x * canvasWidth,
                      y: rr.y * canvasHeight,
                      w: rr.w * canvasWidth,
                      h: rr.h * canvasHeight,
                      r: typeof rr.r === 'number' ? rr.r : 0,
                      photoIndex: slot,
                    });
                  }
                  return cells;
                }
              }
            } catch (e) {
              // fall back below
            }
          }

          for (let i = 0; i < rects.length; i++) {
            const r = rects[i];
            cells.push({
              x: r.x * canvasWidth,
              y: r.y * canvasHeight,
              w: r.w * canvasWidth,
              h: r.h * canvasHeight,
              r: typeof r.r === 'number' ? r.r : 0,
              photoIndex: i,
            });
          }
          return cells;
        }

        function drawFrameOverlayWithHoles(ctx, frameImg, canvasWidth, canvasHeight, rects) {
          if (!frameImg) return;
          const off = document.createElement('canvas');
          off.width = canvasWidth;
          off.height = canvasHeight;
          const octx = off.getContext('2d');

          // Draw frame
          octx.clearRect(0, 0, canvasWidth, canvasHeight);
          octx.drawImage(frameImg, 0, 0, canvasWidth, canvasHeight);

          // Punch holes (slightly expanded) so no white edge remains
          const bleed = Math.max(1, Math.round(Math.min(canvasWidth, canvasHeight) * 0.002));
          octx.save();
          octx.globalCompositeOperation = 'destination-out';
          octx.fillStyle = '#000';
          (rects || []).forEach((r) => {
            const x = r.x * canvasWidth;
            const y = r.y * canvasHeight;
            const w = r.w * canvasWidth;
            const h = r.h * canvasHeight;
            const ex = x - bleed;
            const ey = y - bleed;
            const ew = w + bleed * 2;
            const eh = h + bleed * 2;
            const radius = (typeof r.r === 'number' ? r.r : 0) * Math.min(ew, eh);
            __roundedRectPath(octx, ex, ey, ew, eh, radius + bleed);
            octx.fill();
          });
          octx.restore();

          ctx.drawImage(off, 0, 0);
        }


        let __pbPreviewHitCells = [];
        let __pbRenderToken = 0;
        let __pbRenderRaf = 0;

        function scheduleRenderPreview() {
          __pbRenderToken++;
          if (__pbRenderRaf) return;
          __pbRenderRaf = requestAnimationFrame(() => {
            __pbRenderRaf = 0;
            renderPreview(__pbRenderToken);
          });
        }

        async function renderPreview(token) {
          try {
            const myToken = (typeof token === 'number' && token > 0) ? token : (++__pbRenderToken);
            const canvas = document.getElementById('previewCanvas');
            const ctx = canvas.getContext('2d');

            // Apply global view zoom around canvas center
            const viewScale = __clamp(previewViewScale, 0.5, 2.5);
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;

            // frame mode:
            // - JPG/JPEG always background
            // - PNG/WEBP opaque => background, alpha => overlay
            let frameImg = null;
            let frameMode = null;
            let windowRects = null;
            let __layout2DetectedWant4 = false;
            if (previewFrameUrl) {
              try {
                frameImg = await __getFrameImg(previewFrameUrl);
                if (myToken !== __pbRenderToken) return;
                if (!frameImg) throw new Error('frame failed');
                if (isBackgroundFrame(previewFrameUrl)) frameMode = 'background';
                else frameMode = estimateHasAlpha(frameImg) ? 'overlay' : 'background';

                // Detect frame windows (holes). For layout2 we want to auto-detect 2×2 frames
                // even if the session is still in 2-photo mode.
                if (previewLayoutKey === 'layout2') {
                  const rects4 = getWindowRects(previewFrameUrl, frameImg, 4);
                  if (Array.isArray(rects4) && rects4.length >= 4) {
                    windowRects = rects4;
                    frameMode = 'holes';
                    __layout2DetectedWant4 = true;
                  } else {
                    const rects2 = getWindowRects(previewFrameUrl, frameImg, 2);
                    if (Array.isArray(rects2) && rects2.length >= 2) {
                      windowRects = rects2;
                      frameMode = 'holes';
                    }
                  }
                } else {
                  const expected = (previewLayoutKey === 'layout3') ? 6 : 0;
                  if (expected > 0) {
                    // For layout3, accept variant frames with only 3 windows (3x1 / 1x3)
                    windowRects = getWindowRects(previewFrameUrl, frameImg, expected);
                    if (previewLayoutKey === 'layout3' && Array.isArray(windowRects) && windowRects.length >= 3 && windowRects.length < 6) {
                      windowRects = __pickBestThreeWindowRects(windowRects);
                    }
                    const minOk = 3;
                    if (Array.isArray(windowRects) && windowRects.length >= minOk) {
                      frameMode = 'holes';
                    }
                  }
                }

              } catch (e) {
                frameImg = null;
                frameMode = null;
              }
            }

            // UI hint: if user selected a 2×2 (4-hole) layout2 frame but capture mode is still 2 photos.
            try {
              const hint = document.getElementById('pbLayoutHint');
              const isLayout2 = (previewLayoutKey === 'layout2');
              const in2Mode = (typeof previewLayoutCount === 'number' && previewLayoutCount > 0 && previewLayoutCount < 4);
              if (hint && isLayout2 && in2Mode && __layout2DetectedWant4) hint.style.display = 'block';
              else if (hint) hint.style.display = 'none';
            } catch (e) {
              // ignore
            }

            const cells = ((previewLayoutKey === 'layout3' || previewLayoutKey === 'layout2') && Array.isArray(windowRects) && windowRects.length)
              ? windowRectsToCells(windowRects, canvas.width, canvas.height, previewLayoutKey)
              : getCells(previewLayoutKey, canvas.width, canvas.height);
            const indices = Array.from(new Set(cells.map((c, idx) => (typeof c.photoIndex === 'number' ? c.photoIndex : idx)).filter((i) => i >= 0))).sort((a,b)=>a-b);
            const maxIndex = indices.length ? Math.max(...indices) : -1;
            const needPhotos = Math.min(previewPhotos.length, maxIndex + 1);
            const imgs = await Promise.all(previewPhotos.slice(0, needPhotos).map(__getPhotoImg));
            if (myToken !== __pbRenderToken) return;

            // preload sticker images used by visible slots
            try {
              const usedStickerIds = new Set();
              for (let i = 0; i < cells.length; i++) {
                const cell = cells[i];
                const slotIdx = (typeof cell.photoIndex === 'number') ? cell.photoIndex : i;
                const st = getSlotSticker(slotIdx);
                if (st && typeof st.id === 'string') usedStickerIds.add(st.id);
              }
              await Promise.all(Array.from(usedStickerIds).map(__getStickerImg));
            } catch (e) {
              // ignore
            }

            if (myToken !== __pbRenderToken) return;

            // Draw only after all required assets are ready (prevents flicker).
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.filter = 'none';
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#00000000';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.save();
            ctx.translate(cx, cy);
            ctx.scale(viewScale, viewScale);
            ctx.translate(-cx, -cy);

            if (previewFrameUrl && frameImg && frameMode === 'background') {
              ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);
            }

            const hitCells = [];

            for (let i = 0; i < cells.length; i++) {
              const cell = cells[i];
              const cellIdx = i;
              const photoIdx = typeof cell.photoIndex === 'number' ? cell.photoIndex : i;
              const slotIdx = photoIdx;
              if (photoIdx >= 0 && photoIdx < imgs.length && imgs[photoIdx]) {
                const photoSrc = previewPhotos[photoIdx];
                const rotateDeg = getPhotoRotateDeg(photoSrc);
                const userScale = getPhotoUserScale(photoSrc);
                const userPan = getPhotoUserPan(photoSrc);
                const filterCss = getPhotoFilterCss(photoSrc);
                const sticker = getSlotSticker(slotIdx);
                let rx = cell.x, ry = cell.y, rw = cell.w, rh = cell.h;
                // Special sizing adjustment for layout2 2-photo mode only.
                const layout2Want4 = (previewLayoutKey === 'layout2' && typeof previewLayoutCount === 'number' && previewLayoutCount >= 4);
                if (previewLayoutKey === 'layout2' && !layout2Want4 && typeof cell.row === 'number') {
                  const cfg = previewFrameConfig || {};
                  const anchorRatio = (typeof cfg.row_anchor_ratio === 'number') ? cfg.row_anchor_ratio : 1.000;
                  const ratio = Math.max(0, Math.min(1, anchorRatio));

                  // Use a square slot and pull rows toward the center to reduce the middle band.
                  const size = Math.min(cell.w, cell.h);
                  const dx = (cell.w - size) / 2;
                  const dy = (cell.h - size);
                  const y = (cell.row === 0)
                    ? (cell.y + dy * (0.5 + 0.5 * ratio))
                    : (cell.y + dy * (0.5 - 0.5 * ratio));
                  rx = cell.x + dx;
                  ry = y;
                  rw = size;
                  rh = size;
                }

                ctx.save();
                ctx.filter = filterCss || 'none';
                if (previewLayoutKey === 'layout1') {
                  drawCover(ctx, imgs[photoIdx], rx, ry, rw, rh, rotateDeg, userScale, userPan);
                } else if ((previewLayoutKey === 'layout3' || previewLayoutKey === 'layout2') && Array.isArray(windowRects) && windowRects.length) {
                  // For detected frame windows, draw photo as CONTAIN (fit inside slot) to avoid cropping.
                  const radius = (typeof cell.r === 'number' ? cell.r : 0) * Math.min(rw, rh);
                  ctx.save();
                  __roundedRectPath(ctx, rx, ry, rw, rh, radius);
                  ctx.clip();
                  // drawContain preserves whole photo (letterbox inside slot) and respects rotation
                  drawContain(ctx, imgs[photoIdx], rx, ry, rw, rh, rotateDeg, userScale, userPan);
                  ctx.restore();
                } else {
                  drawPhotoWithBorder(ctx, imgs[photoIdx], rx, ry, rw, rh, rotateDeg, userScale, userPan);
                }
                ctx.restore();

                if (sticker && typeof sticker.id === 'string') {
                  const stickerImg = __PB_STICKER_IMG_CACHE.get(sticker.id);
                  if (stickerImg) {
                    __drawSticker(ctx, stickerImg, rx, ry, rw, rh, sticker);
                  }
                }

                hitCells.push({ x: rx, y: ry, w: rw, h: rh, cellIndex: cellIdx, slotIndex: slotIdx, photoIndex: photoIdx, photoSrc: photoSrc, r: (typeof cell.r === 'number' ? cell.r : 0) });
              }
            }

            // Highlight selected slot (preferred) or selected photo key (fallback)
            if (previewSelectedCellIndex !== null || previewSelectedPhotoKey) {
              const selected = (previewSelectedCellIndex !== null)
                ? hitCells.filter((c) => c && typeof c.cellIndex === 'number' && c.cellIndex === previewSelectedCellIndex)
                : hitCells.filter((c) => normalizePhotoKeyForZoom(c.photoSrc) === previewSelectedPhotoKey);
              if (selected.length) {
                ctx.save();
                ctx.strokeStyle = 'rgba(249,115,22,0.95)';
                ctx.lineWidth = Math.max(3, Math.round(Math.min(canvas.width, canvas.height) * 0.006));
                ctx.shadowColor = 'rgba(249,115,22,0.45)';
                ctx.shadowBlur = 18;
                selected.forEach((c) => {
                  const radius = (typeof c.r === 'number' ? c.r : 0) * Math.min(c.w, c.h);
                  __roundedRectPath(ctx, c.x, c.y, c.w, c.h, radius);
                  ctx.stroke();
                });
                ctx.restore();
              }
            }

            __pbPreviewHitCells = hitCells;

            if (previewFrameUrl && frameImg) {
              if (frameMode === 'overlay') {
                ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);
              }
              if (frameMode === 'holes' && Array.isArray(windowRects) && windowRects.length) {
                drawFrameOverlayWithHoles(ctx, frameImg, canvas.width, canvas.height, windowRects);
              }
            }

            ctx.restore();
          } catch (e) {
            console.error('preview error', e);
          }
        }

        (() => {
          __loadFilterStore();
          __loadStickerStore();
          if (window.PB_ROTATE && typeof window.PB_ROTATE.init === 'function') {
            window.PB_ROTATE.init(document);
          }
          window.addEventListener('pb:rotate-changed', () => {
            scheduleRenderPreview();
          });
          scheduleRenderPreview();

          const nextBtn = document.getElementById('nextBtn');
          if (nextBtn) {
            nextBtn.addEventListener('click', () => {
              // keep default navigation
            });
          }

          // Zoom controls
          const zoomOutBtn = document.getElementById('zoomOutBtn');
          const zoomInBtn = document.getElementById('zoomInBtn');
          const resetZoomBtn = document.getElementById('resetZoomBtn');
          const zoomPct = document.getElementById('zoomPct');
          const zoomIndicator = document.getElementById('zoomIndicator');
          const swapModeBtn = document.getElementById('swapModeBtn');
          const retakeForm = document.getElementById('retakeForm');
          const filterSelect = document.getElementById('filterSelect');
          const stickerRemoveBtn = document.getElementById('stickerRemoveBtn');
          const stickerSizeDownBtn = document.getElementById('stickerSizeDownBtn');
          const stickerSizeUpBtn = document.getElementById('stickerSizeUpBtn');

          // Global (view) zoom controls
          const globalZoomOutBtn = document.getElementById('globalZoomOutBtn');
          const globalZoomInBtn = document.getElementById('globalZoomInBtn');
          const globalResetZoomBtn = document.getElementById('globalResetZoomBtn');
          const globalZoomPct = document.getElementById('globalZoomPct');
          const globalZoomIndicator = document.getElementById('globalZoomIndicator');

          let swapMode = false;

          // Sticker drag state
          let __pbDraggingSticker = false;
          let __pbDragStickerSlotIndex = null;
          let __pbDragStickerSlot = null; // {rx,ry,rw,rh}
          let __pbDragStickerOffset = null; // {dxNorm, dyNorm}
          let __pbDragMoved = false;

          function __getCanvasPoint(previewCanvas, ev) {
            const rect = previewCanvas.getBoundingClientRect();
            const x0 = (ev.clientX - rect.left) * (previewCanvas.width / rect.width);
            const y0 = (ev.clientY - rect.top) * (previewCanvas.height / rect.height);
            const s = __clamp(previewViewScale, 0.5, 2.5);
            const cx = previewCanvas.width / 2;
            const cy = previewCanvas.height / 2;
            const x = (x0 - cx) / s + cx;
            const y = (y0 - cy) / s + cy;
            return { x, y };
          }

          function __findHitCellAtPoint(x, y) {
            return (__pbPreviewHitCells || []).find((c) => x >= c.x && x <= (c.x + c.w) && y >= c.y && y <= (c.y + c.h)) || null;
          }

          function __hitTestSticker(hitCell, x, y) {
            if (!hitCell || typeof hitCell.slotIndex !== 'number') return null;
            const st = getSlotSticker(hitCell.slotIndex);
            if (!st) return null;
            const rx = hitCell.x, ry = hitCell.y, rw = hitCell.w, rh = hitCell.h;
            const size = Math.max(8, Math.round(Math.min(rw, rh) * (st.s || 0.35)));
            const cx = rx + rw * (st.x ?? 0.5);
            const cy = ry + rh * (st.y ?? 0.5);
            const half = size / 2;
            const inside = (x >= (cx - half) && x <= (cx + half) && y >= (cy - half) && y <= (cy + half));
            if (!inside) return null;
            const dxNorm = (cx - x) / rw;
            const dyNorm = (cy - y) / rh;
            return { sticker: st, slot: { rx, ry, rw, rh }, offset: { dxNorm, dyNorm } };
          }

          function basenameFromUrl(u) {
            if (!u) return '';
            try {
              const clean = String(u).split('#')[0].split('?')[0];
              const parts = clean.split('/');
              return parts[parts.length - 1] || '';
            } catch (e) {
              return '';
            }
          }

          function setSwapMode(on) {
            swapMode = !!on;
            if (!swapModeBtn) return;
            if (!previewSelectedPhotoSrc) swapMode = false;
            swapModeBtn.disabled = !previewSelectedPhotoSrc;
            swapModeBtn.textContent = swapMode ? 'MOVE: PILIH SLOT' : 'MOVE';
            swapModeBtn.style.background = swapMode
              ? 'linear-gradient(135deg, rgba(249,115,22,0.95), rgba(234,88,12,0.95))'
              : '';
            const previewCanvas = document.getElementById('previewCanvas');
            if (previewCanvas) previewCanvas.style.cursor = swapMode ? 'crosshair' : 'default';
          }

          async function persistCurrentOrder() {
            try {
              if (!Array.isArray(previewPhotos)) return;
              const order = previewPhotos.map((src) => basenameFromUrl(src)).filter(Boolean);
              if (!order.length) return;

              const tokenEl = document.querySelector('meta[name="csrf-token"]');
              const token = tokenEl ? tokenEl.getAttribute('content') : '';
              await fetch({!! json_encode(route('photo.order')) !!}, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                  'Accept': 'application/json',
                },
                body: JSON.stringify({ order }),
              });
            } catch (e) {
              // ignore
            }
          }

          // ensure initial selected key
          if (previewSelectedPhotoSrc) setSelectedPhoto(previewSelectedPhotoSrc);

          function updateSelectedThumbnailUI() {
            const selectedKey = previewSelectedPhotoKey;
            const thumbs = document.querySelectorAll('img.rotatable-photo[data-rotate-src]');
            thumbs.forEach((img) => {
              const key = normalizePhotoKeyForZoom(img.getAttribute('data-rotate-src'));
              if (selectedKey && key === selectedKey) {
                img.style.outline = '3px solid rgba(249,115,22,0.95)';
                img.style.outlineOffset = '3px';
                img.style.boxShadow = '0 0 0 4px rgba(0,0,0,0.25), 0 12px 30px rgba(249,115,22,0.22)';
              } else {
                img.style.outline = '';
                img.style.outlineOffset = '';
                img.style.boxShadow = '';
              }
            });
          }

          function applyFiltersToThumbnails() {
            const thumbs = document.querySelectorAll('img.rotatable-photo[data-rotate-src]');
            thumbs.forEach((img) => {
              const src = img.getAttribute('data-rotate-src');
              img.style.filter = getPhotoFilterCss(src);
            });
          }

          function refreshFilterUI() {
            if (!filterSelect) return;
            const id = getPhotoFilterId(previewSelectedPhotoSrc);
            filterSelect.value = (__PB_FILTER_PRESETS[id] ? id : 'none');
          }

          function __ensureStickerOnSelected(defaultId) {
            if (typeof previewSelectedPhotoIndex !== 'number' || !isFinite(previewSelectedPhotoIndex) || previewSelectedPhotoIndex < 0) return null;
            const cur = getSlotSticker(previewSelectedPhotoIndex);
            if (cur) return cur;
            const id = (typeof defaultId === 'string' && __PB_STICKER_IDS.includes(defaultId)) ? defaultId : 'star';
            const next = { id, x: 0.5, y: 0.5, s: 0.35 };
            setSlotSticker(previewSelectedPhotoIndex, next);
            return next;
          }

          function __nudgeSticker(dxNorm, dyNorm) {
            if (typeof previewSelectedPhotoIndex !== 'number' || !isFinite(previewSelectedPhotoIndex) || previewSelectedPhotoIndex < 0) return;
            const st = getSlotSticker(previewSelectedPhotoIndex);
            if (!st) return;
            const step = 0.035;
            const next = { ...st, x: __clamp(st.x + dxNorm * step, 0, 1), y: __clamp(st.y + dyNorm * step, 0, 1) };
            setSlotSticker(previewSelectedPhotoIndex, next);
            scheduleRenderPreview();
          }

          function __scaleSticker(dir) {
            if (typeof previewSelectedPhotoIndex !== 'number' || !isFinite(previewSelectedPhotoIndex) || previewSelectedPhotoIndex < 0) return;
            const st = getSlotSticker(previewSelectedPhotoIndex);
            if (!st) return;
            const step = 0.05;
            const next = { ...st, s: __clamp(st.s + (dir * step), 0.10, 0.90) };
            setSlotSticker(previewSelectedPhotoIndex, next);
            scheduleRenderPreview();
          }

          function normalizeBasename(src) {
            if (!src) return '';
            try {
              const clean = String(src).split('#')[0].split('?')[0];
              const parts = clean.split('/');
              return (parts[parts.length - 1] || '').toLowerCase();
            } catch (e) {
              return '';
            }
          }

          function syncRetakeCheckboxSingle(photoSrc) {
            const targetBase = normalizeBasename(photoSrc);
            if (!targetBase) return;
            const items = document.querySelectorAll('.photo-item');
            let matched = false;
            items.forEach((item) => {
              const img = item.querySelector('img.rotatable-photo[data-rotate-src]');
              const cb = item.querySelector('input.checkbox-limit');
              if (!cb || !img) return;
              const base = normalizeBasename(img.getAttribute('data-rotate-src'));
              const isTarget = base === targetBase;
              cb.checked = isTarget;
              if (isTarget) matched = true;
            });

            // If not found by thumbnail src, try matching by checkbox value basename.
            if (!matched) {
              const cbs = document.querySelectorAll('input.checkbox-limit');
              cbs.forEach((cb) => {
                const base = normalizeBasename(cb.value || cb.id);
                cb.checked = base === targetBase;
              });
            }
          }

          function __updateZoomIndicator(indicatorEl, min, max, scale) {
            if (!indicatorEl) return;
            const clamped = __clamp(scale, min, max);
            const pct = ((clamped - min) / (max - min)) * 100;
            indicatorEl.style.width = Math.max(0, Math.min(100, pct)) + '%';
            if (indicatorEl.parentElement) {
              indicatorEl.parentElement.setAttribute('aria-valuenow', String(Math.round(clamped * 100)));
            }
            indicatorEl.setAttribute('title', Math.round(clamped * 100) + '%');
          }

          function refreshZoomLabel() {
            if (!zoomPct) return;
            const scale = getPhotoUserScale(previewSelectedPhotoSrc);
            zoomPct.textContent = Math.round(scale * 100) + '%';
            __updateZoomIndicator(zoomIndicator, 0.2, 3.0, scale);
          }

          function refreshGlobalZoomLabel() {
            if (!globalZoomPct) return;
            const scale = __clamp(previewViewScale, 0.5, 2.5);
            globalZoomPct.textContent = Math.round(scale * 100) + '%';
            __updateZoomIndicator(globalZoomIndicator, 0.5, 2.5, scale);
          }

          function applyGlobalZoom(action) {
            if (action === 'in') previewViewScale = __clamp(previewViewScale + 0.1, 0.5, 2.5);
            if (action === 'out') previewViewScale = __clamp(previewViewScale - 0.1, 0.5, 2.5);
            if (action === 'reset') previewViewScale = 1.0;
            refreshGlobalZoomLabel();
            scheduleRenderPreview();
          }

          function applySelectedZoom(action) {
            if (!previewSelectedPhotoSrc) return;
            const cur = getPhotoUserScale(previewSelectedPhotoSrc);
            let next = cur;
            if (action === 'in') next = cur + 0.1;
            if (action === 'out') next = cur - 0.1;
            if (action === 'reset') {
              next = 1.0;
              resetPhotoUserPan(previewSelectedPhotoSrc);
            }
            setPhotoUserScale(previewSelectedPhotoSrc, next);
            refreshZoomLabel();
            scheduleRenderPreview();
          }

          if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => applySelectedZoom('out'));
          if (zoomInBtn) zoomInBtn.addEventListener('click', () => applySelectedZoom('in'));
          if (resetZoomBtn) resetZoomBtn.addEventListener('click', () => applySelectedZoom('reset'));
          refreshZoomLabel();
          updateSelectedThumbnailUI();
          refreshFilterUI();
          applyFiltersToThumbnails();
          setSwapMode(false);

          if (globalZoomOutBtn) globalZoomOutBtn.addEventListener('click', () => applyGlobalZoom('out'));
          if (globalZoomInBtn) globalZoomInBtn.addEventListener('click', () => applyGlobalZoom('in'));
          if (globalResetZoomBtn) globalResetZoomBtn.addEventListener('click', () => applyGlobalZoom('reset'));
          refreshGlobalZoomLabel();

          if (swapModeBtn) {
            swapModeBtn.addEventListener('click', () => {
              if (!previewSelectedPhotoSrc) return;
              setSwapMode(!swapMode);
            });
          }

          if (filterSelect) {
            filterSelect.addEventListener('change', () => {
              if (!previewSelectedPhotoSrc) return;
              setPhotoFilterId(previewSelectedPhotoSrc, filterSelect.value);
              applyFiltersToThumbnails();
              scheduleRenderPreview();
            });
          }

          // Sticker picker
          document.addEventListener('click', async (ev) => {
            const btn = ev.target && ev.target.closest ? ev.target.closest('button.sticker-btn[data-sticker-id]') : null;
            if (!btn) return;
            const id = btn.getAttribute('data-sticker-id');
            if (typeof previewSelectedPhotoIndex !== 'number' || !id) return;
            const st = __ensureStickerOnSelected(id);
            if (st) {
              setSlotSticker(previewSelectedPhotoIndex, { ...st, id });
              await __getStickerImg(id);
              scheduleRenderPreview();
            }
          });

          if (stickerRemoveBtn) {
            stickerRemoveBtn.addEventListener('click', () => {
              if (typeof previewSelectedPhotoIndex !== 'number' || !isFinite(previewSelectedPhotoIndex) || previewSelectedPhotoIndex < 0) return;
              setSlotSticker(previewSelectedPhotoIndex, null);
              scheduleRenderPreview();
            });
          }
          if (stickerSizeDownBtn) stickerSizeDownBtn.addEventListener('click', () => __scaleSticker(-1));
          if (stickerSizeUpBtn) stickerSizeUpBtn.addEventListener('click', () => __scaleSticker(+1));

          // Preview interactions:
          // - pointerdown on sticker => drag sticker (when not in MOVE swap mode)
          // - pointerup without drag => select photo / swap slot
          const previewCanvas = document.getElementById('previewCanvas');
          if (previewCanvas) {
            previewCanvas.addEventListener('pointerdown', (ev) => {
              try {
                if (ev.button !== undefined && ev.button !== 0) return; // left click only
                const { x, y } = __getCanvasPoint(previewCanvas, ev);
                const hit = __findHitCellAtPoint(x, y);
                __pbDragMoved = false;

                // Sticker drag (disabled while swapMode is active)
                if (!swapMode && hit && hit.photoSrc) {
                  const stHit = __hitTestSticker(hit, x, y);
                  if (stHit) {
                    __pbDraggingSticker = true;
                    __pbDragStickerSlotIndex = hit.slotIndex;
                    __pbDragStickerSlot = stHit.slot;
                    __pbDragStickerOffset = stHit.offset;
                    previewCanvas.setPointerCapture(ev.pointerId);
                    previewCanvas.style.cursor = 'grabbing';
                    ev.preventDefault();
                    return;
                  }
                }
              } catch (e) {
                // ignore
              }
            }, { passive: false });

            previewCanvas.addEventListener('pointermove', (ev) => {
              try {
                const { x, y } = __getCanvasPoint(previewCanvas, ev);

                if (__pbDraggingSticker && typeof __pbDragStickerSlotIndex === 'number' && __pbDragStickerSlot && __pbDragStickerOffset) {
                  const { rw, rh } = __pbDragStickerSlot;
                  const dxNorm = __pbDragStickerOffset.dxNorm || 0;
                  const dyNorm = __pbDragStickerOffset.dyNorm || 0;
                  const nextX = __clamp((x - __pbDragStickerSlot.rx) / rw + dxNorm, 0, 1);
                  const nextY = __clamp((y - __pbDragStickerSlot.ry) / rh + dyNorm, 0, 1);
                  const cur = getSlotSticker(__pbDragStickerSlotIndex);
                  if (cur) {
                    setSlotSticker(__pbDragStickerSlotIndex, { ...cur, x: nextX, y: nextY }, { persist: false });
                    __pbDragMoved = true;
                    scheduleRenderPreview();
                  }
                  ev.preventDefault();
                  return;
                }

                // Hover cursor feedback (optional)
                if (!swapMode) {
                  const hit = __findHitCellAtPoint(x, y);
                  if (hit && hit.photoSrc) {
                    const stHit = __hitTestSticker(hit, x, y);
                    previewCanvas.style.cursor = stHit ? 'grab' : (swapMode ? 'crosshair' : 'default');
                  } else {
                    previewCanvas.style.cursor = swapMode ? 'crosshair' : 'default';
                  }
                }
              } catch (e) {
                // ignore
              }
            }, { passive: false });

            previewCanvas.addEventListener('pointerup', (ev) => {
              try {
                if (__pbDraggingSticker) {
                  // Persist sticker position once on release.
                  if (typeof __pbDragStickerSlotIndex === 'number' && __pbDragMoved) {
                    const cur = getSlotSticker(__pbDragStickerSlotIndex);
                    if (cur) setSlotSticker(__pbDragStickerSlotIndex, cur, { persist: true });
                  }
                  __pbDraggingSticker = false;
                  __pbDragStickerSlotIndex = null;
                  __pbDragStickerSlot = null;
                  __pbDragStickerOffset = null;
                  previewCanvas.style.cursor = swapMode ? 'crosshair' : 'default';
                  ev.preventDefault();
                  return;
                }

                // Treat as click (selection / swap)
                const { x, y } = __getCanvasPoint(previewCanvas, ev);
                const hit = __findHitCellAtPoint(x, y);
                if (hit && hit.photoSrc) {
                  if (swapMode && Array.isArray(previewPhotos)) {
                    const toSlot = (typeof hit.slotIndex === 'number') ? hit.slotIndex : null;
                    const fromIdx = (typeof previewSelectedPhotoIndex === 'number')
                      ? previewSelectedPhotoIndex
                      : previewPhotos.findIndex((src) => normalizePhotoKeyForZoom(src) === previewSelectedPhotoKey);
                    if (toSlot !== null && fromIdx >= 0 && fromIdx !== toSlot && toSlot >= 0 && toSlot < previewPhotos.length) {
                      const tmp = previewPhotos[toSlot];
                      previewPhotos[toSlot] = previewPhotos[fromIdx];
                      previewPhotos[fromIdx] = tmp;
                      persistCurrentOrder();
                      previewSelectedPhotoIndex = toSlot;
                    }
                    setSwapMode(false);
                    // Update selection to the photo now in the clicked slot.
                    if (toSlot !== null && previewPhotos[toSlot]) {
                      setSelectedPhoto(previewPhotos[toSlot], { cellIndex: (typeof hit.cellIndex === 'number' ? hit.cellIndex : null), photoIndex: toSlot });
                      refreshZoomLabel();
                      updateSelectedThumbnailUI();
                      refreshFilterUI();
                    }
                    scheduleRenderPreview();
                    return;
                  }
                  setSelectedPhoto(hit.photoSrc, { cellIndex: hit.cellIndex, photoIndex: hit.slotIndex });
                  refreshZoomLabel();
                  updateSelectedThumbnailUI();
                  refreshFilterUI();
                  syncRetakeCheckboxSingle(hit.photoSrc);
                  setSwapMode(false);
                  scheduleRenderPreview();
                }
              } catch (e) {
                // ignore
              }
            }, { passive: false });

            previewCanvas.addEventListener('pointercancel', () => {
              __pbDraggingSticker = false;
              __pbDragStickerSlotIndex = null;
              __pbDragStickerSlot = null;
              __pbDragStickerOffset = null;
              previewCanvas.style.cursor = swapMode ? 'crosshair' : 'default';
            });
          }

          // Click thumbnail to select photo
          document.addEventListener('click', (ev) => {
            const img = ev.target.closest('img.rotatable-photo[data-rotate-src]');
            if (!img) return;
            const src = img.getAttribute('data-rotate-src');
            if (!src) return;
            setSelectedPhoto(src);
            refreshZoomLabel();
            updateSelectedThumbnailUI();
            refreshFilterUI();
            syncRetakeCheckboxSingle(src);
            setSwapMode(false);
            scheduleRenderPreview();
          });

          // Select photo when clicking checkbox too
          document.addEventListener('change', (ev) => {
            const cb = ev.target && ev.target.matches ? (ev.target.matches('input.checkbox-limit') ? ev.target : null) : null;
            if (!cb) return;
            const wrapper = cb.closest('.photo-item');
            const img = wrapper ? wrapper.querySelector('img.rotatable-photo[data-rotate-src]') : null;
            const src = img ? img.getAttribute('data-rotate-src') : null;
            if (!src) return;
            // Force single selection for retake: the checkbox you touched becomes the only one checked.
            syncRetakeCheckboxSingle(src);
            setSelectedPhoto(src);
            refreshZoomLabel();
            updateSelectedThumbnailUI();
            refreshFilterUI();
            setSwapMode(false);
            scheduleRenderPreview();
          });

          // Ensure retake submits exactly what is selected.
          if (retakeForm) {
            retakeForm.addEventListener('submit', () => {
              const checked = retakeForm.querySelectorAll('input.checkbox-limit:checked');
              if (checked.length === 0 && previewSelectedPhotoSrc) {
                syncRetakeCheckboxSingle(previewSelectedPhotoSrc);
              }
            });
          }

        })();
      </script>
    @elseif(Request::routeIs('print-photo'))
      <form action="{{ route('print') }}" method="post">
        @csrf
        @foreach(collect($photos)->chunk(5) as $photoschunk)
          <div class="photo-group">
            @foreach($photoschunk as $photo)
              <div class="photo-item">
                <input type="checkbox" name="photos[]" class="checkbox-limit" id="{{ $photo }}" value="{{ $photo }}">
                @php $photoSrc = asset(str_replace('public', 'storage', $photo)); @endphp
                <label for="{{ $photo }}"><img class="rotatable-photo" data-rotate-src="{{ $photoSrc }}" src="{{ $photoSrc }}" alt=""></label>
                <button type="button" class="photo-rotate-btn formbutton" data-photo-src="{{ $photoSrc }}" style="margin-top:8px; padding:6px 10px; border-radius: 12px; min-width: unset;">ROTATE</button>
              </div>
            @endforeach
          </div>
        @endforeach
        <div class="formgroup printphoto-actions">
          <a href="{{ route('list-photo') }}" class="formbutton">
            BACK
          </a>
          <button type="submit" class="formbutton">
            PRINT
          </button>
        </div>
      </form>

      <script src="{{ asset('js/rotate.js') }}"></script>
      <script>
        (function () {
          if (window.PB_ROTATE && typeof window.PB_ROTATE.init === 'function') {
            window.PB_ROTATE.init(document);
          }
        })();
      </script>
    @endif
  </section>
</section>

@if(Request::routeIs('print-photo'))
  <script>
    window.maxChecked = {{ (int) ($limit ?? 3) }};
  </script>
@endif
@endsection
