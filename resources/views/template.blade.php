@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('tempcollage.index', 'img/pilihanlayout.jpg') }}');">
  @php
    $coffeeDrops = [
      ['left' => '6%',  'delay' => '0s',   'dur' => '7.8s', 'size' => '18px', 'op' => '0.22'],
      ['left' => '14%', 'delay' => '-2s',  'dur' => '9.2s', 'size' => '22px', 'op' => '0.28'],
      ['left' => '22%', 'delay' => '-5s',  'dur' => '8.6s', 'size' => '20px', 'op' => '0.20'],
      ['left' => '30%', 'delay' => '-1s',  'dur' => '10.4s','size' => '26px', 'op' => '0.18'],
      ['left' => '38%', 'delay' => '-3.5s','dur' => '8.9s', 'size' => '19px', 'op' => '0.24'],
      ['left' => '46%', 'delay' => '-6s',  'dur' => '11.0s','size' => '28px', 'op' => '0.16'],
      ['left' => '54%', 'delay' => '-2.8s','dur' => '9.6s', 'size' => '21px', 'op' => '0.21'],
      ['left' => '62%', 'delay' => '-4.2s','dur' => '8.2s', 'size' => '18px', 'op' => '0.26'],
      ['left' => '70%', 'delay' => '-1.8s','dur' => '10.8s','size' => '24px', 'op' => '0.17'],
      ['left' => '78%', 'delay' => '-5.4s','dur' => '9.0s', 'size' => '20px', 'op' => '0.23'],
      ['left' => '86%', 'delay' => '-3.2s','dur' => '12.0s','size' => '30px', 'op' => '0.15'],
      ['left' => '94%', 'delay' => '-6.6s','dur' => '8.4s', 'size' => '19px', 'op' => '0.25'],
    ];
  @endphp

  <div class="coffee-rain" aria-hidden="true">
    @foreach($coffeeDrops as $d)
      <span class="coffee-drop" style="--left: {{ $d['left'] }}; --delay: {{ $d['delay'] }}; --dur: {{ $d['dur'] }}; --size: {{ $d['size'] }}; --op: {{ $d['op'] }};">
        <i class="fas fa-coffee"></i>
      </span>
    @endforeach
  </div>

  <div class="card">
    <h1>Pilih Layout</h1>
    <p>Pilih layout foto yang kamu mau. Setting ini akan dipakai sampai cetak.</p>

    @if(session('message'))
      <p style="padding: 10px 12px; border: 1px solid rgba(255,255,255,0.35); border-radius: 10px; margin-top: 12px; color:#fff; background: rgba(0,0,0,0.18);">
        {{ session('message') }}
      </p>
    @endif

    @php
      $enabled = $layout_enabled ?? [1 => false, 2 => true, 3 => true, 4 => false];
      $framesByLayout = $frames_by_layout ?? ['layout1'=>[], 'layout2'=>[], 'layout3'=>[], 'layout4'=>[]];
    @endphp

    {{-- Hidden submit forms (robust: ensure layout+count always reaches server) --}}
    <form id="pbLayoutForm1" action="{{ route('tempcollage.chooseLayout') }}" method="post" style="display:none;">
      @csrf
      <input type="hidden" name="layout" value="1" />
    </form>
    <form id="pbLayoutForm2" action="{{ route('tempcollage.chooseLayout') }}" method="post" style="display:none;">
      @csrf
      <input type="hidden" name="layout" value="2" />
      <input type="hidden" name="count" value="2" />
    </form>
    <form id="pbLayoutForm2x2" action="{{ route('tempcollage.chooseLayout') }}" method="post" style="display:none;">
      @csrf
      <input type="hidden" name="layout" value="2" />
      <input type="hidden" name="count" value="4" />
    </form>
    <form id="pbLayoutForm3" action="{{ route('tempcollage.chooseLayout') }}" method="post" style="display:none;">
      @csrf
      <input type="hidden" name="layout" value="3" />
    </form>
    <form id="pbLayoutForm4" action="{{ route('tempcollage.chooseLayout') }}" method="post" style="display:none;">
      @csrf
      <input type="hidden" name="layout" value="4" />
    </form>

    <div class="layout-grid">
        <div role="button" tabindex="0" class="layout-option js-layout-option" data-form="pbLayoutForm1" data-layout="1" data-enabled="{{ ($enabled[1] ?? false) ? '1' : '0' }}">
          <div class="layout-preview layout-1">
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout1', 'img/bgkamera.jpg') }}'); --layout-pos: 50% 35%;"></span>
          </div>
          <div class="layout-title">Layout 1</div>
          <div class="layout-sub">1 foto</div>
          @if(count($framesByLayout['layout1'] ?? []) > 0)
            <button type="button" class="layout-preview-btn js-preview-frames" data-layout="layout1" title="Preview Frames">
              <i class="fas fa-eye"></i> Preview Frames ({{ count($framesByLayout['layout1']) }})
            </button>
          @endif
        </div>

        <div role="button" tabindex="0" class="layout-option js-layout-option" data-form="pbLayoutForm2" data-layout="2" data-count="2" data-enabled="{{ ($enabled[2] ?? true) ? '1' : '0' }}">
          <div class="layout-preview layout-2">
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 35%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 35%;"></span>
          </div>
          <div class="layout-title">Layout 2</div>
          <div class="layout-sub">2 foto</div>
          @if(count($framesByLayout['layout2'] ?? []) > 0)
            <button type="button" class="layout-preview-btn js-preview-frames" data-layout="layout2" title="Preview Frames">
              <i class="fas fa-eye"></i> Preview Frames ({{ count($framesByLayout['layout2']) }})
            </button>
          @endif
        </div>

        <div role="button" tabindex="0" class="layout-option js-layout-option" data-form="pbLayoutForm2x2" data-layout="2" data-count="4" data-enabled="{{ ($enabled[2] ?? true) ? '1' : '0' }}">
          <div class="layout-preview layout-4">
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 30%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 30%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 70%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout2', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 70%;"></span>
          </div>
          <div class="layout-title">Layout 2 (2×2)</div>
          <div class="layout-sub">4 foto • 2×2</div>
          @if(count($framesByLayout['layout2'] ?? []) > 0)
            <button type="button" class="layout-preview-btn js-preview-frames" data-layout="layout2" title="Preview Frames">
              <i class="fas fa-eye"></i> Preview Frames ({{ count($framesByLayout['layout2']) }})
            </button>
          @endif
        </div>

        <div role="button" tabindex="0" class="layout-option js-layout-option" data-form="pbLayoutForm3" data-layout="3" data-enabled="{{ ($enabled[3] ?? true) ? '1' : '0' }}">
          <div class="layout-preview layout-3">
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout3', 'img/bgkamera.jpg') }}'); --layout-pos: 50% 18%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout3', 'img/bgkamera.jpg') }}'); --layout-pos: 50% 36%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout3', 'img/bgkamera.jpg') }}'); --layout-pos: 50% 54%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout3', 'img/bgkamera.jpg') }}'); --layout-pos: 50% 72%;"></span>
          </div>
          <div class="layout-title">Layout 3</div>
          <div class="layout-sub">3 x 2 foto • vertikal 6 Foto</div>
          @if(count($framesByLayout['layout3'] ?? []) > 0)
            <button type="button" class="layout-preview-btn js-preview-frames" data-layout="layout3" title="Preview Frames">
              <i class="fas fa-eye"></i> Preview Frames ({{ count($framesByLayout['layout3']) }})
            </button>
          @endif
        </div>

        <div role="button" tabindex="0" class="layout-option js-layout-option" data-form="pbLayoutForm4" data-layout="4" data-enabled="{{ ($enabled[4] ?? false) ? '1' : '0' }}">
          <div class="layout-preview layout-4">
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 22%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 22%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 50%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 50%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 30% 78%;"></span>
            <span class="layout-thumb" style="--layout-thumb: url('{{ \App\Models\PageBackground::url('tempcollage.thumb.layout4', 'img/bgkamera.jpg') }}'); --layout-pos: 70% 78%;"></span>
          </div>
          <div class="layout-title">Layout 4</div>
          <div class="layout-sub">6 foto • 3×2</div>
          @if(count($framesByLayout['layout4'] ?? []) > 0)
            <button type="button" class="layout-preview-btn js-preview-frames" data-layout="layout4" title="Preview Frames">
              <i class="fas fa-eye"></i> Preview Frames ({{ count($framesByLayout['layout4']) }})
            </button>
          @endif
        </div>
      </div>
  </div>

  <div id="pb-coming-soon" class="pb-coming-soon" aria-hidden="true" style="display:none;">
    <div class="pb-coming-soon__card">
      <div style="font-family: 'Bungee'; font-size: 22px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.7);">COMING SOON</div>
      <div style="margin-top: 8px; color: rgba(255,255,255,0.92);">Layout ini belum tersedia.</div>
      <button type="button" class="formbutton" id="pb-coming-soon-close" style="margin-top: 14px; width:auto; padding: 10px 16px;">OK</button>
    </div>
  </div>

  {{-- Frame Preview Modal --}}
  <div id="pb-frame-preview" class="pb-frame-preview" aria-hidden="true" style="display:none;">
    <div class="pb-frame-preview__backdrop"></div>
    <div class="pb-frame-preview__card">
      <button type="button" class="pb-frame-preview__close" id="pb-frame-preview-close" title="Close">
        <i class="fas fa-times"></i>
      </button>
      <h2 id="pb-frame-preview-title" class="pb-frame-preview__title">Frame Preview</h2>
      <div id="pb-frame-preview-grid" class="pb-frame-preview__grid"></div>
    </div>
  </div>

  <script>
    window.pbFramesData = @json($framesByLayout ?? []);
  </script>

  <script>
    (function () {
      const modal = document.getElementById('pb-coming-soon');
      const closeBtn = document.getElementById('pb-coming-soon-close');
      function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
      }
      function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
      }
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (modal) modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });

      document.querySelectorAll('.js-layout-option').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          const enabled = String(btn.getAttribute('data-enabled') || '1') === '1';
          if (enabled) return;
          e.preventDefault();
          e.stopPropagation();
          openModal();
        });
      });

      // Submit the associated hidden form when clicking an enabled layout card.
      function submitLayoutCard(card) {
        const enabled = String(card.getAttribute('data-enabled') || '1') === '1';
        if (!enabled) {
          openModal();
          return;
        }
        const formId = String(card.getAttribute('data-form') || '');
        const form = formId ? document.getElementById(formId) : null;
        if (form && typeof form.submit === 'function') form.submit();
      }

      document.querySelectorAll('.js-layout-option').forEach((card) => {
        card.addEventListener('click', (e) => {
          // allow preview button clicks to work
          if (e.target && (e.target.closest && e.target.closest('.js-preview-frames'))) return;
          e.preventDefault();
          e.stopPropagation();
          submitLayoutCard(card);
        }, { passive: false });

        card.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            submitLayoutCard(card);
          }
        });
      });

      // Frame Preview Modal
      const previewModal = document.getElementById('pb-frame-preview');
      const previewCloseBtn = document.getElementById('pb-frame-preview-close');
      const previewTitle = document.getElementById('pb-frame-preview-title');
      const previewGrid = document.getElementById('pb-frame-preview-grid');
      const framesData = window.pbFramesData || {};

      function openPreviewModal(layoutKey) {
        if (!previewModal || !previewGrid) return;
        
        const frames = framesData[layoutKey] || [];
        const layoutLabels = {
          'layout1': 'Layout 1',
          'layout2': 'Layout 2',
          'layout3': 'Layout 3',
          'layout4': 'Layout 4'
        };
        
        if (previewTitle) {
          previewTitle.textContent = 'Preview Frames - ' + (layoutLabels[layoutKey] || layoutKey);
        }

        if (frames.length === 0) {
          previewGrid.innerHTML = '<div style="text-align:center; padding:40px; color:rgba(255,255,255,0.8);">Belum ada frame untuk layout ini.</div>';
        } else {
          previewGrid.innerHTML = frames.map(frame => `
            <div class="pb-frame-preview__item">
              <img src="${frame.url}" alt="${frame.name}" loading="lazy" />
            </div>
          `).join('');
        }

        previewModal.style.display = 'flex';
        previewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }

      function closePreviewModal() {
        if (!previewModal) return;
        previewModal.style.display = 'none';
        previewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }

      if (previewCloseBtn) {
        previewCloseBtn.addEventListener('click', closePreviewModal);
      }

      if (previewModal) {
        previewModal.addEventListener('click', (e) => {
          if (e.target === previewModal || e.target.classList.contains('pb-frame-preview__backdrop')) {
            closePreviewModal();
          }
        });
      }

      document.querySelectorAll('.js-preview-frames').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const layoutKey = btn.getAttribute('data-layout');
          if (layoutKey) {
            openPreviewModal(layoutKey);
          }
        });
      });
    })();
  </script>
</section>
@endsection
