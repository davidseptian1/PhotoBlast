<!DOCTYPE html>
<html lang="en" class="@if(Request::routeIs('flow.*') || Request::routeIs('redeem.*') || Request::routeIs('tempcollage.*') || Request::routeIs(['camera','retake-photo'])) kiosk-fullscreen @endif">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Photochika</title>
  <meta name="theme-color" content="#6777ef">
  <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
  <link rel="manifest" href="{{ asset('/manifest.json') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard@latest/build/css/index.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <!-- Tutorial CSS and Libraries -->
  <link rel="stylesheet" href="{{ asset('css/tutorial.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">

  @if(Request::routeIs('flow.*') || Request::routeIs('redeem.*') || Request::routeIs('tempcollage.*') || Request::routeIs(['camera', 'retake-photo', 'list-photo', 'print-photo', 'print']))
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
  @endif
  @if(Request::routeIs(['camera', 'retake-photo', 'print']))
    <link rel="stylesheet" href="{{ asset('css/camera.css') }}">
    <link rel="stylesheet" href="{{ asset('css/print-a6.css') }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset("js/jszip.min.js") }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
  @else
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @endif
  @if(Request::routeIs(['tempcollage.*', 'camera', 'retake-photo', 'list-photo', 'print-photo', 'print']))
    <style>
      .pb-progress-stepper {
        position: fixed;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9998;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 8px 16px;
        border-radius: 999px;
        background: rgba(0,0,0,0.75);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.15);
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
      }
      .pb-progress-step {
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .pb-progress-step-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.08);
        border: 2px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.4);
        font-size: 14px;
        transition: all 0.3s ease;
      }
      .pb-progress-step.is-active .pb-progress-step-icon {
        background: linear-gradient(135deg, rgba(249,115,22,0.95), rgba(234,88,12,0.95));
        border-color: rgba(249,115,22,0.95);
        color: #fff;
        box-shadow: 0 0 16px rgba(249,115,22,0.5);
      }
      .pb-progress-step.is-done .pb-progress-step-icon {
        background: rgba(34,197,94,0.9);
        border-color: rgba(34,197,94,0.95);
        color: #fff;
      }
      .pb-progress-step-label {
        color: rgba(255,255,255,0.5);
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.3s ease;
        display: none;
      }
      .pb-progress-step.is-active .pb-progress-step-label {
        color: rgba(255,255,255,0.95);
        font-weight: 600;
        display: inline;
      }
      .pb-progress-step.is-done .pb-progress-step-label {
        color: rgba(255,255,255,0.65);
      }
      .pb-progress-divider {
        width: 16px;
        height: 2px;
        background: rgba(255,255,255,0.15);
        transition: all 0.3s ease;
      }
      .pb-progress-step.is-done ~ .pb-progress-divider {
        background: rgba(34,197,94,0.5);
      }
      .pb-progress-step.is-active ~ .pb-progress-divider {
        background: rgba(249,115,22,0.4);
      }
      @media (min-width: 769px) {
        .pb-progress-stepper { 
          padding: 10px 18px; 
          gap: 6px;
        }
        .pb-progress-step-icon { 
          width: 34px; 
          height: 34px; 
          font-size: 15px; 
        }
        .pb-progress-step-label { 
          display: inline;
          font-size: 12px; 
        }
        .pb-progress-divider { 
          width: 20px; 
        }
      }
      @media (max-width: 480px) {
        .pb-progress-stepper { 
          padding: 6px 12px; 
          gap: 3px;
          top: 8px;
        }
        .pb-progress-step-icon { 
          width: 28px; 
          height: 28px; 
          font-size: 12px; 
        }
        .pb-progress-divider { 
          width: 12px; 
        }
      }
    </style>
  @endif
  @if(Request::routeIs('flow.*') || Request::routeIs('redeem.*') || Request::routeIs('tempcollage.*') || Request::routeIs(['camera','retake-photo']))
    <link rel="preload" as="image" href="/img/logostart.jpg" fetchpriority="high">
    <link rel="preload" as="image" href="/img/logosetelahstart.jpg">
    <link rel="preload" as="image" href="/img/bgpembayaran.jpg">
    <link rel="preload" as="image" href="/img/bgsukses.jpg">
    <link rel="preload" as="image" href="/img/redeemcode.jpg">
    <link rel="preload" as="image" href="/img/pilihanlayout.jpg">
    <link rel="preload" as="image" href="/img/bgkamera.png">
  @endif

  @if(Request::routeIs(['print']))
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @endif
</head>
<body class="@if(Request::routeIs('flow.*') || Request::routeIs('redeem.*') || Request::routeIs('tempcollage.*') || Request::routeIs(['camera','retake-photo'])) kiosk-fullscreen @endif">
  @php
    $pbDeadline = session('pb_flow_deadline');
    $pbDeadlineTs = $pbDeadline ? (int) $pbDeadline : 0;
  @endphp

  @if(Request::routeIs(['tempcollage.*', 'camera', 'retake-photo', 'list-photo', 'print-photo', 'print']))
    @php
      $currentStep = 1;
      if (Request::routeIs(['camera', 'retake-photo'])) $currentStep = 2;
      elseif (Request::routeIs(['list-photo'])) $currentStep = 3;
      elseif (Request::routeIs(['print-photo', 'print'])) $currentStep = 4;
    @endphp
    <div class="pb-progress-stepper" aria-label="Progress">
      <div class="pb-progress-step {{ $currentStep >= 1 ? 'is-active' : '' }} {{ $currentStep > 1 ? 'is-done' : '' }}">
        <div class="pb-progress-step-icon">
          <i class="fas {{ $currentStep > 1 ? 'fa-check' : 'fa-th-large' }}"></i>
        </div>
        <div class="pb-progress-step-label">Layout</div>
      </div>
      <div class="pb-progress-divider"></div>
      <div class="pb-progress-step {{ $currentStep >= 2 ? 'is-active' : '' }} {{ $currentStep > 2 ? 'is-done' : '' }}">
        <div class="pb-progress-step-icon">
          <i class="fas {{ $currentStep > 2 ? 'fa-check' : 'fa-camera' }}"></i>
        </div>
        <div class="pb-progress-step-label">Foto</div>
      </div>
      <div class="pb-progress-divider"></div>
      <div class="pb-progress-step {{ $currentStep >= 3 ? 'is-active' : '' }} {{ $currentStep > 3 ? 'is-done' : '' }}">
        <div class="pb-progress-step-icon">
          <i class="fas {{ $currentStep > 3 ? 'fa-check' : 'fa-image' }}"></i>
        </div>
        <div class="pb-progress-step-label">Frame</div>
      </div>
      <div class="pb-progress-divider"></div>
      <div class="pb-progress-step {{ $currentStep >= 4 ? 'is-active' : '' }}">
        <div class="pb-progress-step-icon">
          <i class="fas fa-print"></i>
        </div>
        <div class="pb-progress-step-label">Print</div>
      </div>
    </div>
  @endif

  @if($pbDeadlineTs > 0 && !Request::routeIs('admin.*') && !Request::is('admin/*'))
    <style>
      .pb-flow-timer { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; align-items: center; gap: 12px; padding: 16px 18px; border-radius: 18px; background: rgba(0,0,0,0.62); border: 1px solid rgba(255,255,255,0.24); color: #fff; font-family: 'Poppins', sans-serif; font-size: 18px; line-height: 1; box-shadow: 0 12px 34px rgba(0,0,0,0.42); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
      .pb-flow-timer .label { opacity: 0.95; font-weight: 600; }
      .pb-flow-timer .time { font-weight: 900; letter-spacing: 1px; min-width: 78px; text-align: right; font-size: 22px; }
      .pb-flow-timer.is-danger { background: rgba(220, 38, 38, 0.72); border-color: rgba(255,255,255,0.32); }
      @media (max-width: 520px) {
        .pb-flow-timer { top: 10px; right: 10px; padding: 12px 14px; font-size: 16px; }
        .pb-flow-timer .time { font-size: 20px; min-width: 72px; }
      }
    </style>
    <div class="pb-flow-timer" id="pb-flow-timer" data-deadline="{{ $pbDeadlineTs }}" aria-label="Sisa waktu sesi">
      <span class="label">Sisa waktu</span>
      <span class="time" id="pb-flow-countdown">08:00</span>
    </div>
    <script>
      (function(){
        try {
          var el = document.getElementById('pb-flow-timer');
          if (!el) return;
          var deadline = parseInt(el.getAttribute('data-deadline') || '0', 10) * 1000;
          if (!deadline) return;

          var out = document.getElementById('pb-flow-countdown');
          var timeoutUrl = "{{ route('pb.timeout') }}";
          var redirected = false;
          var intervalId = null;

          function pad2(n){ return (n < 10 ? '0' : '') + n; }
          function tick(){
            var msLeft = deadline - Date.now();
            var secLeft = Math.max(0, Math.floor(msLeft / 1000));
            var m = Math.floor(secLeft / 60);
            var s = secLeft % 60;
            if (out) out.textContent = pad2(m) + ':' + pad2(s);

            if (secLeft <= 30) el.classList.add('is-danger');
            if (secLeft <= 0) {
              if (redirected) return;
              redirected = true;
              try { if (intervalId) clearInterval(intervalId); } catch (e) {}
              // replace() avoids bouncing back, and cache-busting avoids any weird reload behavior
              window.location.replace(timeoutUrl + '?ts=' + Date.now());
              return;
            }
          }

          tick();
          intervalId = setInterval(tick, 250);
        } catch (e) {
          // ignore
        }
      })();
    </script>
  @endif

  @yield('content')
  @if(Request::routeIs('print-photo'))
    <script src="{{ asset('js/script-limit.js') }}"></script>
  @endif

  @if(Request::routeIs('flow.*') || Request::routeIs('redeem.*') || Request::routeIs('tempcollage.*'))
    <div class="keyboard-container">
      <div class="keyboard-preview" id="keyboardPreview" aria-hidden="true">
        <div class="keyboard-preview-label">Sedang mengetik:</div>
        <div class="keyboard-preview-text" id="keyboardPreviewText"></div>
      </div>
      <div class="simple-keyboard" aria-hidden="true"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/simple-keyboard@latest/build/index.js"></script>
    <script src="{{ asset('js/keyboard.js') }}"></script>
  @endif

  <!-- Tutorial Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/tutorial-helper.js') }}"></script>

  <script src="{{ asset('/sw.js') }}"></script>
  <script>  
    if(!navigator.serviceWorker.controller) {
      navigator.serviceWorker.register('/sw.js').then(function(reg){
        console.log('Service worker has been registered for scope: ' + reg.scope);
      })
    }
  </script>
</body>
</html>
