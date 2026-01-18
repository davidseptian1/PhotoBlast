@extends('layouts.app')

@section('content')
  <section style="max-width: 900px; margin: 0 auto; padding: 24px;">
    <h1 style="font-family: 'Bungee';">Admin - Settings Global</h1>

    <p style="margin-top: 6px; opacity: 0.9;">
      Setting di sini berlaku global untuk semua frame.
    </p>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:10px;">
      <a href="{{ route('admin.backgrounds.index') }}" class="formbutton" style="width:auto; padding: 10px 16px; text-decoration:none;">Admin Backgrounds</a>
      <a href="{{ route('admin.frames.index') }}" class="formbutton" style="width:auto; padding: 10px 16px; text-decoration:none;">Admin Frames</a>
      <a href="{{ route('admin.codes.index') }}" class="formbutton" style="width:auto; padding: 10px 16px; text-decoration:none;">Admin Codes</a>
    </div>

    @if(session('message'))
      <p style="padding: 10px 12px; border: 1px solid #cfd4da; border-radius: 10px; margin-top: 14px;">{{ session('message') }}</p>
    @endif

    @if($errors->any())
      <ul style="margin-top:12px;">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    @endif

    <div style="margin-top: 18px; border: 1px solid #cfd4da; border-radius: 14px; padding: 16px;">
      <h2 style="margin: 0 0 10px 0; font-family: 'Bungee';">Settings</h2>

      <form method="POST" action="{{ route('admin.settings.update') }}" style="display:flex; gap:18px; align-items:flex-end; flex-wrap:wrap;">
        @csrf

        <div style="min-width: 280px;">
          <div style="font-family: 'Bungee'; font-size: 14px; margin-bottom: 6px;">Batas Waktu Flow (User)</div>
          <div style="font-size: 13px; opacity: 0.85; margin-bottom: 10px;">
            Timer countdown dari setelah submit kode sampai print. Default sistem: <b>{{ $flow_timeout_default }} menit</b>.
          </div>
          <label style="font-size:12px;">Timeout (menit)
            <input type="number" step="1" min="1" max="60" name="flow_timeout_minutes" value="{{ $flow_timeout_minutes }}" style="width:180px; padding:8px; border-radius:10px;" />
          </label>
        </div>

        <div style="min-width: 320px;">
          <div style="font-family: 'Bungee'; font-size: 14px; margin-bottom: 6px;">Jarak Antar Baris Foto (Global)</div>
          <div style="font-size: 13px; opacity: 0.85; margin-bottom: 10px;">
            Mengatur jarak vertikal antar baris untuk layout grid (layout2/layout3/layout4). Range aman: 0.000–0.060.
            Default sistem: <b>{{ number_format($row_gap_default, 3) }}</b>
          </div>
          <label style="font-size:12px;">Row gap ratio
            <input type="number" step="0.001" min="0.000" max="0.060" name="row_gap_ratio" value="{{ $row_gap_ratio }}" style="width:180px; padding:8px; border-radius:10px;" />
          </label>
          <div style="margin-top: 8px; font-size: 12px; opacity: 0.85;">
            Tip: coba <b>0.006</b>–<b>0.012</b> untuk merapatkan baris.
          </div>
        </div>

        <button type="submit" class="formbutton" style="width:auto; padding: 10px 16px;">Simpan</button>
      </form>
    </div>
  </section>
@endsection
