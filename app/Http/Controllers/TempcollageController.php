<?php

namespace App\Http\Controllers;

use App\Models\Tempcollage;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTempcollageRequest;
use App\Http\Requests\UpdateTempcollageRequest;
use Illuminate\Support\Facades\Session;
use App\Models\Code;
use App\Models\FlowRun;
use App\Models\AppSetting;
use App\Models\Frame;
use Illuminate\Support\Str;

class TempcollageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        if(session()->has('code')){
            $code = Code::where('code', session('code'))->first(); 
            if($code && $code->status == 'ready' && $code->transaction) {
                $layoutEnabled = [
                    1 => AppSetting::getString('tempcollage.layout1.enabled', '0') === '1',
                    2 => AppSetting::getString('tempcollage.layout2.enabled', '1') === '1',
                    3 => AppSetting::getString('tempcollage.layout3.enabled', '1') === '1',
                    4 => AppSetting::getString('tempcollage.layout4.enabled', '0') === '1',
                ];

                $layoutHidden = [
                    1 => AppSetting::getString('tempcollage.layout1.hidden', '0') === '1',
                    2 => AppSetting::getString('tempcollage.layout2.hidden', '0') === '1',
                    3 => AppSetting::getString('tempcollage.layout3.hidden', '0') === '1',
                    4 => AppSetting::getString('tempcollage.layout4.hidden', '0') === '1',
                ];

                // Get frames for each layout to show in preview modal
                $framesByLayout = [];
                foreach (['layout1', 'layout2', 'layout3', 'layout4'] as $layoutKey) {
                    $framesByLayout[$layoutKey] = Frame::query()
                        ->where('layout_key', $layoutKey)
                        ->orderByDesc('id')
                        ->get(['id', 'file', 'original_name'])
                        ->map(fn ($f) => [
                            'id' => (int) $f->id,
                            'file' => (string) $f->file,
                            'url' => '/'.ltrim((string) $f->file, '/'),
                            'name' => (string) ($f->original_name ?: basename($f->file)),
                        ])
                        ->all();
                }

                return view('template', [
                    'layout_enabled' => $layoutEnabled,
                    'layout_hidden' => $layoutHidden,
                    'frames_by_layout' => $framesByLayout,
                ]);
            }
            // if code exists but has no transaction, show a helpful message on redeem
            if ($code && $code->status == 'ready' && !$code->transaction) {
                return redirect()->route('redeem.index')->with('message', 'Kode belum terkait transaksi. Hubungi admin.');
            }
        }
        return redirect()->route('redeem.index');
    }

    public function chooseLayout(Request $request)
    {
        $request->validate([
            'layout' => 'required|in:1,2,3,4',
            // Optional override used for layout2 variants (e.g., 2x2 frame => 4 photos)
            'count' => 'nullable|in:2,4',
        ]);

        $layout = (int) $request->layout;

        $hidden = match ($layout) {
            1 => AppSetting::getString('tempcollage.layout1.hidden', '0') === '1',
            2 => AppSetting::getString('tempcollage.layout2.hidden', '0') === '1',
            3 => AppSetting::getString('tempcollage.layout3.hidden', '0') === '1',
            4 => AppSetting::getString('tempcollage.layout4.hidden', '0') === '1',
            default => false,
        };

        if ($hidden) {
            return redirect()->route('tempcollage.index')->with('message', 'Layout disembunyikan');
        }

        $enabled = match ($layout) {
            1 => AppSetting::getString('tempcollage.layout1.enabled', '0') === '1',
            2 => AppSetting::getString('tempcollage.layout2.enabled', '1') === '1',
            3 => AppSetting::getString('tempcollage.layout3.enabled', '1') === '1',
            4 => AppSetting::getString('tempcollage.layout4.enabled', '0') === '1',
            default => false,
        };

        if (!$enabled) {
            return redirect()->route('tempcollage.index')->with('message', 'Coming Soon');
        }

        $layout2Count = 2;
        if ($layout === 2) {
            $layout2Count = ((int) $request->input('count', 2) === 4) ? 4 : 2;
        }

        $layoutConfig = match ($layout) {
            1 => ['key' => 'layout1', 'count' => 1],
            2 => ['key' => 'layout2', 'count' => $layout2Count],
            // layout3 uses 2 columns × 3 rows => 6 photos
            3 => ['key' => 'layout3', 'count' => 6],
            4 => ['key' => 'layout4', 'count' => 6],
        };

        Session::put('layout_key', $layoutConfig['key']);
        Session::put('layout_count', $layoutConfig['count']);
        Session::forget(['frame_file', 'frame_id']);

        // Persist layout choice into DB (flow_runs). Create a flow run if user came from redeem/tempcollage.
        $flowId = (int) session('flow_run_id', 0);
        if ($flowId <= 0) {
            $data = [
                'uuid' => (string) Str::uuid(),
                'status' => 'started',
                'started_at' => now(),
            ];

            if (session()->has('code')) {
                $code = Code::where('code', session('code'))->first();
                if ($code && $code->transaction) {
                    $data['code_id'] = $code->id;
                    $data['transaction_id'] = $code->transaction->id;
                    $data['email'] = $code->transaction->email;
                }
            }

            $flow = FlowRun::create($data);
            session([
                'flow_run_id' => $flow->id,
                'flow_id' => $flow->uuid,
                'flow_started_at' => $flow->started_at?->toISOString(),
            ]);
            $flowId = $flow->id;
        }

        if ($flowId > 0) {
            FlowRun::where('id', $flowId)->update([
                'layout_key' => $layoutConfig['key'],
                'layout_count' => $layoutConfig['count'],
                'frame_id' => null,
                'frame_file' => null,
            ]);
        }

        return redirect()->route('camera');
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
    public function store(StoreTempcollageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tempcollage $tempcollage)
    {
        if(session()->has('code')){
            $code = Code::where('code', session('code'))->first(); 
            if($code && $code->status == 'ready' && $code->transaction) {
                // masukkan ke sesi temp_id yang bernilai id tabel dari template foto
                Session::put('temp_id', $tempcollage->id);

                // redirect ke halaman kamera
                return redirect()->route('camera');
            }
            if ($code && $code->status == 'ready' && !$code->transaction) {
                return redirect()->route('redeem.index')->with('message', 'Kode belum terkait transaksi. Hubungi admin.');
            }
        }
        return redirect()->route('redeem.index');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tempcollage $tempcollage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTempcollageRequest $request, Tempcollage $tempcollage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tempcollage $tempcollage)
    {
        //
    }
}
