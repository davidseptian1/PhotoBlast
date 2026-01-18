@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed flow-payment" style="--flow-bg-image: url('{{ \App\Models\PageBackground::url('flow.payment', 'img/bgpembayaran.jpg') }}');">
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
    <h1>Pembayaran</h1>
    <div class="flow-payment__grid">
      <div class="flow-payment__left">
        <p class="flow-payment__label">Jumlah yang harus dibayar</p>
        <div class="flow-payment__price">Rp {{ number_format($amount,0,',','.') }}</div>
        <p class="flow-payment__hint">Buka e-wallet / m-banking, lalu scan QRIS di bawah. Pastikan nominal sesuai.</p>
      </div>

      <div class="flow-payment__right">
        <div class="qr-box flow-payment__qr">
          <div class="flow-payment__qr-title">Scan QRIS</div>
          <img class="flow-payment__qr-img" src="/img/qris/qris.png" alt="QRIS" loading="eager">
        </div>
      </div>
    </div>

    <form action="{{ route('flow.paid') }}" method="post" class="flow-actions">
      @csrf
      <button type="submit" class="btn primary">Saya sudah bayar (simulate)</button>
    </form>
  </div>
</section>
@endsection
