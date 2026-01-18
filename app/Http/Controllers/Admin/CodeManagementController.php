<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Code;
use App\Models\Transaction;

class CodeManagementController extends Controller
{
    public function index()
    {
        $codes = Code::orderBy('id', 'desc')->get();
        $transactions = Transaction::orderBy('id', 'desc')->get();
        return view('admin.codes', compact('codes', 'transactions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:codes,code',
            'transaction_id' => 'nullable|exists:transactions,id',
        ]);

        Code::create([
            'code' => $request->code,
            'status' => 'ready',
            'transaction_id' => $request->transaction_id ?: null,
        ]);

        return redirect()->route('admin.codes.index')->with('message', 'Kode berhasil dibuat');
    }
}
