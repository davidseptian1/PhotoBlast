<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Http\Requests\StoreCodeRequest;
use App\Http\Requests\UpdateCodeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\AppSetting;
use App\Models\FlowRun;
use DB;
class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('redeem');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'nullable|string',
        ]);

        $codeValue = (string) ($request->code ?: session('flow_code') ?: session('code'));
        if ($codeValue === '') {
            return redirect()->route('redeem.index')->with('message', 'Kode tidak ditemukan.');
        }

        // deteksi code apakah tersedia dalam database atau tidak
        $code = Code::whereRaw('BINARY `code` = ?', [$codeValue])->first();
        if($code && $code->status == 'ready') {
            // simpan data code ke session
            Session::put('code', $code->code);

            if ($code->transaction) {
                $code->transaction->email = (string) $request->email;
                $code->transaction->save();
            }

            $flowId = (int) session('flow_run_id', 0);
            if ($flowId > 0) {
                FlowRun::where('id', $flowId)->update([
                    'email' => (string) $request->email,
                ]);
            }

            // Start global flow timer (max 8 minutes until print)
            $mins = (int) (AppSetting::getString('tempcollage.flow_timeout_minutes', '8') ?: '8');
            $mins = max(1, min(60, $mins));
            Session::put('pb_flow_started_at', now()->timestamp);
            Session::put('pb_flow_deadline', now()->addMinutes($mins)->timestamp);

            // redirect ke halaman pemilihan template
            return redirect()->route('tempcollage.index');

        }else{

            // redirect ke halaman input code redeem
            return redirect()->route('redeem.index')->with('message', "Kode yang anda inputkan salah!" );

        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Code $code)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Code $code)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCodeRequest $request, Code $code)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Code $code)
    {

    }
}
