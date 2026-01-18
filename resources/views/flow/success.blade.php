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

  <div id="successPopup" class="flow-popup" role="status" aria-live="polite">
    <div class="flow-popup__title">Yey!</div>
    <div class="flow-popup__body">Kamu bisa photo-photo nih.</div>
    <button type="button" id="successPopupClose" class="flow-popup__close" aria-label="Tutup">OK</button>
  </div>

  <div class="card">
    <h1>Pembayaran Berhasil</h1>
    <p>Terima kasih. Berikut kode redeem Anda:</p>
    <div class="code-box" id="codeBox">{{ $code }}</div>

    @if(session('message'))
      <div class="flow-notice" style="margin-top: 10px;">{{ session('message') }}</div>
    @endif

    @php
      $currentEmail = $transaction?->email;
      $hasRealEmail = $currentEmail && $currentEmail !== 'pending@example.com';
    @endphp

    <div class="flow-email">
      <p class="flow-email__label">Masukkan email untuk menerima hasil setelah print:</p>
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
            value="{{ $hasRealEmail ? $currentEmail : '' }}"
            placeholder="nama@email.com"
            autocomplete="email"
            inputmode="email"
            class="flow-email__input"
          />
          <button type="submit" id="saveEmailBtn" class="btn primary flow-email__btn">Simpan Email</button>
        </div>
      </form>
    </div>

    <div class="flow-actions">
      <button class="btn" id="copyBtn">Salin Kode</button>
      @if($hasRealEmail)
        <a href="{{ route('redeem.index') }}"><button class="btn primary">Redeem Kode</button></a>
      @else
        <button class="btn primary" type="button" disabled style="opacity:0.55; cursor:not-allowed;">Redeem Kode</button>
      @endif
    </div>
  </div>
</section>
<script src="{{ asset('js/flow.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var popup = document.getElementById('successPopup');
    var closeBtn = document.getElementById('successPopupClose');
    if (!popup) return;

    function hidePopup() {
      popup.classList.add('is-hidden');
    }

    if (closeBtn) closeBtn.addEventListener('click', hidePopup);
    window.setTimeout(hidePopup, 3200);
  });
</script>
@endsection
