@extends('layouts.app')
@section('content')
<section class="flow-container flow-themed thanks-page" style="--flow-bg-image: url('{{ asset('img/bgkamera.png') }}');">
  @php
    $particles = [];
    for ($i = 0; $i < 20; $i++) {
      $particles[] = [
        'left' => rand(0, 100) . '%',
        'delay' => (rand(0, 40) * 0.1) . 's',
        'dur' => (rand(15, 30) * 0.1) . 's',
        'size' => rand(4, 16) . 'px',
      ];
    }
  @endphp

  <div class="thanks-particles" aria-hidden="true">
    @foreach($particles as $p)
      <span class="particle" style="left: {{ $p['left'] }}; animation-delay: {{ $p['delay'] }}; animation-duration: {{ $p['dur'] }}; width: {{ $p['size'] }}; height: {{ $p['size'] }};"></span>
    @endforeach
  </div>

  <div class="thanks-content">
    <div class="thanks-title-wrapper">
      <h1 class="thanks-title gradient-text">THANKS</h1>
      <h1 class="thanks-title-shadow">THANKS</h1>
    </div>

    <div class="thanks-message">
      <p class="fade-in-up" style="animation-delay: 0.3s;">For sharing your best poses using</p>
      <p class="fade-in-up highlight-text" style="animation-delay: 0.5s;">Photo Studio Chika</p>
      <p class="fade-in-up" style="animation-delay: 0.7s;">Ignite your passion by sharing your precious</p>
      <p class="fade-in-up highlight-text" style="animation-delay: 0.9s;">memories</p>
    </div>
  </div>
</section>
<script src="{{ asset('js/thanks.js') }}"></script>
@endsection
