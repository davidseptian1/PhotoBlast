@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed flow-package" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('flow.package', 'img/logosetelahstart.jpg') }}');">
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
      <div class="flow-notice" role="status" aria-live="polite">{{ session('message') }}</div>
    @endif
    @if($errors->any())
      <div class="flow-notice" role="alert">{{ $errors->first() }}</div>
    @endif
    <div id="packageNotice" class="flow-notice" role="status" aria-live="polite" hidden></div>

    <h1 style="background: linear-gradient(135deg, #fff 0%, #f97316 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 36px; margin-bottom: 8px;">PILIH PAKET</h1>
    <p style="font-size: 16px; margin-bottom: 20px;">Silakan pilih paket yang sesuai kebutuhan Anda.</p>
    <form action="{{ route('flow.package.choose') }}" method="post">
      @csrf
      <div class="packages">
        <label class="package package-selectable" data-package="a6">
          <div class="package-badge package-badge-recommended">RECOMMENDED</div>
          <div class="package-check"><i class="fas fa-check-circle"></i></div>
          <h3>A6 (Default)</h3>
          <div class="price">Rp 30.000</div>
          <div class="note">Ukuran standar</div>
          <div class="package-cta">
            <i class="fas fa-hand-pointer"></i>
            <span>Klik untuk Pilih</span>
          </div>
          <input id="packageA6" type="radio" name="package" value="30000" checked style="display:none;">
        </label>
        <label class="package package-selectable package-disabled" data-package="a3">
          <div class="package-badge package-badge-soon">COMING SOON</div>
          <div class="package-check"><i class="fas fa-check-circle"></i></div>
          <h3>A3</h3>
          <div class="price">Rp 40.000</div>
          <div class="note">Ukuran A3, lebih besar</div>
          <div class="package-cta">
            <i class="fas fa-clock"></i>
            <span>Segera Hadir</span>
          </div>
          <input id="packageA3" type="radio" name="package" value="40000" style="display:none;">
        </label>
      </div>
      <button type="submit" class="btn primary" style="margin-top:24px; font-size: 18px; padding: 14px 32px;">Lanjut ke Pembayaran</button>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var radioA6 = document.getElementById('packageA6');
    var radioA3 = document.getElementById('packageA3');
    var notice = document.getElementById('packageNotice');
    var packages = document.querySelectorAll('.package-selectable');
    var hideTimer = null;

    if (!radioA6 || !radioA3 || !notice) return;

    function showNotice(message) {
      notice.textContent = message;
      notice.hidden = false;
      if (hideTimer) window.clearTimeout(hideTimer);
      hideTimer = window.setTimeout(function () {
        notice.hidden = true;
      }, 2600);
    }

    function updateSelection() {
      packages.forEach(function(pkg) {
        var pkgType = pkg.getAttribute('data-package');
        if (pkgType === 'a6' && radioA6.checked) {
          pkg.classList.add('package-selected');
        } else if (pkgType === 'a3' && radioA3.checked) {
          pkg.classList.add('package-selected');
        } else {
          pkg.classList.remove('package-selected');
        }
      });
    }

    packages.forEach(function(pkg) {
      pkg.addEventListener('click', function(e) {
        var pkgType = pkg.getAttribute('data-package');
        if (pkgType === 'a3') {
          e.preventDefault();
          showNotice('Paket A3 masih Coming Soon. Untuk sekarang silakan pilih A6 dulu ya.');
          radioA6.checked = true;
          updateSelection();
        }
      });
    });

    radioA6.addEventListener('change', updateSelection);
    radioA3.addEventListener('change', function() {
      if (radioA3.checked) {
        showNotice('Paket A3 masih Coming Soon. Untuk sekarang silakan pilih A6 dulu ya.');
        radioA6.checked = true;
        updateSelection();
      }
    });

    // Initial selection state
    updateSelection();
  });
</script>
@endsection
