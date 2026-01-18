<?php

use Illuminate\Support\Facades\Route;

// Flow timeout endpoint: invalidates session and returns to start page
Route::get('/timeout', function (\Illuminate\Http\Request $request) {
  $request->session()->invalidate();
  $request->session()->regenerateToken();
  return redirect()->route('home')->with('message', 'Waktu habis. Mulai ulang dari awal.');
})->name('pb.timeout');

Route::get('/', [App\Http\Controllers\PhotoblastController::class, 'index'])->name('home');
Route::get('/camera', [App\Http\Controllers\PhotoblastController::class, 'camera'])->name('camera')->middleware('pb.flow.deadline');
Route::post('/camera/frame', [App\Http\Controllers\PhotoblastController::class, 'setFrame'])->name('camera.frame')->middleware('pb.flow.deadline');
Route::post('/photo-order', [App\Http\Controllers\PhotoblastController::class, 'setPhotoOrder'])->name('photo.order')->middleware('pb.flow.deadline');
// Route::post('/payment', [App\Http\Controllers\PhotoblastController::class, 'processpayment'])->name('payment');
// Route::post('/verify', [App\Http\Controllers\PhotoblastController::class, 'verifyPayment'])->name('verify');
Route::post('/save-video', [App\Http\Controllers\PhotoblastController::class, 'saveVideo'])->name('save-video')->middleware('pb.flow.deadline');
Route::post('/save-photo', [App\Http\Controllers\PhotoblastController::class, 'savePhoto'])->name('save-photo')->middleware('pb.flow.deadline');
Route::post('/save-collage', [App\Http\Controllers\PhotoblastController::class, 'saveCollage'])->name('save-collage')->middleware('pb.flow.deadline');
Route::post('/open-print-pictures', [App\Http\Controllers\PhotoblastController::class, 'openPrintPictures'])->name('open-print-pictures')->middleware('pb.flow.deadline');
Route::post('/send-mail', [App\Http\Controllers\PhotoblastController::class, 'sendPhoto'])->name('send-mail')->middleware('pb.flow.deadline');
Route::resource('redeem', App\Http\Controllers\CodeController::class);
// Admin routes to create/manage redeem codes (simple, no auth)
Route::get('/admin/codes', [App\Http\Controllers\Admin\CodeManagementController::class, 'index'])->name('admin.codes.index');
Route::post('/admin/codes', [App\Http\Controllers\Admin\CodeManagementController::class, 'store'])->name('admin.codes.store');

// Admin routes to manage frames per layout (simple, no auth)
Route::get('/admin/frames', [App\Http\Controllers\Admin\FrameManagementController::class, 'index'])->name('admin.frames.index');
Route::post('/admin/frames', [App\Http\Controllers\Admin\FrameManagementController::class, 'store'])->name('admin.frames.store');
Route::post('/admin/frames/from-template', [App\Http\Controllers\Admin\FrameManagementController::class, 'storeFromTemplate'])->name('admin.frames.fromTemplate');
Route::post('/admin/frames/settings', [App\Http\Controllers\Admin\FrameManagementController::class, 'updateSettings'])->name('admin.frames.settings');
Route::delete('/admin/frames', [App\Http\Controllers\Admin\FrameManagementController::class, 'destroy'])->name('admin.frames.destroy');

// Admin routes to manage page backgrounds (simple, no auth)
Route::get('/admin/backgrounds', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'index'])->name('admin.backgrounds.index');
Route::post('/admin/backgrounds/global', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'updateGlobal'])->name('admin.backgrounds.global');
Route::post('/admin/backgrounds/page', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'updatePage'])->name('admin.backgrounds.page');
Route::post('/admin/backgrounds/clear', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'clearPage'])->name('admin.backgrounds.clear');
Route::post('/admin/backgrounds/apply-global', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'applyGlobalToAll'])->name('admin.backgrounds.applyGlobal');
Route::post('/admin/backgrounds/tempcollage-layouts', [App\Http\Controllers\Admin\BackgroundManagementController::class, 'updateTempcollageLayouts'])->name('admin.backgrounds.tempcollageLayouts');

Route::get('/admin/settings', [App\Http\Controllers\Admin\AppSettingsController::class, 'index'])->name('admin.settings.index');
Route::post('/admin/settings', [App\Http\Controllers\Admin\AppSettingsController::class, 'update'])->name('admin.settings.update');
// Sample customer flow: start -> package -> payment -> create code -> choose template
Route::get('/flow/start', [App\Http\Controllers\FlowController::class, 'start'])->name('flow.start');
Route::post('/flow/start', [App\Http\Controllers\FlowController::class, 'begin'])->name('flow.begin');
Route::get('/flow/package', [App\Http\Controllers\FlowController::class, 'package'])->name('flow.package');
Route::post('/flow/package', [App\Http\Controllers\FlowController::class, 'choosePackage'])->name('flow.package.choose');
Route::get('/flow/payment', [App\Http\Controllers\FlowController::class, 'payment'])->name('flow.payment');
Route::post('/flow/paid', [App\Http\Controllers\FlowController::class, 'paid'])->name('flow.paid');
Route::post('/flow/update-email', [App\Http\Controllers\FlowController::class, 'updateEmail'])->name('flow.updateEmail');
Route::get('/flow/success', [App\Http\Controllers\FlowController::class, 'success'])->name('flow.success');
Route::resource('tempcollage', \App\Http\Controllers\TempcollageController::class)->middleware('pb.flow.deadline');
Route::post('tempcollage/layout', [\App\Http\Controllers\TempcollageController::class, 'chooseLayout'])->name('tempcollage.chooseLayout')->middleware('pb.flow.deadline');
Route::get('/listphoto', [App\Http\Controllers\PhotoblastController::class, 'listRepeatPhoto'])->name('list-photo')->middleware('pb.flow.deadline');
Route::get('/printphoto', [App\Http\Controllers\PhotoblastController::class, 'listPrintPhoto'])->name('print-photo')->middleware('pb.flow.deadline');
Route::post('/retakephoto', [App\Http\Controllers\PhotoblastController::class, 'retakePhoto'])->name('retake-photo')->middleware('pb.flow.deadline');
Route::post('/print', [App\Http\Controllers\PhotoblastController::class, 'print'])->name('print')->middleware('pb.flow.deadline');
Route::get('/finish', function () {

  if(session()->has('flow_run_id')){
    $flowId = (int) session('flow_run_id');
    if($flowId > 0) {
      try {
        App\Models\FlowRun::where('id', $flowId)->update([
          'status' => 'finished',
          'ended_at' => now(),
        ]);
      } catch (Throwable $e) {
        // ignore
      }
    }
  }

  if(session()->has('code')){
    $code = App\Models\Code::where('code', session('code'))->first();
    if($code && $code->status == 'ready') {
      $code->status = 'used';
      $code->save();
    }
  }
  // bersihkan semua session
  session()->flush();
  return redirect()->route('home');
});
Route::get('/video/{code}', [App\Http\Controllers\PhotoblastController::class, 'getVideo'])->name('video');

// Serve original collage file directly (no image processing). Email must be URL-encoded (e.g. @ -> %40)
Route::get('/print/original/{email}', [App\Http\Controllers\PrintController::class, 'original'])->name('print.original');
Route::get('/print/view/{email}', [App\Http\Controllers\PrintController::class, 'view'])->name('print.view');

// Dev shortcut: set session code manually (local only)
Route::get('/dev/set-code', function (Illuminate\Http\Request $request) {
  if (!app()->environment('local')) {
    abort(403, 'Dev shortcut only available in local environment');
  }
  $codeValue = (string) $request->query('code', '');
  if ($codeValue === '') {
    return redirect()->route('redeem.index')->with('message', 'Masukkan ?code=XXXXX');
  }
  $code = App\Models\Code::where('code', $codeValue)->first();
  if (!$code || $code->status !== 'ready' || !$code->transaction) {
    return redirect()->route('redeem.index')->with('message', 'Code tidak ready / tidak ada transaksi');
  }
  session(['code' => $codeValue]);
  return redirect()->route('list-photo')->with('message', 'Session code di-set');
});
