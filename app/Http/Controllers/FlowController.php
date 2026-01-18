<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Code;
use Illuminate\Support\Str;
use App\Models\FlowRun;

class FlowController extends Controller
{
    public function start()
    {
        return view('flow.start');
    }

    public function begin(Request $request)
    {
        // Start a brand-new flow run (persisted in DB) and reset flow-related session
        session()->forget([
            'flow_run_id',
            'flow_id',
            'flow_started_at',
            'flow_package',
            'flow_code',
            'code',
            'layout_key',
            'layout_count',
            'frame_file',
            'temp_id',
        ]);

        $flow = FlowRun::create([
            'uuid' => (string) Str::uuid(),
            'status' => 'started',
            'started_at' => now(),
        ]);

        session([
            'flow_run_id' => $flow->id,
            'flow_id' => $flow->uuid,
            'flow_started_at' => $flow->started_at?->toISOString(),
        ]);

        return redirect()->route('flow.package');
    }

    public function package()
    {
        return view('flow.package');
    }

    public function choosePackage(Request $request)
    {
        $request->validate(['package' => 'required|in:30000,40000']);

        if ((int) $request->package === 40000) {
            return back()->with('message', 'Paket A3 masih Coming Soon. Silakan pilih ukuran A6 dulu ya.');
        }

        $amount = (int) $request->package;
        session(['flow_package' => $amount]);

        $flowId = (int) session('flow_run_id', 0);
        if ($flowId > 0) {
            FlowRun::where('id', $flowId)->update([
                'package_amount' => $amount,
            ]);
        }
        return redirect()->route('flow.payment');
    }

    public function payment()
    {
        $amount = session('flow_package', 30000);
        return view('flow.payment', compact('amount'));
    }

    public function paid(Request $request)
    {
        // simulate payment: create transaction + code and put in session (idempotent per flow_run)
        $flowId = (int) session('flow_run_id', 0);
        if ($flowId <= 0) {
            return redirect()->route('flow.start');
        }

        $flow = FlowRun::with(['code', 'transaction'])->find($flowId);
        if ($flow && $flow->code && $flow->transaction && $flow->code->status === 'ready') {
            session(['code' => $flow->code->code, 'flow_code' => $flow->code->code]);
            return redirect()->route('flow.success');
        }

        $amount = (int) session('flow_package', 30000);

        // email wajib di tabel, isi placeholder sampai user input di halaman sukses
        $transaction = Transaction::create([
            'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            'amount' => $amount,
            'status' => 'settlement',
            'method' => 'sample',
            'email' => 'pending@example.com'
        ]);

        // generate code (5 chars) unique
        do {
            $codeStr = strtoupper(Str::random(5));
        } while (Code::where('code', $codeStr)->exists());

        $code = Code::create([
            'code' => $codeStr,
            'status' => 'ready',
            'transaction_id' => $transaction->id,
        ]);

        if ($flow) {
            $flow->transaction_id = $transaction->id;
            $flow->code_id = $code->id;
            $flow->status = 'paid';
            $flow->package_amount = $amount;
            $flow->save();
        }

        session(['code' => $code->code, 'flow_code' => $code->code]);

        return redirect()->route('flow.success');
    }

    public function success()
    {
        $flowId = (int) session('flow_run_id', 0);
        if ($flowId <= 0) {
            return redirect()->route('flow.start');
        }

        $flow = FlowRun::with(['code', 'transaction'])->find($flowId);
        $code = $flow?->code?->code ?: session('flow_code');
        if (!$code) {
            return redirect()->route('flow.start');
        }
        $transaction = $flow?->transaction ?: Code::where('code', $code)->first()?->transaction;
        return view('flow.success', ['code' => $code, 'transaction' => $transaction]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $codeValue = (string) (session('flow_code') ?: session('code'));
        $code = $codeValue ? Code::where('code', $codeValue)->first() : null;
        if (!$code || !$code->transaction) {
            return redirect()->route('redeem.index')->with('message', 'Kode tidak ditemukan.');
        }

        $newEmail = (string) $request->email;
        $oldEmail = (string) $code->transaction->email;

        // If user updates email after photos already exist, move the folder so list/print keeps working.
        if ($oldEmail !== '' && $oldEmail !== $newEmail) {
            $from = 'public/'.$oldEmail;
            $to = 'public/'.$newEmail;

            try {
                // only move when source exists and destination doesn't, to avoid overwriting
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($from)
                    && !\Illuminate\Support\Facades\Storage::disk('local')->exists($to)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->move($from, $to);
                }
            } catch (\Throwable $e) {
                // ignore move failure; email will still be saved
            }
        }

        $code->transaction->email = $newEmail;
        $code->transaction->save();

        $flowId = (int) session('flow_run_id', 0);
        if ($flowId > 0) {
            FlowRun::where('id', $flowId)->update([
                'email' => $newEmail,
            ]);
        }
        return redirect()->back()->with('message', 'Email disimpan.');
    }
}
