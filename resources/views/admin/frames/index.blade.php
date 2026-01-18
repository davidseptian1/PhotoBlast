@extends('layouts.app')

@section('content')
  <section style="max-width: 1100px; margin: 0 auto; padding: 24px;">
    <h1 style="font-family: 'Bungee';">Admin - Frame per Layout</h1>

    <p style="margin-top: 6px; opacity: 0.9;">
      Target saat ini: <b>{{ $layout_meta['label'] ?? $layout }}</b>. Frame yang kamu upload/simpan di sini akan muncul di halaman <b>/listphoto</b> ketika user memilih layout ini.
    </p>

    @if(session('message'))
      <p style="padding: 10px 12px; border: 1px solid #cfd4da; border-radius: 10px;">{{ session('message') }}</p>
    @endif

    <form method="GET" action="{{ route('admin.frames.index') }}" style="display:flex; gap:12px; align-items:center; margin: 18px 0; flex-wrap:wrap;">
      <label for="layout"><b>Pilih Layout Target</b></label>
      <select id="layout" name="layout" style="padding: 10px 12px; border-radius: 10px;">
        @foreach($layouts as $l)
          @php
            $num = (int) str_replace('layout', '', $l);
            $cnt = match ($num) { 1 => 1, 2 => 4, 3 => 4, 4 => 6, default => 4 };
          @endphp
          <option value="{{ $l }}" @if($layout === $l) selected @endif>Layout {{ $num }} ({{ $cnt }} foto)</option>
        @endforeach
      </select>
      @if(!empty($selected_photo))
        <input type="hidden" name="photo" value="{{ $selected_photo }}" />
      @endif
      @if(!empty($selected_frame))
        <input type="hidden" name="frame" value="{{ $selected_frame }}" />
      @endif
      <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Tampilkan</button>
    </form>

    <h2 style="margin-top: 18px; font-family: 'Bungee';">1) Upload Frame Baru</h2>
    <form method="POST" action="{{ route('admin.frames.store') }}" enctype="multipart/form-data" style="border: 1px solid #cfd4da; border-radius: 14px; padding: 16px;">
      @csrf
      <input type="hidden" name="layout" value="{{ $layout }}" />
      <p style="margin: 0 0 10px; opacity: 0.9;">Frame akan disimpan ke <b>storage/app/public/frames/{{ $layout }}</b> dan bisa diakses via <b>/storage/frames/{{ $layout }}</b>.</p>
      <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <input type="file" name="frames[]" accept="image/png,image/jpeg,image/webp" multiple />
        <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Upload ke {{ $layout }}</button>
      </div>
      @if($errors->any())
        <ul style="margin-top:12px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      @endif
    </form>

    <h2 style="margin-top: 24px; font-family: 'Bungee';">Frames Tersimpan ({{ $layout }})</h2>

    @if(empty($frames))
      <p>Belum ada frame untuk layout ini.</p>
    @else
      <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;">
        @foreach($frames as $f)
          <div style="border: 1px solid #cfd4da; border-radius: 14px; padding: 10px;">
            <img src="{{ $f['url'] }}" alt="Frame" style="width:100%; height:140px; object-fit:contain; display:block;" />

            <form method="POST" action="{{ route('admin.frames.settings') }}" style="margin-top: 10px; display:grid; grid-template-columns: 1fr 1fr; gap: 8px;">
              @csrf
              <input type="hidden" name="layout" value="{{ $layout }}" />
              <input type="hidden" name="id" value="{{ $f['id'] }}" />

              <label style="font-size:12px;">Photo scale
                <input type="number" step="0.01" min="0.50" max="1.50" name="photo_scale" value="{{ $f['photo_scale'] ?? 1.0 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>
              <label style="font-size:12px;">Pad ratio
                <input type="number" step="0.005" min="0.00" max="0.20" name="photo_pad_ratio" value="{{ $f['photo_pad_ratio'] ?? 0.07 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>
              <label style="font-size:12px;">Offset X
                <input type="number" step="0.01" min="-0.30" max="0.30" name="photo_offset_x" value="{{ $f['photo_offset_x'] ?? 0.0 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>
              <label style="font-size:12px;">Offset Y
                <input type="number" step="0.01" min="-0.30" max="0.30" name="photo_offset_y" value="{{ $f['photo_offset_y'] ?? 0.0 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>

              <label style="font-size:12px;">Grid gap (jarak antar slot)
                <input type="number" step="0.001" min="0.000" max="0.060" name="grid_gap_ratio" value="{{ $f['grid_gap_ratio'] ?? 0.020 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>

              <label style="font-size:12px;">Rapatkan baris (layout2)
                <input type="number" step="0.05" min="0.00" max="1.00" name="row_anchor_ratio" value="{{ $f['row_anchor_ratio'] ?? 1.000 }}" style="width:100%; padding:8px; border-radius:10px;" />
              </label>

              <button type="submit" class="formbutton" style="grid-column: 1 / -1; width:auto; padding: 8px 12px;">Simpan Pengaturan Foto</button>
              <small style="grid-column: 1 / -1; opacity:0.85;">Tip: kecilkan <b>Grid gap</b> untuk mendekatkan kolom/baris; <b>Rapatkan baris</b> (0–1) untuk mengurangi jarak tengah (khusus layout2); naikkan <b>Pad ratio</b> untuk mengecilkan foto; <b>Scale</b> &gt; 1 bisa memperbesar (berpotensi crop).</small>
            </form>

            <div style="display:flex; gap:10px; justify-content:space-between; align-items:center; margin-top:10px;">
              <small style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width: 120px;">{{ $f['name'] }}</small>
              <div style="display:flex; gap:10px; align-items:center;">
                <a href="{{ route('admin.frames.index', ['layout' => $layout, 'photo' => $selected_photo ?? '', 'frame' => $f['id']]) }}" class="formbutton" style="width:auto; padding: 8px 12px;">Preview</a>
                <form method="POST" action="{{ route('admin.frames.destroy') }}" onsubmit="return confirm('Hapus frame ini?')">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="layout" value="{{ $layout }}" />
                  <input type="hidden" name="id" value="{{ $f['id'] }}" />
                  <button type="submit" class="formbutton" style="width:auto; padding: 8px 12px;">Delete</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <h2 style="margin-top: 24px; font-family: 'Bungee';">2) Simpan dari Foto Template (Jadikan Frame)</h2>
    @if(empty($photos ?? []))
      <p>Tidak ada file gambar di folder template.</p>
    @else
      <p style="margin-bottom: 10px; opacity: 0.9;">Step: klik salah satu foto template di bawah → lalu klik tombol “Simpan sebagai Frame”.</p>

      <form method="POST" action="{{ route('admin.frames.fromTemplate') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom: 12px;">
        @csrf
        <input type="hidden" name="layout" value="{{ $layout }}" />
        <input type="hidden" name="template_file" value="{{ $selected_photo ?? '' }}" />
        <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;" @if(empty($selected_photo)) disabled @endif>
          Simpan sebagai Frame ke {{ $layout }}
        </button>
        <small>Catatan: file akan <b>dicopy</b> dari template ke folder frames (bukan dipindah).</small>
      </form>

      <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px;">
        @foreach($photos as $p)
          <a href="{{ route('admin.frames.index', ['layout' => $layout, 'photo' => $p['file'], 'frame' => $selected_frame ?? '']) }}" style="display:block; border: 2px solid #cfd4da; border-radius: 12px; padding: 6px; @if(($selected_photo ?? '') === $p['file']) outline: 4px solid #cfd4da; @endif">
            <img src="{{ $p['url'] }}" alt="Photo" style="width:100%; height:120px; object-fit:cover; display:block; border-radius: 8px;" />
          </a>
        @endforeach
      </div>
    @endif

    <h2 style="margin-top: 24px; font-family: 'Bungee';">Preview (Foto + Frame)</h2>
    <div style="border: 1px solid #cfd4da; border-radius: 14px; padding: 14px; max-width: 520px;">
      @if(!empty($preview_photo_url))
        <div style="position: relative; width: 480px; max-width: 100%; aspect-ratio: 1 / 1; margin: 0 auto; border-radius: 14px; overflow: hidden; background: rgba(255,255,255,0.06);">
          <img src="{{ $preview_photo_url }}" alt="Preview photo" style="position:absolute; inset:0; width:100%; height:100%; object-fit:contain;" />
          @if(!empty($preview_frame_url))
            <img src="{{ $preview_frame_url }}" alt="Preview frame" style="position:absolute; inset:0; width:100%; height:100%; object-fit:contain;" />
          @endif
        </div>
        <p style="margin-top: 10px;">
          Layout target: <b>{{ $layout_meta['label'] ?? $layout }}</b><br>
          Foto template: <b>{{ $selected_photo }}</b><br>
          Frame preview: <b>{{ $selected_frame ? ('#'.$selected_frame) : 'none' }}</b>
        </p>
      @else
        <p>Pilih foto dulu untuk melihat preview.</p>
      @endif
    </div>
  </section>
@endsection
