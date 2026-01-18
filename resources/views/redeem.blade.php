@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed redeem-page" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('redeem.index', 'img/redeemcode.jpg') }}');">
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
    @if(session('message'))
      <div class="flow-notice" role="alert">{{ session('message') }}</div>
    @endif
    <div id="redeemNotice" class="flow-notice" role="status" aria-live="polite" hidden></div>

    <h1>Redeem Code</h1>
    <p>Tap kolom kode lalu tekan <strong>Tempel</strong> untuk memasukkan kode.</p>

    <form action="{{ route('redeem.store') }}" method="post">
      @csrf
      <div style="margin-top:12px; display:flex; justify-content:center;">
        <input
          type="text"
          name="code"
          id="redeemInput"
          autocomplete="off"
          spellcheck="false"
          autocapitalize="characters"
          inputmode="text"
          maxlength="5"
          placeholder="XXXXX"
          style="width: min(360px, 80vw); height: 56px; font-size: 22px; text-align: center; border-radius: 12px; border: 1px solid rgba(124, 45, 18, 0.14); outline: none;"
          required
        />
      </div>

      <div class="flow-actions">
        <button type="button" class="btn" id="pasteBtn">Tempel</button>
        <button type="submit" class="btn primary">Submit</button>
      </div>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('redeemInput');
    var pasteBtn = document.getElementById('pasteBtn');
    var notice = document.getElementById('redeemNotice');
    if (!input || !pasteBtn || !notice) return;

    function showNotice(message) {
      notice.textContent = message;
      notice.hidden = false;
      window.setTimeout(function () { notice.hidden = true; }, 2600);
    }

    function normalizeCode(text) {
      return (text || '')
        .toString()
        .trim()
        .replace(/\s+/g, '')
        .toUpperCase()
        .slice(0, 5);
    }

    async function tryPaste() {
      if (!navigator.clipboard || !navigator.clipboard.readText) {
        showNotice('Fitur tempel otomatis tidak tersedia di browser ini. Silakan paste manual.');
        return;
      }
      try {
        var text = await navigator.clipboard.readText();
        var code = normalizeCode(text);
        if (!code) {
          showNotice('Clipboard kosong. Salin kode dulu ya, lalu tekan Tempel.');
          return;
        }
        input.value = code;
        input.focus();
      } catch (e) {
        showNotice('Izin clipboard tidak aktif. Tekan & tahan untuk Paste, atau gunakan keyboard.');
      }
    }

    pasteBtn.addEventListener('click', tryPaste);

    // UX touchscreen: tap input -> coba tempel otomatis
    input.addEventListener('pointerdown', function () {
      window.setTimeout(tryPaste, 0);
    });
  });
</script>
@endsection
