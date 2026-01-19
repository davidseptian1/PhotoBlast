@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed flow-success" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('flow.success', 'img/bgsukses.jpg') }}');">
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

  <div id="successBackdrop" class="success-backdrop" style="display: none;"></div>
  <div id="successPopup" class="flow-popup flow-popup-success" role="status" aria-live="polite" style="display: none;">
    <div class="success-icon">
      <div class="success-icon-circle">
        <i class="fas fa-check"></i>
      </div>
    </div>
    <div class="flow-popup__title" style="font-size: 32px; margin-top: 16px;">Pembayaran Berhasil!</div>
    <div class="flow-popup__body" style="font-size: 18px; margin-top: 12px;">Anda sudah membayar. Halaman akan otomatis menutup dalam <span id="countdownTimer" style="font-weight: 800; color: #f97316; font-size: 24px;">5</span> detik</div>
    <button type="button" id="successPopupClose" class="flow-popup__close" aria-label="Tutup" style="margin-top: 20px; opacity: 0.5; pointer-events: none;">OK, Mengerti</button>
  </div>

  <div class="card">
    <h1>Masukkan Email</h1>
    <p>Isi email Anda untuk melanjutkan ke pilihan layout.</p>

    @if(session('message'))
      <div class="flow-notice" style="margin-top: 10px;">{{ session('message') }}</div>
    @endif

    @php
      $currentEmail = $transaction?->email;
      $hasRealEmail = $currentEmail && $currentEmail !== 'pending@example.com';
    @endphp

    <div class="flow-email">
      <p class="flow-email__label">Masukkan email:</p>
      <form action="{{ route('flow.updateEmail') }}" method="post" class="flow-email__form">
        @csrf
        <div class="flow-email__row">
          <input
            type="email"
            name="email"
            id="emailInput"
            data-osk="true"
            data-osk-layout="email"
            data-osk-enter-target="#saveEmailBtn"
            required
            value=""
            placeholder="nama@email.com"
            autocomplete="off"
            inputmode="email"
            class="flow-email__input"
          />
          <button type="submit" id="saveEmailBtn" class="btn primary flow-email__btn">Submit</button>
        </div>
      </form>
    </div>
  </div>
</section>
<script src="{{ asset('js/flow.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var popup = document.getElementById('successPopup');
    var backdrop = document.getElementById('successBackdrop');
    var closeBtn = document.getElementById('successPopupClose');
    var countdownEl = document.getElementById('countdownTimer');
    if (!popup || !backdrop) return;

    var countdown = 5;
    var timer = null;

    // Check if popup has already been shown in this session
    var popupShown = sessionStorage.getItem('paymentPopupShown');
    
    // Only show popup if it hasn't been shown yet in this session
    if (!popupShown) {
      popup.style.display = 'flex';
      backdrop.style.display = 'block';
      
      // Mark popup as shown in this session
      sessionStorage.setItem('paymentPopupShown', 'true');
    } else {
      // If already shown, keep popup hidden
      popup.style.display = 'none';
      backdrop.style.display = 'none';
      return; // Exit early, no need to set up countdown
    }

    function updateCountdown() {
      countdown--;
      if (countdownEl) {
        countdownEl.textContent = countdown;
        countdownEl.style.animation = 'countdownPulse 0.3s ease';
        setTimeout(function() {
          if (countdownEl) countdownEl.style.animation = '';
        }, 300);
      }
      
      // Enable button when countdown reaches 2 or less
      if (countdown <= 2 && closeBtn) {
        closeBtn.style.opacity = '1';
        closeBtn.style.pointerEvents = 'auto';
      }
      
      if (countdown <= 0) {
        clearInterval(timer);
        hidePopup();
      }
    }

    function hidePopup() {
      if (timer) clearInterval(timer);
      popup.classList.add('is-hidden');
      backdrop.classList.add('is-hidden');
      
      // Completely remove after animation
      setTimeout(function() {
        popup.style.display = 'none';
        backdrop.style.display = 'none';
      }, 300);
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', hidePopup);
    }

    // Start countdown immediately
    timer = setInterval(updateCountdown, 1000);
  });
</script>
@endsection
