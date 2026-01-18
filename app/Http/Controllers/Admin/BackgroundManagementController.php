<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\PageBackground;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackgroundManagementController extends Controller
{
    /**
     * Keys here should match route names for the pages.
     */
    private const PAGES = [
        ['key' => 'flow.start', 'label' => 'Flow - Start', 'default' => 'img/bgstart2.png'],
        ['key' => 'flow.package', 'label' => 'Flow - Package', 'default' => 'img/logosetelahstart.jpg'],
        ['key' => 'flow.payment', 'label' => 'Flow - Payment', 'default' => 'img/bgpembayaran.jpg'],
        ['key' => 'flow.success', 'label' => 'Flow - Success', 'default' => 'img/bgsukses.jpg'],
        ['key' => 'redeem.index', 'label' => 'Redeem Code', 'default' => 'img/redeemcode.jpg'],
        ['key' => 'tempcollage.index', 'label' => 'Pilih Layout', 'default' => 'img/pilihanlayout.jpg'],
        // Thumbnails used in tempcollage layout picker (visual-only)
        ['key' => 'tempcollage.thumb.layout1', 'label' => 'Thumbnail - Layout 1', 'default' => 'img/bgkamera.jpg'],
        ['key' => 'tempcollage.thumb.layout2', 'label' => 'Thumbnail - Layout 2', 'default' => 'img/bgkamera.jpg'],
        ['key' => 'tempcollage.thumb.layout3', 'label' => 'Thumbnail - Layout 3', 'default' => 'img/bgkamera.jpg'],
        ['key' => 'tempcollage.thumb.layout4', 'label' => 'Thumbnail - Layout 4', 'default' => 'img/bgkamera.jpg'],
        ['key' => 'camera', 'label' => 'Camera', 'default' => 'img/bgkamera.png'],
        ['key' => 'list-photo', 'label' => 'Pilih Frame (/listphoto)', 'default' => 'img/bgkamera.png'],
        ['key' => 'print-photo', 'label' => 'Pilih Foto Cetak (/printphoto)', 'default' => 'img/bgkamera.png'],
    ];

    public function index()
    {
        $keys = array_map(fn ($p) => $p['key'], self::PAGES);

        $rows = PageBackground::query()
            ->whereIn('page_key', array_merge(['global'], $keys))
            ->get()
            ->keyBy('page_key');

        $globalPath = $rows->get('global')?->path;
        $globalUrl = $globalPath ? '/storage/'.ltrim((string) $globalPath, '/') : null;

        $pages = array_map(function ($p) use ($rows) {
            $customPath = $rows->get($p['key'])?->path;
            return [
                'key' => $p['key'],
                'label' => $p['label'],
                'default' => $p['default'],
                'effective_url' => PageBackground::url($p['key'], $p['default']),
                'custom_url' => $customPath ? '/storage/'.ltrim((string) $customPath, '/') : null,
            ];
        }, self::PAGES);

        $layoutEnabled = [
            1 => AppSetting::getString('tempcollage.layout1.enabled', '0') === '1',
            2 => AppSetting::getString('tempcollage.layout2.enabled', '1') === '1',
            3 => AppSetting::getString('tempcollage.layout3.enabled', '1') === '1',
            4 => AppSetting::getString('tempcollage.layout4.enabled', '0') === '1',
        ];

        return view('admin.backgrounds.index', [
            'global_url' => $globalUrl,
            'pages' => $pages,
            'layout_enabled' => $layoutEnabled,
        ]);
    }

    public function updateTempcollageLayouts(Request $request)
    {
        $data = $request->validate([
            'layout1' => 'nullable|in:0,1',
            'layout2' => 'nullable|in:0,1',
            'layout3' => 'nullable|in:0,1',
            'layout4' => 'nullable|in:0,1',
        ]);

        AppSetting::setString('tempcollage.layout1.enabled', (($data['layout1'] ?? '0') === '1') ? '1' : '0');
        AppSetting::setString('tempcollage.layout2.enabled', (($data['layout2'] ?? '0') === '1') ? '1' : '0');
        AppSetting::setString('tempcollage.layout3.enabled', (($data['layout3'] ?? '0') === '1') ? '1' : '0');
        AppSetting::setString('tempcollage.layout4.enabled', (($data['layout4'] ?? '0') === '1') ? '1' : '0');

        return redirect()->route('admin.backgrounds.index')->with('message', 'Pengaturan layout tempcollage berhasil disimpan.');
    }

    public function updateGlobal(Request $request)
    {
        $request->validate([
            'background' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        $file = $request->file('background');
        $folder = 'backgrounds/global';

        $existing = PageBackground::query()->where('page_key', 'global')->first();
        $path = $file->store($folder, 'public');

        PageBackground::query()->updateOrCreate(
            ['page_key' => 'global'],
            ['path' => $path]
        );

        if ($existing?->path) {
            Storage::disk('public')->delete($existing->path);
        }

        return redirect()->route('admin.backgrounds.index')->with('message', 'Background global berhasil disimpan.');
    }

    public function updatePage(Request $request)
    {
        $allowed = array_map(fn ($p) => $p['key'], self::PAGES);

        $request->validate([
            'page_key' => 'required|string|in:' . implode(',', $allowed),
            'background' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        $pageKey = (string) $request->input('page_key');
        $file = $request->file('background');

        $safeKey = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $pageKey);
        $folder = 'backgrounds/pages/' . $safeKey;

        $existing = PageBackground::query()->where('page_key', $pageKey)->first();
        $path = $file->store($folder, 'public');

        PageBackground::query()->updateOrCreate(
            ['page_key' => $pageKey],
            ['path' => $path]
        );

        if ($existing?->path) {
            Storage::disk('public')->delete($existing->path);
        }

        return redirect()->route('admin.backgrounds.index')->with('message', 'Background untuk ' . $pageKey . ' berhasil disimpan.');
    }

    public function clearPage(Request $request)
    {
        $allowed = array_map(fn ($p) => $p['key'], self::PAGES);

        $request->validate([
            'page_key' => 'required|string|in:' . implode(',', $allowed),
        ]);

        $pageKey = (string) $request->input('page_key');
        $existing = PageBackground::query()->where('page_key', $pageKey)->first();

        if ($existing?->path) {
            Storage::disk('public')->delete($existing->path);
        }

        PageBackground::query()->where('page_key', $pageKey)->delete();

        return redirect()->route('admin.backgrounds.index')->with('message', 'Override background ' . $pageKey . ' berhasil dihapus.');
    }

    public function applyGlobalToAll()
    {
        $rows = PageBackground::query()->where('page_key', '!=', 'global')->get();

        foreach ($rows as $row) {
            if ($row->path) {
                Storage::disk('public')->delete($row->path);
            }
        }

        PageBackground::query()->where('page_key', '!=', 'global')->delete();

        return redirect()->route('admin.backgrounds.index')->with('message', 'Semua halaman sekarang memakai background global (override dihapus).');
    }
}
