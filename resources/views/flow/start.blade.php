@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed flow-start" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('flow.start', 'img/bgstart2.png') }}');">
  @php
    $startPosterUrl = \App\Models\PageBackground::url('flow.start', 'img/bgstart2.png');
    $startVideoRel = 'img/vidstart.mp4';
    $startVideoUrl = asset($startVideoRel);
    $startVideoExists = is_file(public_path($startVideoRel));

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

  @if ($startVideoExists)
    <video
      id="pbStartVideo"
      autoplay
      muted
      loop
      playsinline
      preload="auto"
      poster="{{ $startPosterUrl }}"
      style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0; pointer-events:none;"
      aria-hidden="true"
    >
      <source src="{{ $startVideoUrl }}" type="video/mp4">
    </video>
  @endif

  <div class="coffee-rain" aria-hidden="true">
    @foreach($coffeeDrops as $d)
      <span class="coffee-drop" style="--left: {{ $d['left'] }}; --delay: {{ $d['delay'] }}; --dur: {{ $d['dur'] }}; --size: {{ $d['size'] }}; --op: {{ $d['op'] }};">
        <i class="fas fa-coffee"></i>
      </span>
    @endforeach
  </div>

  <div class="card" id="pbStartCard" style="opacity:0; pointer-events:none;">
    @php($welcomeText = 'Welcome to Photochika')
    <h1 class="wave-text" aria-label="{{ $welcomeText }}" style="color: #ffffff; font-size: 56px; font-weight: 900; letter-spacing: 8px; margin-bottom: 20px; text-transform: uppercase; text-shadow: 0 4px 16px rgba(0,0,0,0.9), 0 8px 32px rgba(0,0,0,0.7), 0 0 40px rgba(249,115,22,0.5), 0 2px 4px rgba(0,0,0,1); white-space: nowrap;">
      @foreach(preg_split('//u', $welcomeText, -1, PREG_SPLIT_NO_EMPTY) as $i => $ch)
        <span class="wave-char" style="--i: {{ $i }}">@if($ch === ' ')&nbsp;@else{{ $ch }}@endif</span>
      @endforeach
    </h1>
    <p style="font-size: 18px; margin-bottom: 24px; text-shadow: 0 2px 12px rgba(0,0,0,0.9), 0 4px 24px rgba(0,0,0,0.7); color: #ffffff; font-weight: 600;">Pilih paket, bayar, dan dapatkan kode redeem untuk mulai mengambil foto.</p>
    <form method="POST" action="{{ route('flow.begin') }}" style="margin-top:20px;">
      @csrf
      <button type="submit" class="btn primary btn-start-enhanced">
        <span class="btn-text">Start</span>
        <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
      </button>
    </form>
  </div>

  <script>
    (function () {
      var card = document.getElementById('pbStartCard');
      if (!card) return;

      var idleMs = 5000;
      var idleTimer = null;

      function showCard() {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
      }

      function hideCard() {
        card.style.opacity = '0';
        card.style.pointerEvents = 'none';
      }

      function resetIdleTimer() {
        if (idleTimer) {
          clearTimeout(idleTimer);
          idleTimer = null;
        }
        idleTimer = setTimeout(function () {
          hideCard();
        }, idleMs);
      }

      function onPointerMove() {
        showCard();
        resetIdleTimer();
      }

      // Default: tampilkan video saja.
      // Begitu cursor/pointer digerakkan, munculkan tombol Start.
      // Jika idle 5 detik, kembali ke tampilan video saja.
      window.addEventListener('pointermove', onPointerMove, { passive: true });
    })();
  </script>
</section>
@endsection
