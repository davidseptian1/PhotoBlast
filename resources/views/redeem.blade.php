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

    <h1>Masukkan Email</h1>
    <p>Isi email Anda lalu tekan <strong>Submit</strong>.</p>

    <form action="{{ route('redeem.store') }}" method="post">
      @csrf
      <div style="margin-top:12px; display:flex; justify-content:center;">
        <input
          type="email"
          name="email"
          id="emailInput"
          autocomplete="email"
          spellcheck="false"
          inputmode="email"
          placeholder="nama@email.com"
          style="width: min(360px, 80vw); height: 56px; font-size: 18px; text-align: center; border-radius: 12px; border: 1px solid rgba(124, 45, 18, 0.14); outline: none;"
          required
        />
      </div>

      <div class="flow-actions">
        <button type="submit" class="btn primary">Submit</button>
      </div>
    </form>
  </div>
</section>
@endsection
