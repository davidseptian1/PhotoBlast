<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Tempcollage;
use App\Models\Transaction;
use App\Models\FlowRun;
use App\Models\Frame;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Mail\Mailables\Attachment;
use App\Mail\SendPhotoAndVideo;
use Exception;
use ZipArchive;

class PhotoblastController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(){

        // show start flow page instead of redirecting to redeem
        return redirect()->route('flow.start');

    }

    public function camera(){
        $code = $this->getReadyCode();
        if ($code) {
            $this->ensureFlowRunInSessionForCode($code);
            $this->ensureLayoutInSessionFromFlowRun();
            $layoutCount = (int) session('layout_count', 0);
            if ($layoutCount <= 0) {
                return redirect()->route('tempcollage.index');
            }
            return view('camera', [
                'email' => $code->transaction->email,
                'limit' => $layoutCount,
                'photoname' => [],
                'request' => "photo",
            ]);
        }
        return redirect()->route('redeem.index');
    }

    // public function processpayment(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'amount' => 'required'
    //     ]);

    //     if($validator->failed()){
    //         return response()->json(data: $validator->errors(), status: 400);
    //     }
    //     $transaction = Transaction::create([
    //         'invoice_number' => 'adfdaf',
    //         'amount' => $request->amount,
    //         'status' => 'CREATED'
    //     ]);

    //     $resp = Http::withHeaders([
    //         'Accept' => 'application/json',
    //         'Content-Type' => 'application/json',
    //     ])->withBasicAuth(env('MIDTRANS_SERVER_KEY'),'')
    //       ->post('https://api.sandbox.midtrans.com/v2/charge', [
    //         'payment_type' => 'qris',
    //         'transaction_details' => [
    //             'order_id' => 'alig'.$transaction->id,
    //             'gross_amount' => $transaction->amount
    //         ]
    //     ]);
    //     if($resp->status() == 201 || $resp->status() == 200) {
    //         $order_id = $resp->json('order_id');
    //         $status_code = $resp->json('status_code');
    //         $gross_amount = $resp->json('gross_amount');

    //         $actions = $resp->json('actions')[0];
    //         if(empty($actions)) {
    //             return response()->json(['message' => $resp['status_message']], 500);
    //         }

    //         return view('image', [
    //             'qrcode'=>$actions['url'],
    //             'order_id' => $order_id,
    //             'status_code' => $status_code,
    //             'gross_amount' => $gross_amount,
    //         ]);
    //     }
    //     return response()->json(['message' => $resp->body()], 500);
    // }

    // public function verifyPayment(Request $request){
    //     $orderId = $request->order_id;
    //     $id = preg_replace('/[a-zA-Z]+/', '', $orderId);

    //     $resp = Http::withHeaders([
    //         'Accept' => 'application/json',
    //     ])->withBasicAuth(env('MIDTRANS_SERVER_KEY'),'')
    //       ->get("https://api.sandbox.midtrans.com/v2/$orderId/status");
    //     $status = $resp->json('transaction_status');

    //     $transaction = Transaction::find($id);
    //     $transaction->status = $status;
    //     $transaction->save();

    //     if($transaction->status == 'settlement'){
    //         return redirect()->route('camera');
    //     } else{
    //         return redirect()->route('home');
    //     }
    // }

    public function saveVideo(Request $request){
        if ($request->hasFile('video')) {
            $videoName = $request->file('video')->getClientOriginalName();
            $videoPath = $request->file('video')->storeAs($request->email.'/video', $videoName, 'public');
            return response()->json(['message' => 'Video berhasil diunggah'], 200);
        } else {
            return response()->json(['message' => 'Tidak ada video yang dikirimkan'], 400);
        }

        return response()->json([
            'error' => 'gagal'
        ], 419);
    }

    public function savePhoto(Request $request){
        $code = $this->getReadyCode();
        if ($code) {
            try {
                $fileData = base64_decode($request->data);
                $fileName = 'public/'.$code->transaction->email.'/photo/'.$request->name_photo;
                Storage::disk('local')->put($fileName, $fileData);
                return response()->json(['message' => "berhasil"]);
            } catch(Exception $e) {
                return response()->json(['error' => $e], 404);
            }
        }
        return redirect()->route('redeem.index');
    }

    // Save/overwrite the composed collage image into a stable public file for printing/email.
    public function saveCollage(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'photo' => 'required|string',
        ]);

        $email = (string) $request->input('email');
        $base64 = (string) $request->input('photo');

        $binary = base64_decode($base64);
        if ($binary === false) {
            return response()->json(['ok' => false, 'message' => 'Invalid photo data'], 400);
        }

        // Optional: light sharpening to make A6 prints look crisper.
        // This only affects the saved collage (not the raw photos).
        $binary = $this->applyCollageSharpening($binary);

        $path = $email.'/photo/collage/collage.jpg';
        Storage::disk('public')->put($path, $binary);

        return response()->json([
            'ok' => true,
            'url' => '/storage/'.ltrim($path, '/').'?t='.time(),
        ]);
    }

    // Open Windows "Print Pictures" dialog for the saved collage image.
    // NOTE: This only works reliably when PHP is running under an interactive user session
    // (e.g. Laragon / php artisan serve). If running as a Windows Service (IIS/Apache service),
    // Windows will block UI from appearing (Session 0 isolation).
    public function openPrintPictures(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'path' => 'sometimes|string',
        ]);

        if (PHP_OS_FAMILY !== 'Windows') {
            return response()->json(['ok' => false, 'message' => 'Windows only'], 400);
        }

        $email = (string) $request->input('email');

        // Optional: allow caller to pass an explicit absolute path to the collage file.
        // This is validated to ensure it exists and is inside the application's
        // storage `app/public` directory to avoid arbitrary file access.
        $absolute = null;
        if ($request->filled('path')) {
            $candidate = (string) $request->input('path');
            $resolved = realpath($candidate);
            $storagePublic = realpath(storage_path('app/public')) ?: storage_path('app/public');
            if ($resolved && is_file($resolved) && str_starts_with($resolved, $storagePublic)) {
                $absolute = $resolved;
            } else {
                Log::warning('openPrintPictures received invalid path override', [
                    'input' => $candidate,
                    'resolved' => $resolved,
                ]);
            }
        }

        if ($absolute === null) {
            $relative = $email.'/photo/collage/collage.jpg';
            $absolute = storage_path('app/public/'.$relative);
        }

        if (!is_file($absolute)) {
            return response()->json(['ok' => false, 'message' => 'Collage file not found'], 404);
        }

        $psFileLiteral = str_replace("'", "''", $absolute);

        // Most reliable way to trigger the Windows "Print Pictures" wizard is via the Shell "print" verb
        // (same as right-click image -> Print). We run it in a hidden STA PowerShell and confirm that a
        // "Print Pictures" window appears; otherwise we return ok=false so the client can fall back.
        $ps = <<<'PS'
    $ErrorActionPreference = 'Stop'
    $f = '__PB_FILE__'

    Add-Type @"
    using System;
    using System.Runtime.InteropServices;
    public static class PBWin {
        [DllImport("user32.dll")] public static extern IntPtr GetForegroundWindow();
        [DllImport("user32.dll")] public static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint lpdwProcessId);
        [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd);
    }
    "@

# Invoke Shell print verb
$folder = Split-Path -LiteralPath $f
$name = Split-Path -Leaf -LiteralPath $f
$sh = New-Object -ComObject Shell.Application
$ns = $sh.Namespace($folder)
if ($ns -eq $null) { exit 10 }
$item = $ns.ParseName($name)
if ($item -eq $null) { exit 11 }
$item.InvokeVerb('print')

# Try to detect the wizard window title (English + Indonesian), then bring it to front.
$ok = $false
$ws = $null
try { $ws = New-Object -ComObject WScript.Shell } catch {}

for ($i = 0; $i -lt 20; $i++) {
    Start-Sleep -Milliseconds 150
    try {
        $p = Get-Process | Where-Object {
            $_.MainWindowHandle -ne 0 -and $_.MainWindowTitle -and (
                $_.MainWindowTitle -like '*Print Pictures*' -or
                $_.MainWindowTitle -like '*Cetak Gambar*'
            )
        } | Select-Object -First 1

        if ($p) {
            # Try to foreground it
            try {
                if ($ws) { $null = $ws.AppActivate($p.Id) }
            } catch {}
            try {
                [PBWin]::SetForegroundWindow([IntPtr]$p.MainWindowHandle) | Out-Null
            } catch {}

            Start-Sleep -Milliseconds 120
            $fg = [PBWin]::GetForegroundWindow()
            $fgPid = 0
            [PBWin]::GetWindowThreadProcessId($fg, [ref]$fgPid) | Out-Null
            if ($fgPid -eq [uint32]$p.Id) { $ok = $true; break }
        }
    } catch {}
}

if ($ok) { exit 0 }
exit 20
PS;

    $ps = str_replace('__PB_FILE__', $psFileLiteral, $ps);

        // Use -Command with an embedded file path instead of -EncodedCommand + args.
        // This avoids quoting/arg parsing issues that can cause PowerShell to print help and exit non-zero.
        $cmd = 'powershell.exe -Sta -WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -Command '
            . escapeshellarg($ps);

        $exitCode = 1;
        $out = [];
        try {
            @exec($cmd, $out, $exitCode);
        } catch (\Throwable $e) {
            \Log::warning('openPrintPictures exec failed', [
                'error' => $e->getMessage(),
            ]);
            $exitCode = 1;
        }

        if ($exitCode !== 0) {
            \Log::warning('openPrintPictures not visible', [
                'exitCode' => $exitCode,
                'file' => $absolute,
                'output' => $out,
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Print Pictures did not appear (fallback to browser print)',
            ], 200);
        }

        \Log::info('openPrintPictures launched', [
            'file' => $absolute,
        ]);

        return response()->json(['ok' => true]);
    }

    private function applyCollageSharpening(string $binary): string
    {
        // Prefer Imagick (true unsharp mask) if available.
        if (extension_loaded('imagick')) {
            try {
                $img = new \Imagick();
                $img->readImageBlob($binary);
                $img->setImageColorspace(\Imagick::COLORSPACE_SRGB);
                $img->setImageResolution(300, 300);
                $img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
                $img->setImageInterlaceScheme(\Imagick::INTERLACE_JPEG);
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(95);

                // Light unsharp mask: keep it subtle to avoid noise.
                // radius, sigma, amount, threshold
                $img->unsharpMaskImage(1.0, 0.8, 0.65, 0.02);

                $img->setImageFormat('jpeg');
                $out = $img->getImageBlob();
                $img->clear();
                $img->destroy();
                if (is_string($out) && $out !== '') return $out;
            } catch (\Throwable $e) {
                // fallback below
            }
        }

        // Fallback: mild sharpening kernel via GD (not a full unsharp mask, but helps).
        if (function_exists('imagecreatefromstring') && function_exists('imageconvolution')) {
            try {
                $im = @imagecreatefromstring($binary);
                if ($im !== false) {
                    // Mild sharpen kernel
                    $matrix = [
                        [0.0, -0.7, 0.0],
                        [-0.7, 3.8, -0.7],
                        [0.0, -0.7, 0.0],
                    ];
                    @imageconvolution($im, $matrix, 1.0, 0.0);
                    if (function_exists('imageresolution')) {
                        @imageresolution($im, 300, 300);
                    }
                    ob_start();
                    imagejpeg($im, null, 95);
                    $out = (string) ob_get_clean();
                    imagedestroy($im);
                    if ($out !== '') return $out;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $binary;
    }

    // Setelah foto selesai diambil: pilih frame sesuai layout
    public function listRepeatPhoto(){
        $code = $this->getReadyCode();
        if ($code) {
            $this->ensureFlowRunInSessionForCode($code);
            $this->ensureLayoutInSessionFromFlowRun();
            // ambil code yang terdeteksi yang akan digunakan untuk mengambil path foto yang bersangkutan
            $directory = 'public/'.$code->transaction->email.'/photo';

            if(Storage::exists($directory)) {
                //ambil semua file dari folder bersangkutan
                $files = Storage::files($directory);
                $files = $this->applySessionPhotoOrder($files);

                $layoutKey = (string) session('layout_key', 'layout2');
                $defaultCount = match ($layoutKey) {
                    'layout1' => 1,
                    'layout2' => 2,
                        // layout3 uses 2 columns × 3 rows => 6 photos
                        'layout3' => 6,
                    'layout4' => 6,
                    default => 3,
                };
                $layoutCount = (int) session('layout_count', $defaultCount);
                $frames = $this->getAvailableFrames($layoutKey, false);
                $frameUrl = $this->getSelectedFrameUrl($frames);
                $frameConfig = $this->getSelectedFrameConfig($frames);
                $globalRowGapRatio = \App\Models\AppSetting::getFloat('row_gap_ratio', (float) config('photoblast.row_gap_ratio', 0.012));

                $photoUrls = array_map(function ($p) {
                    $rel = str_replace('public', 'storage', $p);
                    return '/'.ltrim($rel, '/');
                }, $files);

                return view('list', [
                    'h1' => 'Pilih frame',
                    'photos' => $files,
                    'notes' => '',
                    'mode' => 'frame',
                    'frames' => $frames,
                    'frameUrl' => $frameUrl,
                    'frameConfig' => $frameConfig,
                    'global_row_gap_ratio' => $globalRowGapRatio,
                    'layout_key' => $layoutKey,
                    'layout_count' => $layoutCount,
                    'photoUrls' => $photoUrls,
                    'email' => $code->transaction->email,
                ]);
            }
        }
        return redirect()->route('redeem.index');
    }

    public function listPrintPhoto(){
        $code = $this->getReadyCode();
        if ($code) {
            // ambil code yang terdeteksi untuk mengambil path foto
            $directory = 'public/'.$code->transaction->email.'/photo';
            if(Storage::exists($directory)) {
                // ambil semua file dari folder bersangkutan
                $files = Storage::files($directory);
                $files = $this->applySessionPhotoOrder($files);
                $globalRowGapRatio = \App\Models\AppSetting::getFloat('row_gap_ratio', (float) config('photoblast.row_gap_ratio', 0.012));
                return view('list', [
                    'h1' => 'Pilih foto untuk dicetak',
                    'photos' => $files,
                    'notes' => '',
                    'limit' => (int) session('layout_count', 3),
                    'global_row_gap_ratio' => $globalRowGapRatio,
                ]);
            }
        }
        return redirect()->route('redeem.index');
    }

    public function retakePhoto(Request $request) {
        $code = $this->getReadyCode();
        if ($code && $request->photos) {
            // ambil nama file photo bersangkutan
            $photos = [];
            foreach($request->photos as $photo){
                array_push($photos, str_replace('public/'.$code->transaction->email.'/photo/', '', $photo));
            }
            return view('camera', [
                'email' => $code->transaction->email,
                'limit' => count($request->photos),
                'request'=> "retake",
                'photoname' => $photos,
            ]);
        } elseif ($code) {
            return redirect()->route('list-photo');
        }
        return redirect()->route('redeem.index');
    }

    public function setPhotoOrder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1', 'max:100'],
            'order.*' => ['required', 'string', 'max:255'],
        ]);

        $raw = $data['order'] ?? [];
        $basenames = [];
        foreach ($raw as $item) {
            $b = basename((string) $item);
            if ($b !== '' && !in_array($b, $basenames, true)) {
                $basenames[] = $b;
            }
        }

        session(['photo_order' => $basenames]);

        return response()->json(['ok' => true, 'count' => count($basenames)]);
    }

    private function applySessionPhotoOrder(array $files): array
    {
        $order = session('photo_order', []);
        if (!is_array($order) || empty($order) || empty($files)) {
            return $files;
        }

        $byBase = [];
        foreach ($files as $f) {
            $byBase[basename((string) $f)] = $f;
        }

        $ordered = [];

        // First: files listed in the session order (if present)
        foreach ($order as $b) {
            $b = basename((string) $b);
            if ($b !== '' && array_key_exists($b, $byBase)) {
                $ordered[] = $byBase[$b];
                unset($byBase[$b]);
            }
        }

        // Then: any remaining files, preserving original order
        foreach ($files as $f) {
            $b = basename((string) $f);
            if (array_key_exists($b, $byBase)) {
                $ordered[] = $byBase[$b];
                unset($byBase[$b]);
            }
        }

        return $ordered;
    }

    public function setFrame(Request $request)
    {
        $this->ensureLayoutInSessionFromFlowRun();
        $layoutKey = (string) session('layout_key', 'layout2');
        $frames = $this->getAvailableFrames($layoutKey, false);
        $request->validate([
            'frame' => 'nullable',
        ]);

        $frameInput = $request->input('frame');
        if ($frameInput === null || $frameInput === '') {
            session()->forget(['frame_file', 'frame_id']);

            $flowId = (int) session('flow_run_id', 0);
            if ($flowId > 0) {
                FlowRun::where('id', $flowId)->update([
                    'frame_id' => null,
                    'frame_file' => null,
                ]);
            }
            session()->forget('frame_file');
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'frame' => null]);
            }
            return redirect()->back();
        }

        // Prefer selecting by frame ID (DB-backed)
        $selectedFrame = null;
        if (is_numeric($frameInput)) {
            $id = (int) $frameInput;
            foreach ($frames as $f) {
                if ((int) ($f['id'] ?? 0) === $id) {
                    $selectedFrame = $f;
                    break;
                }
            }
        } else {
            // Backward compatible: accept file path
            $file = (string) $frameInput;
            foreach ($frames as $f) {
                if ((string) ($f['file'] ?? '') === $file) {
                    $selectedFrame = $f;
                    break;
                }
            }
        }

        if (!$selectedFrame) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Frame tidak valid'], 422);
            }
            return redirect()->back()->with('message', 'Frame tidak valid');
        }

        $frameId = (int) ($selectedFrame['id'] ?? 0);
        $frameFile = (string) ($selectedFrame['file'] ?? '');
        session([
            'frame_id' => $frameId > 0 ? $frameId : null,
            'frame_file' => $frameFile,
        ]);

        $flowId = (int) session('flow_run_id', 0);
        if ($flowId > 0) {
            FlowRun::where('id', $flowId)->update([
                'frame_id' => $frameId > 0 ? $frameId : null,
                'frame_file' => $frameFile,
            ]);
        }
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'frame' => '/'.ltrim($frameFile, '/')]);
        }
        return redirect()->back();
    }

    private function ensureLayoutInSessionFromFlowRun(): void
    {
        $layoutKey = (string) session('layout_key', '');
        $layoutCount = (int) session('layout_count', 0);
        if ($layoutKey !== '' && $layoutCount > 0) {
            return;
        }

        $flowId = (int) session('flow_run_id', 0);
        if ($flowId <= 0) {
            return;
        }

        $flow = FlowRun::find($flowId);
        if (!$flow) {
            return;
        }

        if (!empty($flow->layout_key) && (int) $flow->layout_count > 0) {
            $key = (string) $flow->layout_key;
            $count = (int) $flow->layout_count;
            // Backward-compat: older runs may store layout3 as 4, but layout3 now needs 6 photos.
            if ($key === 'layout3' && $count < 6) {
                $count = 6;
            }
            session([
                'layout_key' => $key,
                'layout_count' => $count,
            ]);
        }

        if (!empty($flow->frame_file)) {
            session(['frame_file' => (string) $flow->frame_file]);
        }

        if (!empty($flow->frame_id)) {
            session(['frame_id' => (int) $flow->frame_id]);
        }
    }

    private function ensureFlowRunInSessionForCode(Code $code): void
    {
        $flowId = (int) session('flow_run_id', 0);
        if ($flowId > 0) {
            return;
        }

        // Recover the latest active flow run for this code/transaction.
        $query = FlowRun::query();
        if (!empty($code->id)) {
            $query->where('code_id', $code->id);
        }

        if ($code->transaction && !empty($code->transaction->id)) {
            $query->orWhere('transaction_id', $code->transaction->id);
        }

        $flow = $query->orderByDesc('id')->first();
        if (!$flow) {
            return;
        }

        session([
            'flow_run_id' => $flow->id,
            'flow_id' => $flow->uuid,
            'flow_started_at' => $flow->started_at?->toISOString(),
        ]);
    }

    private function getAvailableFrames(?string $layoutKey = null, bool $includeTemplateFallback = false): array
    {
        $layoutKey = $layoutKey ?: (string) session('layout_key', 'layout2');

        $frames = Frame::query()
            ->where('layout_key', $layoutKey)
            ->orderByDesc('id')
            ->get(['id', 'file', 'photo_pad_ratio', 'photo_scale', 'photo_offset_x', 'photo_offset_y', 'grid_gap_ratio', 'row_anchor_ratio'])
            ->map(fn ($f) => [
                'id' => (int) $f->id,
                'file' => (string) $f->file,
                'url' => '/'.ltrim((string) $f->file, '/'),
                'config' => [
                    'pad_ratio' => (float) ($f->photo_pad_ratio ?? 0.070),
                    'scale' => (float) ($f->photo_scale ?? 1.000),
                    'offset_x' => (float) ($f->photo_offset_x ?? 0.000),
                    'offset_y' => (float) ($f->photo_offset_y ?? 0.000),
                    'grid_gap_ratio' => (float) ($f->grid_gap_ratio ?? 0.020),
                    'row_anchor_ratio' => (float) ($f->row_anchor_ratio ?? 1.000),
                ],
            ])
            ->all();

        if (!$includeTemplateFallback) {
            return $frames;
        }

        // Optional fallback: read from template folder (legacy behavior)
        $dir = storage_path('app/public/template');
        if (!File::exists($dir)) {
            return $frames;
        }

        $seen = [];
        foreach ($frames as $f) {
            $seen[$f['file']] = true;
        }

        foreach (File::files($dir) as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                continue;
            }
            $name = $file->getFilename();
            $relative = 'storage/template/'.$name;
            if (isset($seen[$relative])) {
                continue;
            }
            $seen[$relative] = true;
            $frames[] = [
                'file' => $relative,
                'url' => '/'.ltrim($relative, '/'),
            ];
        }

        return $frames;
    }

    private function getSelectedFrameConfig(array $frames): array
    {
        $defaults = [
            'pad_ratio' => 0.070,
            'scale' => 1.000,
            'offset_x' => 0.000,
            'offset_y' => 0.000,
            'grid_gap_ratio' => 0.020,
            'row_anchor_ratio' => 1.000,
        ];

        $selectedId = (int) session('frame_id', 0);
        if ($selectedId > 0) {
            foreach ($frames as $frame) {
                if ((int) ($frame['id'] ?? 0) === $selectedId) {
                    $cfg = (array) ($frame['config'] ?? []);
                    return array_merge($defaults, array_intersect_key($cfg, $defaults));
                }
            }
        }

        return $defaults;
    }

    private function getSelectedFrameUrl(array $frames): ?string
    {
        $selectedId = (int) session('frame_id', 0);
        if ($selectedId > 0) {
            foreach ($frames as $frame) {
                if ((int) ($frame['id'] ?? 0) === $selectedId) {
                    return $frame['url'] ?? null;
                }
            }

            session()->forget('frame_id');
        }

        $selected = (string) session('frame_file', '');
        if ($selected === '') {
            return null;
        }

        foreach ($frames as $frame) {
            if (($frame['file'] ?? '') === $selected) {
                return $frame['url'] ?? null;
            }
        }

        session()->forget('frame_file');
        return null;
    }

    private $pythonScript;
    private $pythonBin;
    private $emailSender;
    private $emailPassword;
    private $templateHtml;  // Pindahkan ke constructor
    private $emailSubject = "Thank You for Taking Photo on Our PhotoBooth!";

    public function __construct() {
        // Set path relative to project root untuk kedua file
        $this->pythonScript = base_path('kirim_email.py');
        $this->templateHtml = base_path('template_email/test.html');

        // Prefer project venv python (Windows) so the app works even when system Python isn't installed.
        $defaultPython = base_path('photoblast_env/Scripts/python.exe');
        $this->pythonBin = (string) (env('PYTHON_BIN') ?? (file_exists($defaultPython) ? $defaultPython : 'python'));

        // Gmail credentials should NOT be hardcoded.
        // Prefer dedicated env vars; fall back to Laravel mail env keys if present.
        $this->emailSender = (string) (env('GMAIL_SENDER')
            ?? env('MAIL_FROM_ADDRESS')
            ?? env('MAIL_USERNAME')
            ?? '');

        $this->emailPassword = (string) (env('GMAIL_APP_PASSWORD')
            ?? env('MAIL_PASSWORD')
            ?? '');
    }

    public function sendPhoto(Request $request) {
        if (!session()->has('code')) {
            return redirect()->route('redeem.index');
        }

        $code = Code::where('code', session('code'))->first();
        if (!$code || $code->status !== 'ready') {
            return redirect()->route('redeem.index');
        }

        $request->validate([
            'email' => 'required|email',
            'photo' => 'nullable|string',
        ]);

        $email = (string) $request->input('email');

        try {
            $disk = Storage::disk('public');

            // Save collage sent from browser (base64), if provided.
            $collageRel = $email.'/photo/collage/collage.jpg';
            if ($request->filled('photo')) {
                $binary = base64_decode((string) $request->input('photo'), true);
                if ($binary === false) {
                    return response()->json(['error' => 'Invalid photo data'], 400);
                }
                $disk->put($collageRel, $binary);
            }

            // Attach all photos in storage/app/public/{email}/photo/** (images only)
            $allowedExt = ['png', 'jpg', 'jpeg', 'webp'];
            $attachments = [];

            $photoRoot = $email.'/photo';
            if ($disk->exists($photoRoot)) {
                $all = $disk->allFiles($photoRoot);
                sort($all);
                foreach ($all as $rel) {
                    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExt, true)) continue;
                    // Skip collage here; we add it explicitly below to avoid duplicates.
                    if (str_contains(str_replace('\\', '/', $rel), '/photo/collage/')) continue;
                    $attachments[] = Attachment::fromPath($disk->path($rel))->as(basename($rel));
                }
            }

            // Attach collage if present
            if ($disk->exists($collageRel)) {
                $attachments[] = Attachment::fromPath($disk->path($collageRel))->as('collage.jpg');
            }

            // Attach any mp4 video if it exists (optional)
            $videoCandidates = [];
            $gifVideoRel = $email.'/gif_video/slideshow.mp4';
            if ($disk->exists($gifVideoRel)) $videoCandidates[] = $gifVideoRel;
            $videoDir = $email.'/video';
            if ($disk->exists($videoDir)) {
                foreach ($disk->files($videoDir) as $rel) {
                    if (strtolower(pathinfo($rel, PATHINFO_EXTENSION)) === 'mp4') {
                        $videoCandidates[] = $rel;
                    }
                }
            }
            foreach ($videoCandidates as $rel) {
                // Keep attachments reasonable; attach the first mp4 found.
                $attachments[] = Attachment::fromPath($disk->path($rel))->as(basename($rel));
                break;
            }

            if (count($attachments) === 0) {
                return response()->json(['error' => 'Tidak ada file untuk dikirim'], 422);
            }

            Mail::to($email)->send(new SendPhotoAndVideo((string) $code->code, $attachments));
            return response()->json(['message' => 'Email sent successfully'], 201);

        } catch (Exception $e) {
            Log::error('Send mail error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }

    public function print(Request $request) {
        $code = $this->getReadyCode();
        if ($code) {
            if($request->photos){
                $layoutKey = (string) session('layout_key', 'layout2');
                $layoutCount = (int) session('layout_count', 3);

                $frames = $this->getAvailableFrames($layoutKey, false);
                $frameUrl = $this->getSelectedFrameUrl($frames);
                $frameConfig = $this->getSelectedFrameConfig($frames);
                $globalRowGapRatio = \App\Models\AppSetting::getFloat('row_gap_ratio', (float) config('photoblast.row_gap_ratio', 0.012));

                if (count($request->photos) !== $layoutCount) {
                    return redirect()->route('print-photo');
                }
                // ambil nama foto yang akan dicetak kedalam template
                $photos = [];
                foreach($request->photos as $photo){
                    $rel = str_replace('public', 'storage', $photo);
                    array_push($photos, '/'.ltrim($rel, '/'));
                }

                return view('print', [
                    'layout_key' => $layoutKey,
                    'layout_count' => $layoutCount,
                    'photos' => $photos,
                    'frame_url' => $frameUrl,
                    'frame_config' => $frameConfig,
                    'global_row_gap_ratio' => $globalRowGapRatio,
                    'email' => $code->transaction->email,
                    // When enabled, do not fall back to browser printing and do not auto-finish;
                    // operator must confirm printing before email/finish.
                    'force_print_pictures' => (\App\Models\AppSetting::getString('print.force_print_pictures', '1') === '1'),
                    // If true, skip native Print Pictures and always use browser auto-print (Chrome --kiosk-printing).
                    // Default: always use browser auto-print to avoid native Print Pictures dialog.
                    'force_use_browser' => true,
                ]);
            } else {
                return redirect()->route('print-photo');
            }
        }
        return redirect()->route('redeem.index');
    }

    public function getVideo($code) {
        $c = Code::where('code', $code)->first();
        if (!$c || !$c->transaction) {
            return redirect()->route('redeem.index');
        }
        $email = $c->transaction->email;
        return view('video', ['email' => $email]);
    }

    /**
     * Return the session code if ready and has transaction, otherwise null
     */

    private function getReadyCode()
    {
        if (!session()->has('code')) return null;
        $code = Code::where('code', session('code'))->first();
        if (!$code || $code->status !== 'ready' || !$code->transaction) return null;
        return $code;
    }
    


}
