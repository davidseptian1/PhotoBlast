@extends('layouts.app')
@section('content')
<section id="content" style="background-image: url('{{ \App\Models\PageBackground::url('camera', 'img/bgkamera.png') }}');">
  @include('layouts.nav')
  <style>
    /* Ensure video preview is not mirrored and fits its container */
    .video-container { position:relative; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .video-container video,
    .video-container canvas { position:absolute; top:0; left:0; width:100%; height:100%; transform: none !important; -webkit-transform: none !important; object-fit: contain; max-width:100%; display:block; }

    /* Tidy camera control buttons */
    .button { display:flex; gap:18px; align-items:center; justify-content:center; }
    .button .timer-button, .button .autocapture-button { display:flex; flex-direction:column; align-items:center; }
    /* Button sizes are handled in public/css/camera.css for consistency */
  </style>
  <section id="Camera">
    <section id="Photos">      <a href="{{ route('tempcollage.index') }}" class="back-layout-btn">
        <i class="fas fa-arrow-left"></i> Kembali ke Layout
      </a>
      <div class="filter-panel" aria-label="Filter">
        <div class="filter-title">Filter</div>
        <select id="cameraFilterSelect" class="formbutton" style="padding:8px 10px; border-radius: 12px; min-width: unset; width:100%; background: rgba(255,255,255,0.08); color:#fff; border:2px solid rgba(255,255,255,0.28);">
          <option value="none" style="background:#0b1220; color:#fff;">NO FILTER</option>
          <option value="bw" style="background:#0b1220; color:#fff;">B&W</option>
          <option value="sepia" style="background:#0b1220; color:#fff;">SEPIA</option>
          <option value="vintage" style="background:#0b1220; color:#fff;">VINTAGE</option>
          <option value="soft" style="background:#0b1220; color:#fff;">SOFT</option>
          <option value="noir" style="background:#0b1220; color:#fff;">NOIR</option>
          <option value="vivid" style="background:#0b1220; color:#fff;">VIVID</option>
        </select>
      </div>
      <div id="capture-info" aria-label="Capture info" style="margin-top:10px; padding:10px 12px; border-radius:14px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22); color:#fff; font-size:12px; line-height:1.35; text-shadow: 0 2px 10px rgba(0,0,0,0.55);">
        Loading camera info...
      </div>

      <div id="PhotoList" aria-label="Captured photos"></div>
    </section>
    <div class="video-container">
      <video id="video" class=" watch-video"></video>
      <canvas id="video-display-canvas" style="display:none;"></canvas>
      <div style="position:absolute; right:16px; top:12px; z-index:50;">
        <button id="mirrorToggle" class="flip-btn">
          <i class="fas fa-sync-alt"></i>
          <span>Flip</span>
        </button>
      </div>
      <div id="countdown-display"></div>

      <div class="button">
        <div class="timer-button">
          <button>
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40" viewBox="0 0 80 80" fill="none" style="margin-top: 7px;">
                <rect width="105" height="100" fill="url(#pattern0_108_25)"/>
                <defs>
                <pattern id="pattern0_108_25" patternContentUnits="objectBoundingBox" width="10" height="10">
                <use xlink:href="#image0_108_25" transform="scale(0.015)"/>
                </pattern>
                <image id="image0_108_25" width="50" height="50" xlink:href="data:image/png;base64,iVBORW0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAE+UlEQVR4nO2aa4hVVRTHx1FnGoPMXpZa2QQllkEPeiiWVFBRVhY90B5kU0FJKmoPelDRl7Q+lRRRH6svgdFLQ4tyKFKappxKK2/0ULOcLMqc1JFfrMt/y/J0z5lzzzn3egP/cGD2a+39n73X2mutfZua9qO2AM4A3gU2AW8C4+o18SHAlcCTwFvAOmArsAPYDvwEfA68DTwCXAQMj5E1BviDvbEBOLhWiz8AuBFYDuymMnbqi2t7DbgcGOLkzlb7EmAU0KnydUUTaAPma9sD/gReB+YCFwBHAy1uzFDgGLXNU99tbvxG4DZgMLBIdfM11nbZMK9IEpcB37kFrARmAMNi+huBoTFtBwLTgQ+dvC+A9yJE9iJWxC487yb8CJjs2puBicDD0oOS9MMfo/Vqewg4Bxjkxk8GVkWOXrFEgKOAbgn7C7jdFu6U3Bb/PdXDxjxoMiRrkGRvK5wI0K7/btj2shkEWrUII5YXJuN+kynZJwBfFUZEO1FyulA2f8Apmqho9AATNMcI4NzcRKQT4TiZ+WtT/bW6FwaEk1UNtgNXRdaSi0hQ7C/dTnQk3BdZiLwMnAWcHvlOjRiCQOTFLCYWKd141V1TDYmURG5OuZ5F6m/zT6zmSIV74k7VnQz8XQ2JlET6gE9SfJsjetSShojd2IbVuhtaZK2oAZGsmJ3Gdwpux4WquzfrbAUT2ehclfXhHosjYg6gYZXKwyt4ofuCyG7ziCXrY9VNTSJiXqxhZt7dqMGO3CdZN6i8LI6EuRr9suMHyV34uoGIlNwpCaHBf+MTYJoGLFf5tLwzF0wEd+tb5Ej04gyTBUV6QOUFNB6RBZJnEabhiUpELDzdo0TAqw1I5CXJuzpWT4Bv1HiiynbxNBqRNZI3XuV1lYj0qjHEBr81IJHNkjdS5d8rEQnRXPn6j0R3WbDCyV5BMfjHxUKGnfUg0u5kH08x6HMeyB5icUfr0Eg5C372CQdlVIrAr5J3uMpbkpT9JJW7c5AI4fAQkVhaEJEuyZ2QpOyWljRMU/mVnCTGqUwNze8bSRfioyrf3WAkDHdJ/uPlEiysRMTytoaVEVvdKCT8HWc5tcoesLIW/XLGDlPdZzQOiS7NcQSwS1/FJLh1ekeDZqk8q0FI+LA7HPmlFUlEfP0eufHDgF+Ix9N1IrFJd0ezsjqG6WlD3WC97hhgkmdqTMLQEbFWG0JWMomMpfwNa5V4aHbKtS/QqTW0ukBvTiIJlw4qRWKTsXp9qjd67U1Fa7BEueFbOzkDEtGgSzXILNjZqjvffJs6kugDpmjuSbJShotTkXBkntPAH4DRqruiTmT6bC73nmjvj4bFVZFwim9ZPsMaF6dMyelQDoQtwHkuIdLjEobJCp5A5kglw5DAsDPmCH5QAxLvu/zVGJfhNL0YmYlE5KEnkLEtnqR6u2ducdueBz9aQhtl4PUMZyYWzX1cLhKRnemS4F3KYpQth0x0R0YTbWNudcFcG/CYU+zVuXciRmeedYuw7b7enpNdn7Fa2Auy/yWZ7a36u1NtM4Fj3bjBet0tOfmLM+tESkKXuKOGAjLLgY3KIGs0cI8L6sI/qDoTm3N35ipD7hPNnwJPKRl+pvRrhL521d2kPt2RhyOTNSf1ZVcwoVY5mssUAlSLfoXBM2p6jKqBJZT1m5KFFoLKV+tVNmaH/l6rn25Yn6mx8cR+NP3/8S83clOkQWfgVQAAAABJRU5ErkJggg=="/>
                </defs>
                </svg>
            </button>
            <div class="time"><span class="value">3</span><span>S</span></div>
          </div>
          <button class="camera-button">
            <div class="circle"></div>
          </button>
          <div class="autocapture-button">
            <button>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40" viewBox="0 0 80 80" fill="none">
                  <rect width="105" height="100" fill="url(#pattern0_108_41)"/>
                  <defs>
                  <pattern id="pattern0_108_41" patternContentUnits="objectBoundingBox" width="10" height="10">
                  <use xlink:href="#image0_108_41" transform="scale(0.015)"/>
                  </pattern>
                  <image id="image0_108_41" width="50" height="50" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAABy0lEQVR4nO3ZP2sUQRzG8SEWCTYSiG2CltoEbe1Ma+e/V6CIL8BOW1vtfAFa2FqZQonaprSUBLRSjGnkDN7xkeN+R9aQrGd273Y3zLdcbpnny8xzdzOTUiaTyWQmBMt4iE94k7oE5nELrzGwTzdEcAGP8bUQ/hde4hpOpbaCM7iDD/7mIx5gKbUVzOEqnqNXCP8dT7Ha6t7YD7BVCD+ILgw7Md/a3hgFuIFX+F0I8Dn6cK7VvVEhQOO9wSLuY/NAgM14vjiN3hiNew8321Dc/+qN0bhreFEYd6+KyEaNxd0KqeWSd1fwCNsOoYrImNIA8dnVmKXhbI3pxWwOZ3XuiPcWcBvrB8TrF5lGcXExvjC+lYWfmkis3yt4hp+FcX7Es0vHEG9EZKOG4mqDyJhKxW2NSPr3L32/LoGZieAsnmDHFEkzECn2ptMi/ZMiMhNSFgnyjNRMyksryEurZlJeWu1aWv2TMiNvuy6yE39Il7ooMohd5HA3efrYAoWA7/FuhiLbsRlbqRx+Arm6RZq5WlCfSLNXC6qJ7MZJy+VGwk/am5Lirsch3EJqMw7nSxzAnU9dAXsRvhfnV2tHHZG2GlzH3bKrhUwmkxrlDz4vQSEyTTDtAAAAAElFTkSuQmCC"/>
                  </defs>
                  </svg>
            </button>
            <div class="status"><span class="value">OFF</span></div>
          </div>
        </div>
    </div>
  </section>
</section>
  <script>
    const email = "{{ $email }}";
    const limit = {{ $limit }}
    var photoName = {!! json_encode($photoname) !!};
    const request = "{{ $request }}"
  </script>
  <script>
    // Ensure preview is never mirrored. Inject a high-specificity rule.
    (function(){
      try{
        const css = 'video.watch-video, #video.watch-video, .video-container video { -webkit-transform: none !important; transform: none !important; }';
        const st = document.createElement('style');
        st.setAttribute('data-pb-force', 'unmirror');
        st.appendChild(document.createTextNode(css));
        document.head.appendChild(st);
        // also set inline styles on the element if present later
        function ensureVideoUnmirrored(){
          const v = document.querySelector('video.watch-video');
          if (v) {
            v.style.webkitTransform = 'none';
            v.style.transform = 'none';
            v.style.objectFit = 'contain';
          }
        }
        window.addEventListener('load', ensureVideoUnmirrored);
        setTimeout(ensureVideoUnmirrored, 500);
        const obs = new MutationObserver(ensureVideoUnmirrored);
        obs.observe(document.documentElement || document.body, { childList: true, subtree: true });
      }catch(e){/* ignore */}
    })();
  </script>
  <script src="{{ asset('js/camera.js') }}?v={{ @filemtime(public_path('js/camera.js')) ?: time() }}"></script>
  
  <script>
    // Tutorial Implementation for Camera Page
    document.addEventListener('DOMContentLoaded', function() {
      const tutorialHelper = new TutorialHelper();
      
      // Tunggu sebentar agar semua elemen kamera sudah ter-render
      setTimeout(function() {
        if (!tutorialHelper.hasSeenTutorial) {
          tutorialHelper.showWelcomePopup('Halaman Kamera').then(showTutorial => {
            if (showTutorial) {
              const steps = [
                {
                  element: '.video-container',
                  popover: {
                    title: '📷 Preview Kamera',
                    description: 'Ini adalah preview kamera Anda. Posisikan diri Anda dengan baik dan pastikan wajah terlihat jelas. Tersenyum! 😊',
                    position: 'bottom'
                  }
                },
                {
                  element: '#mirrorToggle',
                  popover: {
                    title: '🔄 Flip Camera',
                    description: 'Gunakan tombol ini untuk membalik tampilan kamera (mirror/normal). Pilih yang paling nyaman untuk Anda.',
                    position: 'left'
                  }
                },
                {
                  element: '#cameraFilterSelect',
                  popover: {
                    title: '🎨 Filter',
                    description: 'Pilih filter untuk foto Anda. Tersedia berbagai filter seperti B&W, Sepia, Vintage, dan lainnya.',
                    position: 'right'
                  }
                },
                {
                  element: '.timer-button',
                  popover: {
                    title: '⏱️ Timer/Countdown',
                    description: 'Set timer countdown sebelum foto diambil. Defaultnya 3 detik, beri Anda waktu untuk bersiap!',
                    position: 'top'
                  }
                },
                {
                  element: '.camera-button',
                  popover: {
                    title: '📸 Tombol Ambil Foto',
                    description: 'Klik tombol besar ini untuk mengambil foto. Countdown akan dimulai dan foto akan diambil secara otomatis.',
                    position: 'top'
                  }
                },
                {
                  element: '.autocapture-button',
                  popover: {
                    title: '🔁 Auto Capture',
                    description: 'Aktifkan mode auto capture untuk mengambil semua foto secara otomatis dengan interval tertentu.',
                    position: 'top'
                  }
                },
                {
                  element: '#PhotoList',
                  popover: {
                    title: '🖼️ Daftar Foto',
                    description: 'Semua foto yang sudah Anda ambil akan muncul di sini. Anda bisa mengulang foto tertentu jika diperlukan.',
                    position: 'left'
                  }
                },
                {
                  popover: {
                    title: '✅ Siap Memotret!',
                    description: 'Sekarang Anda siap untuk mengambil foto! Bersenang-senanglah dan buat momen yang tak terlupakan! 🎉📸',
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
      }, 500); // Tunggu 0.5 detik
    });
  </script>
@endsection
