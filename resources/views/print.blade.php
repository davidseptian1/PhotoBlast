@extends('layouts.app')
@section('content')
@php
  // Reuse the same background as the photo-selection page for consistent look.
  $bgKey = 'print-photo';
@endphp

<section class="content listphoto-bg" style="background-image: url('{{ \App\Models\PageBackground::url($bgKey, 'img/bgkamera.png') }}');">
  @include('layouts.nav')

  <div class="chooseTitle">
    <h1>PRINT</h1>
    <h1>PRINT</h1>
    <h1>PRINT</h1>
    <h1>PRINT</h1>
  </div>

  <div class="tagline" style="margin-top:-10vh;">
    <span style="color: #cfd4da">PLEASE </span>
    <span style="font-weight: bolder; color: white;">WAIT</span>
  </div>

  <div class="listphoto-shell" style="padding-top: 22px;">
    <div class="listphoto-card" style="text-align:center;">
      <h2 style="margin-bottom: 10px;">Menyiapkan hasil cetak…</h2>
      <div style="opacity:0.92; font-size: 15px; line-height: 1.6;">
        Silakan ikuti dialog print yang muncul.
        <br />
        Jangan tutup halaman ini sampai proses selesai.
      </div>
      <div style="opacity:0.70; font-size: 12px; margin-top: 10px;">
        Jika dialog tidak muncul, periksa pop-up blocker pada browser.
      </div>
    </div>
  </div>
</section>
  <script>
    const layoutKey = "{{ $layout_key }}";
    const layoutCount = {{ (int) $layout_count }};
    const photos = {!! json_encode($photos) !!}
    const frameUrl = {!! json_encode($frame_url ?? null) !!};
    const frameConfig = {!! json_encode($frame_config ?? null) !!};
    const globalGapConfig = {!! json_encode(['row_gap_ratio' => (float) ($global_row_gap_ratio ?? config('photoblast.row_gap_ratio', 0.012))]) !!};
    const email = "{{ $email }}";
    const forcePrintPictures = {!! json_encode((bool) ($force_print_pictures ?? true)) !!};
    const forceUseBrowser = {!! json_encode((bool) ($force_use_browser ?? false)) !!};
  </script>
  <script src="{{ asset('js/print.js') }}?v={{ @filemtime(public_path('js/print.js')) }}"></script>
  
  <script>
    // Tutorial Implementation for Print Page
    document.addEventListener('DOMContentLoaded', function() {
      const tutorialHelper = new TutorialHelper();
      
      setTimeout(function() {
        if (!tutorialHelper.hasSeenTutorial) {
          tutorialHelper.showWelcomePopup('Halaman Print').then(showTutorial => {
            if (showTutorial) {
              const steps = [
                {
                  element: '.listphoto-card',
                  popover: {
                    title: '🖨️ Proses Print',
                    description: 'Sistem sedang menyiapkan hasil foto Anda untuk dicetak. Dialog print akan muncul sebentar lagi.',
                    position: 'top'
                  }
                },
                {
                  popover: {
                    title: '📋 Langkah Selanjutnya',
                    description: 'Setelah dialog print muncul, pilih printer dan klik OK/Print. Jangan tutup halaman ini sampai proses selesai!',
                  }
                },
                {
                  popover: {
                    title: '✅ Selesai!',
                    description: 'Foto Anda akan segera dicetak. Terima kasih telah menggunakan layanan kami! Jangan lupa cek email untuk salinan digital! 📧✨',
                  }
                }
              ];
              
              tutorialHelper.startTour(steps);
            } else {
              tutorialHelper.markTutorialAsSeen();
            }
          });
        }

        // Tombol replay tutorial
        const replayBtn = document.createElement('button');
        replayBtn.className = 'tutorial-replay-btn';
        replayBtn.innerHTML = '❓ Lihat Tutorial Lagi';
        replayBtn.onclick = function() {
          tutorialHelper.resetTutorial();
          location.reload();
        };
        document.body.appendChild(replayBtn);
      }, 500); // Delay 0.5 detik
    });
  </script>
@endsection
