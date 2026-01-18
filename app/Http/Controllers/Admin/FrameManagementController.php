<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FrameManagementController extends Controller
{
    private const LAYOUTS = ['layout1', 'layout2', 'layout3', 'layout4'];

    private function normalizeLayout(string $layout): string
    {
        $layout = trim($layout);

        // Accept numeric input (e.g. "3") as layout3
        if (in_array($layout, ['1', '2', '3', '4'], true)) {
            $layout = 'layout'.$layout;
        }

        if (!in_array($layout, self::LAYOUTS, true)) {
            return 'layout2';
        }

        return $layout;
    }

    private function layoutMeta(string $layout): array
    {
        $number = (int) str_replace('layout', '', $layout);
        $count = match ($number) {
            1 => 1,
            2 => 4,
            3 => 4,
            4 => 6,
            default => 4,
        };

        return [
            'number' => $number,
            'count' => $count,
            'label' => 'Layout '.$number.' ('.$count.' foto)',
        ];
    }

    public function index(Request $request)
    {
        $layout = $this->normalizeLayout((string) $request->query('layout', (string) session('layout_key', 'layout2')));
        $meta = $this->layoutMeta($layout);

        $selectedPhoto = (string) $request->query('photo', '');
        $selectedFrameId = (int) $request->query('frame', 0);

        $frames = Frame::query()
            ->where('layout_key', $layout)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'name' => basename((string) $f->file),
                'file' => (string) $f->file,
                'url' => '/'.ltrim((string) $f->file, '/'),
                'photo_pad_ratio' => (float) ($f->photo_pad_ratio ?? 0.070),
                'photo_scale' => (float) ($f->photo_scale ?? 1.000),
                'photo_offset_x' => (float) ($f->photo_offset_x ?? 0.000),
                'photo_offset_y' => (float) ($f->photo_offset_y ?? 0.000),
                'grid_gap_ratio' => (float) ($f->grid_gap_ratio ?? 0.020),
                'row_anchor_ratio' => (float) ($f->row_anchor_ratio ?? 1.000),
            ])
            ->all();

        // Foto yang tersedia untuk preview: ambil dari storage/app/public/template
        $photos = [];
        $templateDir = storage_path('app/public/template');
        if (File::exists($templateDir)) {
            $files = File::files($templateDir);
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $ext = strtolower($file->getExtension());
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                    continue;
                }

                $name = $file->getFilename();
                $relative = 'template/'.$name;
                $photos[] = [
                    'name' => $name,
                    'file' => $relative,
                    'url' => '/storage/'.ltrim($relative, '/'),
                ];
            }
        }

        $photoUrl = null;
        if ($selectedPhoto !== '') {
            foreach ($photos as $p) {
                if ($p['file'] === $selectedPhoto) {
                    $photoUrl = $p['url'];
                    break;
                }
            }
        }

        $frameUrl = null;
        if ($selectedFrameId > 0) {
            foreach ($frames as $f) {
                if ((int) $f['id'] === $selectedFrameId) {
                    $frameUrl = $f['url'];
                    break;
                }
            }
        }

        return view('admin.frames.index', [
            'layout' => $layout,
            'layouts' => self::LAYOUTS,
            'layout_meta' => $meta,
            'frames' => $frames,
            'photos' => $photos,
            'selected_photo' => $selectedPhoto,
            'selected_frame' => $selectedFrameId,
            'preview_photo_url' => $photoUrl,
            'preview_frame_url' => $frameUrl,
        ]);
    }

    public function store(Request $request)
    {
        $layout = $this->normalizeLayout((string) $request->input('layout', 'layout2'));

        $request->validate([
            'layout' => 'required|string',
            'frames' => 'required',
            'frames.*' => 'file|mimes:png,jpg,jpeg,webp|max:10240',
        ]);

        $files = $request->file('frames', []);
        foreach ($files as $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $name = (string) Str::uuid().'.'.$ext;

            $diskPath = 'frames/'.$layout.'/'.$name;
            Storage::disk('public')->putFileAs('frames/'.$layout, $file, $name);

            Frame::create([
                'layout_key' => $layout,
                'file' => 'storage/'.$diskPath,
                'original_name' => (string) $file->getClientOriginalName(),
                'photo_pad_ratio' => 0.070,
                'photo_scale' => 1.000,
                'photo_offset_x' => 0.000,
                'photo_offset_y' => 0.000,
                'grid_gap_ratio' => 0.020,
                'row_anchor_ratio' => 1.000,
            ]);
        }

        return redirect()->route('admin.frames.index', ['layout' => $layout])->with('message', 'Frame berhasil diupload');
    }

    public function storeFromTemplate(Request $request)
    {
        $layout = $this->normalizeLayout((string) $request->input('layout', 'layout2'));

        $templateFile = (string) $request->input('template_file', '');
        if ($templateFile === '' || !str_starts_with($templateFile, 'template/')) {
            return redirect()->back()->with('message', 'Pilih foto template dulu');
        }
        if (str_contains($templateFile, '..') || str_contains($templateFile, '/') && !str_starts_with($templateFile, 'template/')) {
            return redirect()->back()->with('message', 'Request tidak valid');
        }

        $source = storage_path('app/public/'.str_replace('\\', '/', $templateFile));
        if (!File::exists($source)) {
            return redirect()->back()->with('message', 'File template tidak ditemukan');
        }

        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return redirect()->back()->with('message', 'Format file tidak didukung');
        }

        $targetDir = storage_path('app/public/frames/'.$layout);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $baseName = pathinfo($source, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName) ?: 'frame';
        $fileName = $baseName.'.'.$ext;
        $target = $targetDir.'/'.$fileName;
        $i = 2;
        while (File::exists($target)) {
            $fileName = $baseName.'-'.$i.'.'.$ext;
            $target = $targetDir.'/'.$fileName;
            $i++;
        }

        File::copy($source, $target);

        Frame::create([
            'layout_key' => $layout,
            'file' => 'storage/frames/'.$layout.'/'.$fileName,
            'original_name' => basename($templateFile),
            'photo_pad_ratio' => 0.070,
            'photo_scale' => 1.000,
            'photo_offset_x' => 0.000,
            'photo_offset_y' => 0.000,
            'grid_gap_ratio' => 0.020,
            'row_anchor_ratio' => 1.000,
        ]);

        return redirect()->route('admin.frames.index', ['layout' => $layout])->with('message', 'Frame disimpan dari template');
    }

    public function destroy(Request $request)
    {
        $layout = $this->normalizeLayout((string) $request->input('layout', 'layout2'));
        $id = (int) $request->input('id', 0);

        if ($id <= 0) {
            return redirect()->back()->with('message', 'Request tidak valid');
        }

        $frame = Frame::where('id', $id)->where('layout_key', $layout)->first();
        if ($frame) {
            $file = (string) $frame->file;
            // stored as "storage/<diskPath>"; disk path is without leading "storage/"
            $diskPath = str_starts_with($file, 'storage/') ? substr($file, 8) : $file;
            Storage::disk('public')->delete($diskPath);
            $frame->delete();
        }

        return redirect()->route('admin.frames.index', ['layout' => $layout])->with('message', 'Frame dihapus');
    }

    public function updateSettings(Request $request)
    {
        $layout = $this->normalizeLayout((string) $request->input('layout', 'layout2'));
        $id = (int) $request->input('id', 0);

        if ($id <= 0) {
            return redirect()->back()->with('message', 'Request tidak valid');
        }

        $request->validate([
            'photo_pad_ratio' => 'nullable|numeric',
            'photo_scale' => 'nullable|numeric',
            'photo_offset_x' => 'nullable|numeric',
            'photo_offset_y' => 'nullable|numeric',
            'grid_gap_ratio' => 'nullable|numeric',
            'row_anchor_ratio' => 'nullable|numeric',
        ]);

        $frame = Frame::where('id', $id)->where('layout_key', $layout)->first();
        if (!$frame) {
            return redirect()->back()->with('message', 'Frame tidak ditemukan');
        }

        $pad = (float) $request->input('photo_pad_ratio', (float) ($frame->photo_pad_ratio ?? 0.070));
        $scale = (float) $request->input('photo_scale', (float) ($frame->photo_scale ?? 1.000));
        $ox = (float) $request->input('photo_offset_x', (float) ($frame->photo_offset_x ?? 0.000));
        $oy = (float) $request->input('photo_offset_y', (float) ($frame->photo_offset_y ?? 0.000));
        $gapRatio = (float) $request->input('grid_gap_ratio', (float) ($frame->grid_gap_ratio ?? 0.020));
        $rowAnchor = (float) $request->input('row_anchor_ratio', (float) ($frame->row_anchor_ratio ?? 1.000));

        // Clamp to safe ranges
        $pad = max(0.000, min(0.200, $pad));
        $scale = max(0.500, min(1.500, $scale));
        $ox = max(-0.300, min(0.300, $ox));
        $oy = max(-0.300, min(0.300, $oy));
        $gapRatio = max(0.000, min(0.060, $gapRatio));
        $rowAnchor = max(0.000, min(1.000, $rowAnchor));

        $frame->update([
            'photo_pad_ratio' => $pad,
            'photo_scale' => $scale,
            'photo_offset_x' => $ox,
            'photo_offset_y' => $oy,
            'grid_gap_ratio' => $gapRatio,
            'row_anchor_ratio' => $rowAnchor,
        ]);

        return redirect()->route('admin.frames.index', ['layout' => $layout])->with('message', 'Pengaturan foto disimpan');
    }
}
