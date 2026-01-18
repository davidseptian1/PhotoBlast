@extends('layouts.app')
@section('content')
<section class="content">
  <div style="padding:20px;">
    <h2>Admin - Buat Kode Redeem</h2>
    @if(session('message'))
      <div style="color:green;">{{ session('message') }}</div>
    @endif
    @if($errors->any())
      <div style="color:red;">{{ $errors->first() }}</div>
    @endif
    <form action="{{ route('admin.codes.store') }}" method="post" style="margin-top:10px;">
      @csrf
      <input type="text" name="code" placeholder="Masukkan kode" required style="padding:6px; width:250px;">
      <select name="transaction_id" style="padding:6px; margin-left:8px;">
        <option value="">-- Tidak ada transaksi --</option>
        @foreach($transactions as $t)
          <option value="{{ $t->id }}">{{ $t->id }} - {{ $t->email ?? 'no-email' }} ({{ $t->status }})</option>
        @endforeach
      </select>
      <button type="submit" style="padding:6px 10px; margin-left:8px;">Buat</button>
    </form>

    <h3 style="margin-top:20px;">Daftar Kode</h3>
    <table border="1" cellpadding="6" style="border-collapse:collapse; margin-top:8px;">
      <thead>
        <tr><th>ID</th><th>Code</th><th>Status</th><th>Transaction</th></tr>
      </thead>
      <tbody>
        @foreach($codes as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->code }}</td>
            <td>{{ $c->status }}</td>
            <td>{{ $c->transaction ? ($c->transaction->id . ' - ' . ($c->transaction->email ?? 'no-email')) : 'NULL' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
@endsection
