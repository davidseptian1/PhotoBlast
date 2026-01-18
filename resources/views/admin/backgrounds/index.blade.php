@extends('layouts.app')

@section('content')
  <section style="max-width: 1100px; margin: 0 auto; padding: 24px;">
    <h1 style="font-family: 'Bungee';">Admin - Background Halaman</h1>

    <p style="margin-top: 6px; opacity: 0.9;">
      Atur background global (untuk semua halaman) atau set per halaman.
      Jika background per-halaman kosong, halaman akan otomatis pakai background global (kalau ada), kalau tidak maka pakai default bawaan.
    </p>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:10px;">
      <a href="{{ route('admin.codes.index') }}" class="formbutton" style="width:auto; padding: 10px 16px; text-decoration:none;">Admin Codes</a>
      <a href="{{ route('admin.frames.index') }}" class="formbutton" style="width:auto; padding: 10px 16px; text-decoration:none;">Admin Frames</a>
    </div>

    @if(session('message'))
      <p style="padding: 10px 12px; border: 1px solid #cfd4da; border-radius: 10px; margin-top: 14px;">{{ session('message') }}</p>
    @endif

    <h2 style="margin-top: 22px; font-family: 'Bungee';">0) Pengaturan Layout (Pilih Layout)</h2>
    <div style="border: 1px solid #cfd4da; border-radius: 14px; padding: 16px;">
      <p style="margin-top: 0; opacity: 0.9;">
        Jika layout dimatikan, tombol di halaman /tempcollage akan menampilkan pop up "Coming Soon".
      </p>

      <form method="POST" action="{{ route('admin.backgrounds.tempcollageLayouts') }}" style="display:flex; gap:18px; flex-wrap:wrap; align-items:flex-end;">
        @csrf
        <label style="display:flex; gap:10px; align-items:center;">
          <input type="checkbox" name="layout1" value="1" @if(($layout_enabled[1] ?? false)) checked @endif />
          <span><b>Layout 1</b> aktif</span>
        </label>
        <label style="display:flex; gap:10px; align-items:center;">
          <input type="checkbox" name="layout2" value="1" @if(($layout_enabled[2] ?? false)) checked @endif />
          <span><b>Layout 2</b> aktif</span>
        </label>
        <label style="display:flex; gap:10px; align-items:center;">
          <input type="checkbox" name="layout3" value="1" @if(($layout_enabled[3] ?? false)) checked @endif />
          <span><b>Layout 3</b> aktif</span>
        </label>
        <label style="display:flex; gap:10px; align-items:center;">
          <input type="checkbox" name="layout4" value="1" @if(($layout_enabled[4] ?? false)) checked @endif />
          <span><b>Layout 4</b> aktif</span>
        </label>
        <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Simpan</button>
      </form>
    </div>

    <h2 style="margin-top: 22px; font-family: 'Bungee';">1) Background Global</h2>
    <div style="border: 1px solid #cfd4da; border-radius: 14px; padding: 16px;">
      <form method="POST" action="{{ route('admin.backgrounds.global') }}" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        @csrf
        <input type="file" name="background" accept="image/png,image/jpeg,image/webp" required />
        <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Simpan Global</button>
      </form>

      <div style="margin-top: 14px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
        <div style="min-width: 240px;">
          <b>Preview global</b>
          <div style="margin-top: 8px; border: 1px solid #cfd4da; border-radius: 12px; overflow: hidden; width: 240px; height: 140px; background: rgba(0,0,0,0.06);">
            @if(!empty($global_url))
              <img src="{{ $global_url }}" alt="Global background" style="width:100%; height:100%; object-fit:cover; display:block;" />
            @else
              <div style="display:flex; align-items:center; justify-content:center; height:100%; opacity:0.75;">Belum ada</div>
            @endif
          </div>
        </div>

        <form method="POST" action="{{ route('admin.backgrounds.applyGlobal') }}" onsubmit="return confirm('Hapus semua override per-halaman dan pakai background global untuk semua?')">
          @csrf
          <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Pakai Global untuk Semua Halaman</button>
        </form>
      </div>

      @if($errors->any())
        <ul style="margin-top:12px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      @endif
    </div>

    <h2 style="margin-top: 26px; font-family: 'Bungee';">2) Background Per Halaman</h2>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
      @foreach($pages as $p)
        <div style="border: 1px solid #cfd4da; border-radius: 14px; padding: 12px;">
          <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
            <div>
              <b>{{ $p['label'] }}</b>
              <div style="font-size:12px; opacity:0.8; margin-top:2px;">Key: {{ $p['key'] }}</div>
            </div>
            @if(!empty($p['custom_url']))
              <span style="font-size:12px; padding:4px 8px; border:1px solid #cfd4da; border-radius:999px;">custom</span>
            @else
              <span style="font-size:12px; padding:4px 8px; border:1px solid #cfd4da; border-radius:999px; opacity:0.75;">default/global</span>
            @endif
          </div>

          <div style="margin-top: 10px; border: 1px solid #cfd4da; border-radius: 12px; overflow: hidden; width: 100%; height: 140px; background: rgba(0,0,0,0.06);">
            <img src="{{ $p['effective_url'] }}" alt="Background preview" style="width:100%; height:100%; object-fit:cover; display:block;" />
          </div>

          <form method="POST" action="{{ route('admin.backgrounds.page') }}" enctype="multipart/form-data" style="margin-top: 10px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            @csrf
            <input type="hidden" name="page_key" value="{{ $p['key'] }}" />
            <input type="file" name="background" accept="image/png,image/jpeg,image/webp" required />
            <button type="submit" class="formbutton" style="width:auto; padding: 8px 12px;">Simpan</button>
          </form>

          <form method="POST" action="{{ route('admin.backgrounds.clear') }}" style="margin-top: 8px;" onsubmit="return confirm('Hapus override background halaman ini?')">
            @csrf
            <input type="hidden" name="page_key" value="{{ $p['key'] }}" />
            <button type="submit" class="formbutton" style="width:auto; padding: 8px 12px;">Reset (pakai global/default)</button>
          </form>
        </div>
      @endforeach
    </div>
  </section>
@endsection
